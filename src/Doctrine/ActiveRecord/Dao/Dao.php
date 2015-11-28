<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\DBAL\Connection as Db;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Exception\Exception;
use Closure;

/**
 * Data Access Object (DAO)
 *
 * The DAO layer encapsulates the access to a database. You should use one DAO class for each entity or domain.
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

    /**
     * DESCRIBE TABLE cache
     *
     * @var array
     */
    private $_tableDescription = array();

    /**
     * Namespace used by DAO instance factory method
     *
     * @var string
     */
    protected $_factoryNamespace = '';

    /**
     * Class name postfix used by DAO instance factory method
     *
     * @var string
     */
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
     * Sets namespace used by the DAO factory method
     *
     * @param string $namespace
     */
    public function setFactoryNamespace($namespace)
    {
        $this->_factoryNamespace = (string)$namespace;
    }

    /**
     * Sets class name postfix used by the DAO factory method
     *
     * @param string $postfix
     */
    public function setFactoryPostfix($postfix)
    {
        $this->_factoryPostfix = (string)$postfix;
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
     *
     * @param Db $db
     */
    protected function setDb(Db $db)
    {
        $this->_db = $db;
    }

    /**
     * Starts a transaction by suspending auto-commit mode.
     *
     * @return $this
     */
    public function beginTransaction()
    {
        $this->getDb()->beginTransaction();

        return $this;
    }

    /**
     * Commits the current transaction.
     *
     * @throws \Doctrine\DBAL\ConnectionException If the commit failed due to no active transaction or
     *                                            because the transaction was marked for rollback only.
     *
     * @return $this
     */
    public function commit()
    {
        $this->getDb()->commit();

        return $this;
    }

    /**
     * Roll back a database transaction
     *
     * @return $this
     */
    public function rollBack()
    {
        $this->getDb()->rollBack();

        return $this;
    }

    /**
     * Executes a function in a transaction.
     *
     * The function gets passed this DAO instance as an (optional) parameter.
     *
     * If an exception occurs during execution of the function or transaction commit,
     * the transaction is rolled back and the exception re-thrown.
     *
     * @param \Closure $func The function to execute transactionally.
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function transactional(Closure $func)
    {
        $this->beginTransaction();

        try {
            $func();

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();

            throw $e;
        }

        return $this;
    }

    /**
     * The fetchAll() method returns data in an array of associative arrays, using the first column as the array index.
     *
     * @param string $statement The SQL query.
     * @param array $params The prepared statement params.
     * @param array $types The query parameter types.
     * @return array
     * @throws Exception
     */
    protected function fetchAll($statement, array $params = array(), $types = array())
    {
        return $this->getDb()->fetchAll($statement, $params, $types);
    }

    /**
     * The fetchPairs() method returns data in an array of key-value pairs, as an associative array
     * with a single entry per row
     *
     * @param string $statement The SQL query.
     * @param array $params The prepared statement params.
     * @param array $types The query parameter types.
     * @return array
     * @throws Exception
     */
    protected function fetchPairs($statement, array $params = array(), $types = array())
    {
        $result = array();

        $rows = $this->getDb()->fetchAll($statement, $params, $types);

        foreach ($rows as $row) {
            $result[current($row)] = next($row);
        }

        return $result;
    }

    /**
     * Returns value of the first column of the first row
     *
     * @param string $statement The SQL query.
     * @param array $params The prepared statement params.
     * @param array $types The query parameter types.
     * @return mixed
     * @throws Exception
     */
    protected function fetchSingleValue($statement, array $params = array(), array $types = array())
    {
        return $this->getDb()->fetchColumn($statement, $params, 0, $types);
    }

    /**
     * Returns values of the first column as array
     *
     * @param string $statement The SQL query.
     * @param array $params The prepared statement params.
     * @param array $types The query parameter types.
     * @return array
     */
    protected function fetchCol($statement, array $params = array(), $types = array())
    {
        $result = array();

        $rows = $this->getDb()->fetchAll($statement, $params, $types);

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
        $statement = 'DESCRIBE ' . $this->getDb()->quoteIdentifier($tableName);
        $cols = $this->fetchAll($statement);

        foreach ($cols as $col) {
            $result[$col['Field']] = $col['Type'];
        }

        $this->_tableDescription[$tableName] = $result;

        return $result;
    }
}