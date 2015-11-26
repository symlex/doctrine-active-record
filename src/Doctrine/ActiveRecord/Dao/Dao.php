<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\DBAL\Connection as Db;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Exception\Exception;

/**
 * Data Access Object
 *
 * The DAO layer encapsulates the access to a database. You should use one DAO class for each entity.
 * DAOs should not implement business logic, which belongs to the model layer.
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
abstract class Dao
{
    /**
     * @var Db
     */
    private $_db;
    private $_tableDescription = array();

    protected $_factoryNamespace = '';
    protected $_factoryPostfix = 'Dao';

    /**
     * Constructor
     *
     * @param Db $db Database connection (Doctrine DBAL)
     */
    public function __construct(Db $db)
    {
        $this->setDb($db);

        $this->init();
    }

    /**
     * Optional init method
     */
    public function init()
    {
        // Does nothing by default
    }

    /**
     * Returns a new DAO instance
     *
     * @param string $name Class name without namespace prefix and postfix
     * @return Dao
     */
    public function factory($name)
    {
        $className = $this->_factoryNamespace . '\\' . $name . $this->_factoryPostfix;

        $dao = new $className ($this->getDb());

        return $dao;
    }

    /**
     * Returns the current DBAL Connection
     *
     * @throws Exception
     * @return Db
     */
    protected function getDb()
    {
        if (empty($this->_db)) {
            throw new Exception ('No database adapter set');
        }

        return $this->_db;
    }

    /**
     * Returns the Doctrine DBAL query builder
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getDb()->createQueryBuilder();
    }

    /**
     * Sets the Db instance
     * @param Db $db
     */
    protected function setDb(Db $db)
    {
        $this->_db = $db;
    }

    /**
     * Start a database transaction
     */
    public function beginTransaction()
    {
        $this->getDb()->beginTransaction();

        return $this;
    }

    /**
     * Commit a database transaction
     */
    public function commit()
    {
        $this->getDb()->commit();

        return $this;
    }

    /**
     * Roll back a database transaction
     */
    public function rollBack()
    {
        $this->getDb()->rollBack();

        return $this;
    }

    /**
     * The fetchAll() method returns data in an array of associative arrays, using the first column as the array index.
     *
     * @param $query
     * @return array
     */
    protected function fetchAll($query)
    {
        return $this->getDb()->fetchAll($query);
    }

    /**
     * The fetchPairs() method returns data in an array of key-value pairs, as an associative array
     * with a single entry per row
     *
     * @param $query
     * @return array
     */
    protected function fetchPairs($query)
    {
        $result = array();

        $rows = $this->getDb()->fetchAll($query);

        foreach ($rows as $row) {
            $result[current($row)] = next($row);
        }

        return $result;
    }

    /**
     * Returns data only for the first row fetched from the result set, and it returns only the value of the first
     * column in that row. Therefore it returns only a single scalar value, not an array or an object.
     *
     * @param $query
     * @return string
     */
    protected function fetchOne($query)
    {
        return $this->getDb()->fetchColumn($query);
    }

    /**
     * Returns values of the first column as array
     *
     * @param $query
     * @return array
     */
    protected function fetchCol($query)
    {
        $result = array();

        $rows = $this->getDb()->fetchAll($query);

        foreach ($rows as $row) {
            $result[] = current($row);
        }

        return $result;
    }

    /**
     * Returns column names and types as array
     *
     * @param string $tableName
     * @return array
     */
    protected function describeTable($tableName)
    {
        if (isset($this->_tableDescription[$tableName])) {
            return $this->_tableDescription[$tableName];
        }

        $result = array();
        $query = 'DESCRIBE ' . $this->getDb()->quoteIdentifier($tableName);
        $cols = $this->fetchAll($query);

        foreach ($cols as $col) {
            $result[$col['Field']] = $col['Type'];
        }

        $this->_tableDescription[$tableName] = $result;

        return $result;
    }
}