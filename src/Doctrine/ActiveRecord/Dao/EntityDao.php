<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\DBAL\Connection as Db;
use DateTime;
use InvalidArgumentException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Exception\Exception;
use Doctrine\ActiveRecord\Exception\NotFoundException;

/**
 * Data Access Object for SQL database entities
 *
 * DAOs directly deal with database tables and raw SQL, if needed. To implement raw SQL only,
 * you can use the basic \Doctrine\ActiveRecord\DaoAbstract class, while \Doctrine\ActiveRecord\EntityDao
 * inherits from this and adds many powerful methods to easily deal with single database tables.
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
abstract class EntityDao extends DaoAbstract
{
    private $_data = array(); // Property cache
    private $_originalData = array(); // Property cache (equals $_data after calling find() or update())

    protected $_tableName = ''; // Database table name
    protected $_primaryKey = 'id'; // Name of primary key column
    protected $_primaryKeySequence = null; // Sequence name used when inserting (null for mysql)
    protected $_fieldMap = array(); // 'db_column' => 'object_property'
    protected $_formatMap = array(); // 'db_column' => Format::TYPE
    protected $_valueMap = array(); // 'object_property' => 'db_column'

    protected $_timestampEnabled = false; // Set to true to enable "updated" and "created" timestamps
    protected $_timestampCreatedCol = 'created';
    protected $_timestampUpdatedCol = 'updated';

    public function __construct(Db $db = null)
    {
        parent::__construct($db);

        if (empty($this->_valueMap)) {
            $this->_valueMap = array_flip($this->_fieldMap);
        }
    }

    /**
     * Magic function to read a data value
     *
     * @param $name string Name of the property to be returned
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_valueMap[$name])) {
            $key = $this->_valueMap[$name];
        } else {
            $key = $name;
        }

        if (!is_array($this->_primaryKey) && $key == $this->_primaryKey) {
            return $this->getId();
        }

        if (!array_key_exists($key, $this->_data)) {
            // Is there a public getter function for this value?
            $functionName = $this->composeGetterName($key);

            if (method_exists($this, $functionName)) {
                $reflection = new \ReflectionMethod($this, $functionName);

                if ($reflection->isPublic()) {
                    return $this->$functionName();
                }
            }

            throw new Exception ('Column not found in data: ' . $name);
        }

        $result = $this->_data[$key];

        if (isset($this->_formatMap[$key])) {
            $result = Format::fromSql($this->_formatMap[$key], $result);
        }

        return $result;
    }

    /**
     * Magic function to set a data value
     *
     * @param $name string Name of the property to be set/updated
     * @param $value string Values of the property to be set/updated
     * @return void
     */
    public function __set($name, $value)
    {
        if (isset($this->_valueMap[$name])) {
            $key = $this->_valueMap[$name];
        } else {
            $key = $name;
        }

        if (isset($this->_formatMap[$key])) {
            $value = Format::toSql($this->_formatMap[$key], $value);
        }

        if (!is_array($this->_primaryKey) && $key == $this->_primaryKey) {
            $this->setId($value);
        } else {
            $this->_data[$key] = $value;
        }
    }

    /**
     * Set raw data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        $this->_originalData = $data;
    }

    /**
     * Set multiple values at once
     */
    public function setValues(array $data)
    {
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Sets values that exist in the table schema only
     */
    public function setDefinedValues(array $data)
    {
        $keys = array_keys($this->describeTable($this->getTableName()));

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $this->$key = $data[$key];
            }
        }
    }

    /**
     * Returns all data values as array
     *
     * @return array
     */
    public function getValues()
    {
        $result = array();

        foreach ($this->_data as $name => $value) {
            if (isset($this->_fieldMap[$name])) {
                $name = $this->_fieldMap[$name];

            }

            if (isset($this->_formatMap[$name])) {
                $value = Format::fromSql($this->_formatMap[$name], $value);
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Load row with given id from database
     *
     * @param mixed $id Primary Key
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @return $this
     */
    public function find($id)
    {
        $db = $this->getDb();
        $alias = $this->getDefaultTableAlias();

        $select = $this->createQueryBuilder();
        $select->select('*');
        $select->from($this->_tableName, $alias);

        if (is_array($this->_primaryKey) && count($this->_primaryKey) == 1) {
            $primaryKey = $this->_primaryKey[0];
        } else {
            $primaryKey = $this->_primaryKey;
        }

        if (is_array($id)) {
            foreach ($id as $key => $val) {
                if (isset($this->_formatMap[$key])) {
                    $val = Format::toSql($this->_formatMap[$key], $val);
                }

                $select->andWhere($db->quoteIdentifier($key) . ' = ' . $db->quote($val));
            }
        } elseif (!is_array($primaryKey)) {
            if (isset($this->_formatMap[$primaryKey])) {
                $id = Format::toSql($this->_formatMap[$primaryKey], $id);
            }

            $select->where($db->quoteIdentifier($primaryKey) . ' = ' . $db->quote($id));
        } else {
            throw new InvalidArgumentException ('$id must be an array for compound primary keys');
        }

        $data = $db->fetchAssoc($select);

        if (!is_array($data)) {
            throw new NotFoundException ('No matching row found');
        }

        $this->setData($data);

        return $this;
    }

    /**
     * Reloads the entity values from database
     *
     * @return $this
     * @throws NotFoundException
     */
    public function reload()
    {
        $id = $this->getWhereAsArray();

        return $this->find($id);
    }

    /**
     * Check if an entry with the given primary key or key/value exists in the database
     *
     * @param mixed $id The primary key or an array (key/value)
     * @return bool
     */
    public function exists($id)
    {
        $db = $this->getDb();
        $select = $this->createQueryBuilder();
        $select->from($this->_tableName, 'a');

        if (is_array($id)) {
            foreach ($id as $key => $val) {
                if (isset($this->_formatMap[$key])) {
                    $val = Format::toSql($this->_formatMap[$key], $val);
                }

                $select->andWhere($this->getDb()->quoteIdentifier($key) . ' = ' . $db->quote($val));
            }
        } else {
            if (isset($this->_formatMap[$this->_primaryKey])) {
                $id = Format::fromSql($this->_formatMap[$this->_primaryKey], $id);
            }

            $select->where($this->getDb()->quoteIdentifier($this->_primaryKey) . ' = ' . $db->quote($id));
        }

        $data = $this->getDb()->fetchAssoc($select);

        return is_array($data);
    }

    /**
     * Create a new database entry (only if no ID was set)
     */
    public function insert()
    {
        $insertFields = $this->_data;
        $db = $this->getDb();

        if ($this->_timestampEnabled) {
            $now = new \DateTime;
            $insertFields[$this->_timestampCreatedCol] = $now->format(Format::DATETIME);
            $insertFields[$this->_timestampUpdatedCol] = $now->format(Format::DATETIME);
        }

        $db->insert($this->_tableName, $insertFields);

        if (!is_array($this->_primaryKey) && !isset($this->_data[$this->_primaryKey])) {
            // Entity has no primary key yet and primary key is not a compound key (must be manually set)
            $insertFields[$this->_primaryKey] = $db->lastInsertId($this->_primaryKeySequence);
        }

        $this->_data = $insertFields;
        $this->_originalData = $insertFields;
    }

    /**
     * Save updated object values to database (only if they were changed)
     */
    public function update()
    {
        $fields = array();
        $db = $this->getDb();

        foreach ($this->_data as $key => $value) {
            $valueHasChanged =
                (!array_key_exists($key, $this->_originalData)) ||
                ($this->_originalData[$key] != $value) ||
                (is_null($this->_originalData[$key]) !== is_null($value));

            if ($valueHasChanged && (
                    (!is_array($this->_primaryKey) && $key != $this->_primaryKey) ||
                    (is_array($this->_primaryKey) && !in_array($key, $this->_primaryKey)))
            ) {
                $fields[$key] = $value;
            }
        }

        if (count($fields) == 0) {
            // Don't do anything, if no fields were changed since last find() or update()
            return false;
        }

        if ($this->_timestampEnabled) {
            $now = new \DateTime;
            $fields[$this->_timestampUpdatedCol] = $now->format(Format::DATETIME);
        }

        $db->update(
            $this->_tableName,
            $fields,
            $this->getWhereAsArray()
        );

        // Update original data
        $this->_originalData = $this->_data;

        return true;
    }

    /**
     * Delete database rows associated with this object (ID must be set)
     *
     * Note: What and if something is returned depends on the database adapter implementation. An exception might be
     * thrown, if the database row can not be deleted.
     */
    public function delete()
    {
        $db = $this->getDb();

        return $db->delete($this->_tableName, $this->getWhereAsArray());
    }

    /**
     * Compose WHERE part of SQL query
     *
     * @return string
     */
    protected function getWhere()
    {
        $db = $this->getDb();

        if (is_array($this->_primaryKey)) {
            $list = array();

            foreach ($this->_primaryKey as $key) {
                $list[] = $db->quoteIdentifier($key)
                    . ' = ' . $db->quote($this->$key);
            }

            $where = implode(' AND ', $list);
        } else {
            $where = $db->quoteIdentifier($this->_primaryKey)
                . ' = ' . $db->quote($this->getId());
        }

        return $where;
    }

    /**
     * Returns where party of query as array for Doctrine update()
     *
     * @return array
     */
    protected function getWhereAsArray()
    {
        $where = array();

        if (is_array($this->_primaryKey)) {
            foreach ($this->_primaryKey as $key) {
                $where[$key] = $this->$key;
            }
        } else {
            $where[$this->_primaryKey] = $this->getId();
        }

        return $where;
    }


    /**
     * Returns the primary key (or an exception, if it was not set yet)
     *
     * @throws Exception
     * @return mixed
     */
    public function getId()
    {
        if (!is_array($this->_primaryKey) && isset($this->_data[$this->_primaryKey])) {
            return $this->_data[$this->_primaryKey];
        } elseif (is_array($this->_primaryKey)) {
            $result = array();

            foreach ($this->_primaryKey as $key) {
                if (!isset($this->_data[$key])) {
                    throw new Exception('Primary key not complete: ' . $key);
                }

                $result[$key] = $this->_data[$key];
            }

            return $result;
        }

        throw new Exception('No Primary ID set for this object');
    }

    /**
     * Returns true of this DAO has a primary key ID assigned and false, if not
     *
     * @return bool
     */
    public function hasId()
    {
        try {
            $this->getId();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Set primary key
     *
     * @param mixed $id
     * @throws Exception
     * @return void
     */
    public function setId($id)
    {
        if (!is_array($this->_primaryKey) && !isset($this->_data[$this->_primaryKey])) {
            $this->_data[$this->_primaryKey] = $id;
        } elseif (is_array($this->_primaryKey)) {
            foreach ($this->_primaryKey as $key) {
                if (!isset($id[$key])) {
                    throw new Exception('Primary key not complete: ' . $key);
                }

                $this->_data[$key] = $id[$key];
            }
        } else {
            throw new Exception('Can not set Primary ID again');
        }
    }

    /**
     * Returns all instances that match $cond - Attention: this can be a lot!
     * Please use search() or searchAll(), if you want to use count, offset and order
     *
     * Background information: The findAll() should be cachable for future optimizations, while search
     * does not use any caching. Caches (usually) don't support count, offset or order.
     *
     * @param array $cond Key/Value array of matching conditions
     * @param boolean $wrapResult Create a new object for each result? If false, the function returns raw data.
     * @return array
     */
    public function findAll(array $cond = array(), $wrapResult = true)
    {
        $select = $this->createQueryBuilder();
        $alias = $this->getDefaultTableAlias();

        $db = $this->getDb();
        $select->from($this->_tableName, $alias);
        $select->select('*');

        foreach ($cond as $key => $val) {
            if (is_numeric($key) && is_scalar($val) && $val !== NULL) {
                if (isset($this->_formatMap[$this->_primaryKey])) {
                    $val = Format::fromSql($this->_formatMap[$this->_primaryKey], $val);
                }

                $select->orWhere($db->quoteIdentifier($this->_primaryKey) . ' = ' . $db->quote($val));
            } elseif (is_int($key) && is_object($val)) {
                $select->andWhere((string)$val);
            } elseif (is_int($key) && is_array($val)) {
                $select->andWhere($db->quoteIdentifier($this->_primaryKey) . ' IN (' . $this->sqlImplode($val) . ')');
            } elseif (is_string($key) && is_array($val) && count($val) > 0) {
                $select->andWhere($db->quoteIdentifier($key) . ' IN (' . $this->sqlImplode($val) . ')');
            } elseif (is_string($key) && $val === NULL) {
                $select->andWhere($db->quoteIdentifier($key) . ' IS NULL');
            } else {
                if (isset($this->_formatMap[$key])) {
                    $val = Format::fromSql($this->_formatMap[$key], $val);
                }

                $select->andWhere($db->quoteIdentifier($key) . ' = ' . $db->quote($val));
            }
        }

        $rows = $db->fetchAll($select);

        if ($wrapResult) {
            return $this->wrapAll($rows);
        } else {
            return $rows;
        }
    }

    /**
     * Quotes a column name incl table prefix
     *
     * @param $key string Column name
     * @param $tableAlias string Prefix
     * @return string
     */
    private function getQuotedKey($key, $tableAlias)
    {
        $parts = explode('.', $key);

        if (count($parts) == 2) {
            $table = str_replace($this->_tableName, $tableAlias, $parts[0]);
            $result = $this->getDb()->quoteIdentifier($table) . '.'
                . $this->getDb()->quoteIdentifier($parts[1]);
        } else {
            $result = $this->getDb()->quoteIdentifier($tableAlias) . '.'
                . $this->getDb()->quoteIdentifier($key);
        }

        return $result;
    }

    /**
     * @param string $tableName Optional table name (if different from the default)
     * @return string The default table alias (first character of the table name)
     */
    protected function getDefaultTableAlias($tableName = '')
    {
        if ($tableName == '') {
            $tableName = $this->_tableName;
        }

        return substr($tableName, 0, 1);
    }

    /**
     * More powerful alternative to findAll() to search the database incl. count, offset, order etc.
     *
     * @param array $params The search parameter (see beginning of function for supported options)
     * @return array
     */
    public function search(array $params)
    {
        // Default values for all possible input parameters
        $defaults = array(
            'table' => $this->_tableName,
            'cond' => array(),
            'count' => 20,
            'offset' => 0,
            'count_total' => true, // Count total number of rows
            'join' => false,
            'left_join' => false,
            'columns' => false,
            'order' => false,
            'group' => false,
            'wrap' => true,
            'ids_only' => false,
            'sql_filter' => '',
            'table_alias' => '',
            'id_filter' => array()
        );

        $params = array_merge($defaults, $params);

        // Optional SQL filter table alias canonization
        if (empty($params['table_alias'])) {
            $params['table_alias'] = $this->getDefaultTableAlias($params['table']);
        }

        $db = $this->getDb();

        /**
         * @var QueryBuilder
         */
        $select = $this->createQueryBuilder();

        // Build WHERE conditions
        foreach ($params['cond'] as $key => $val) {
            if (is_int($key)) {
                $select->andWhere($val);
            } elseif (is_array($val) && count($val) > 0) {
                $select->andWhere($this->getQuotedKey($key, $params['table_alias']) . ' IN (' . $this->sqlImplode($val) . ')');
            } elseif (!is_array($val) && $val !== '' && $val !== null) {
                if (is_bool($val)) {
                    $val = (int)$val;
                }

                $select->andWhere($this->getQuotedKey($key, $params['table_alias']) . ' = ' . $db->quote($val));
            }
        }

        // Check for optional ID filters (sets; pre-defined result lists)
        if (count($params['id_filter']) > 0) {
            $select->andWhere($this->getQuotedKey($this->_primaryKey, $params['table_alias'])
                . ' IN (' . $this->sqlImplode($params['id_filter']) . ')');
            //$select->setParameter(':id_filter', $params['id_filter']);
        }

        // Optional grouping
        if ($params['group']) {
            $select->groupBy($params['group']);
        }

        // Do a separate query to determine matching row count
        $countSelect = clone $select;

        // Optional columns
        if ($params['columns']) {
            foreach ($params['columns'] as $col) {
                $col = str_replace($this->_tableName . '.', $params['table_alias'] . '.', $col);

                $select->addSelect($col);
            }
        } else {
            $select->addSelect($params['table_alias'] . '.*');
        }

        // Optional join
        if ($params['join'] && is_array($params['join'])) {
            foreach ($params['join'] as $join) {
                $countSelect->join($join[0], $join[1], $join[2], $join[3]);

                $select->join($join[0], $join[1], $join[2], $join[3]);

                if (!$params['ids_only'] && isset($join[4])) {
                    $select->addSelect($join[4]);
                }
            }
        }

        if ($params['left_join'] && is_array($params['left_join'])) {
            foreach ($params['left_join'] as $join) {
                $countSelect->leftJoin($join[0], $join[1], $join[2], $join[3]);

                $select->leftJoin($join[0], $join[1], $join[2], $join[3]);

                if (!$params['ids_only'] && isset($join[4])) {
                    $select->addSelect($join[4]);
                }
            }
        }

        if ($params['ids_only']) {
            $select->select(array('id' => $this->_primaryKey));
        }

        $select->from($params['table'], $params['table_alias']);

        $filterSelect = (string)clone $select;

        // Check for optional SQL filters
        if ($params['sql_filter'] != '') {
            $select->andWhere($params['sql_filter']);
            $countSelect->andWhere($params['sql_filter']);
        }

        if ($params['count']) {
            $select->setMaxResults($params['count'])->setFirstResult($params['offset']);
        }

        if ($params['count_total']) {
            $countSelect->from($params['table'], $params['table_alias']);
            $countSelect->select(array('COUNT(*) AS count'));
            $countSelect = (string)$this->optimizeSearchQuery($countSelect, $params);
            $count = $this->fetchOne($countSelect);
        } else {
            $count = false;
        }

        // Optional ordering of results
        if ($params['order']) {
            if (is_array($params['order'])) {
                foreach ($params['order'] as $sortOrder) {
                    $select->addOrderBy($this->getOrderField($sortOrder), $this->getOrderDirection($sortOrder));
                }
            } else {
                $select->addOrderBy($this->getOrderField($params['order']), $this->getOrderDirection($params['order']));
            }
        }

        $select = (string)$this->optimizeSearchQuery($select, $params);

        if ($params['ids_only']) {
            // Fetch all result ids from the first column of the result set
            $rows = $this->fetchCol($select);
        } else {
            // Fetch all result rows and optionally wrap them in DAO objects (strongly recommended)
            $rows = $this->fetchAll($select);

            if ($params['wrap']) {
                $rows = $this->wrapAll($rows);
            }
        }

        try {
            $primaryKey = $this->getPrimaryKeyName();
        } catch (Exception $e) {
            $primaryKey = '';
        }

        // Build result array that additionally contains the different query parameters and the matching row count
        $result = array(
            'rows' => $rows,
            'order' => $params['order'],
            'count' => $params['count'],
            'offset' => $params['offset'],
            'total' => $count ? $count : count($rows),
            'filter_sql' => $filterSelect,
            'sql' => $select,
            'table_pk' => $primaryKey,
            'table_alias' => $params['table_alias']
        );

        return $result;
    }

    /**
     * Override to manually optimize SQL created by Doctrine DBAL QueryBuilder
     *
     * @param QueryBuilder $query SQL query string or Query Builder instance
     * @param array $params Search parameters (as passed to search($params))
     * @return QueryBuilder|string
     */
    protected function optimizeSearchQuery(QueryBuilder $query, array $params)
    {
        return $query;
    }

    /**
     * Creates an new DAO for each row
     *
     * @param array $rows The db result set
     * @return array
     */
    public function wrapAll(array $rows)
    {
        $class = get_class($this);
        $result = array();

        foreach ($rows as $row) {
            $dao = new $class($this->getDb());
            $dao->setData($row);
            $result[] = $dao;
        }

        return $result;
    }

    /**
     * Converts a string from under_score to CamelCase
     *
     * @param $str
     * @return string
     */
    protected function underscoreToCamelCase($str)
    {
        $result = '';

        $words = explode('_', strtolower($str));

        foreach ($words as $word) {
            $result .= ucfirst(trim($word));
        }

        return $result;
    }

    /**
     * Composes name of getter function for given column name
     *
     * @param string $columnName
     * @return string
     */
    protected function composeGetterName($columnName)
    {
        $result = 'get' . $this->underscoreToCamelCase($columnName);

        return $result;
    }

    /**
     * Adds SQL quotes to all values in an array (useful for "value IN (...)" queries)
     */
    private function quoteArray(array $input)
    {
        $result = array();

        foreach ($input as $value) {
            $result[] = $this->getDb()->quote($value);
        }

        return $result;
    }

    /**
     * Implodes an array using commas and SQL escaping
     *
     * @param array $input The array that should be imploded
     * @return string
     */
    protected function sqlImplode(array $input)
    {
        return implode(',', $this->quoteArray($input));
    }

    /**
     * Helper function that makes sure sorting is case-insensitive and does not
     * contain invalid search directions (ASC and DESC are allowed)
     *
     * @param $rawOrder string
     * @return string
     */
    protected function composeOrderArgument($rawOrder)
    {
        if (empty($rawOrder)) {
            return $rawOrder;
        }

        $parts = explode(' ', $rawOrder);
        $order = $parts[0];
        $direction = count($parts) == 2 ? strtoupper($parts[1]) : '';

        if ($direction == 'ASC' || $direction == 'DESC') {
            $order .= ' ' . $direction;
        }

        return $order;
    }

    protected function getOrderDirection($sortOrder)
    {
        $parts = explode(' ', $sortOrder);

        if (count($parts) == 2 && strtoupper($parts[1]) == 'DESC') {
            $result = 'DESC';
        } else {
            $result = 'ASC';
        }

        return $result;
    }

    protected function getOrderField($sortOrder)
    {
        $parts = explode(' ', $sortOrder);
        $result = $parts[0];

        return $result;
    }

    /**
     * Creates SQL needed to search multiple db fields for a certain string incl. automatic wildcards before and after
     *
     * @param string $value The search string
     * @param array $keys The fields that should be searched
     * @return string
     */
    protected function getFulltextCondition($value, array $keys)
    {
        $result = array();

        foreach ($keys AS $key) {
            $result[] = 'UPPER(' . $this->getDb()->quoteIdentifier($key) . ') LIKE UPPER('
                . $this->getDb()->quote(str_replace('*', '%', '%' . $value . '%')) . ')';
        }

        return '(' . implode(' OR ', $result) . ')';
    }

    /**
     * Removes and returns a value from an array. This function ignore non-existing keys and returns null in this case.
     *
     * @param array $array The array (will be modified!)
     * @param string $valueName The key of the value to be removed
     * @return mixed
     */
    protected function extractValueFromArray(array &$array, $valueName)
    {
        $result = @$array[$valueName];
        unset($array[$valueName]);
        return $result;
    }

    /**
     * Helper function to update n-to-m relationship tables
     *
     * @param $relationTable string The table to be updated
     * @param $primaryKeyName string The name of the column, this entity is referenced with in the relationship table
     * @param $foreignKeyName string The name of the column, the other entity is referenced with
     * @param $existing array List of current relationships (how it is now)
     * @param $updated array List of new relationships (how it should be, after calling this method)
     */
    public function updateRelationTable($relationTable, $primaryKeyName, $foreignKeyName, array $existing, array $updated)
    {
        $db = $this->getDb();

        foreach ($updated as $id) {
            if (!in_array($id, $existing)) {
                $db->insert($relationTable, array(
                        $primaryKeyName => $this->getId(),
                        $foreignKeyName => $id)
                );
            }
        }

        foreach ($existing as $id) {
            if (!in_array($id, $updated)) {
                $whereArray = array($primaryKeyName => $this->getId(), $foreignKeyName => $id);
                $this->getDb()->delete($relationTable, $whereArray);
            }
        }
    }

    /**
     * Returns true, if this DAO automatically adds timestamps when creating and updating rows
     *
     * @return bool
     */
    public function hasTimestampEnabled()
    {
        return ($this->_timestampEnabled == true);
    }

    /**
     * Returns a key/value array (list) of all matching rows
     *
     * @param string $colName The value column name
     * @param string $order The sort order
     * @param string $where An optional filter (raw SQL)
     * @param string $indexName Optional key name (default is the primary key)
     * @return array
     */
    public function findList($colName, $order = '', $where = '', $indexName = '')
    {
        $db = $this->getDb();

        if (!$indexName) {
            $indexName = $this->_primaryKey;
        }

        $select = $this->createQueryBuilder();
        $select->select(array($indexName, $colName));

        $select->from($this->_tableName, 'a');

        if ($where) {
            $select->where($where);
        }

        if ($order) {
            $select->orderBy($this->getOrderField($order), $this->getOrderDirection($order));
        }

        $result = array();
        $rows = $db->fetchAll($select);

        foreach ($rows as $row) {
            $result[$row[$indexName]] = $row[$colName];
        }

        return $result;
    }

    /**
     * Returns the name of the underlying database table
     *
     * @throws Exception
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->_tableName)) {
            throw new Exception ('Table name is not set');
        }

        return $this->_tableName;
    }

    /**
     * Sets the name of the underlying database table
     *
     * @param string $tableName
     * @return $this
     */
    protected function setTableName($tableName)
    {
        $this->_tableName = $tableName;

        return $this;
    }

    /**
     * Returns the name of the primary key column
     * Throws an exception, if primary key is an array
     *
     * @return string
     * @throws Exception
     */
    public function getPrimaryKeyName()
    {
        if (is_array($this->_primaryKey)) {
            throw new Exception ('Primary key is an array');
        }

        return $this->_primaryKey;
    }

    /**
     * Sets the primary key of this entity
     *
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return $this
     */
    protected function setPrimaryKey($key)
    {
        if (!is_array($key) && !is_string($key)) {
            throw new InvalidArgumentException ('Primary key must be a string or an array');
        }

        $this->_primaryKey = $key;

        return $this;
    }

    /**
     * Checks if a column is required.
     * A column is required if
     *   - it is part of the order array
     *   - it is part of the columns array
     *   - the columns array is empty, since in this case all columns are required
     *
     * @param array $searchParams
     * @param string $column
     * @return bool
     */
    protected function columnIsRequired(array $searchParams, $column)
    {
        $result = false;

        if (empty($searchParams['columns']) || in_array($column, $searchParams['columns'])) {
            return true;
        }

        if (isset($searchParams['order'])) {
            if (is_array($searchParams['order'])) {
                $order = $searchParams['order'];
            } else {
                $order = array($searchParams['order']);
            }

            foreach ($order as $orderCol) {
                // Postfix of $orderCol can be the sorting direction (ASC/DESC)
                $parts = explode(' ', $orderCol);
                if ($column == $parts[0]) {
                    $result = true;
                }
            }
        }

        return $result;
    }
}
