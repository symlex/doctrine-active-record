<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\DBAL\Connection as Db;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Exception\Exception;
use Closure;
use DateTime;

/**
 * Data Access Object (DAO)
 *
 * The DAO layer encapsulates the access to a database. You should use one DAO class for each entity or domain.
 * DAOs should not implement business logic, which belongs to the model layer.
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
abstract class Dao
{
    /**
     * @var Factory
     */
    private $_factory;

    /**
     * DESCRIBE TABLE cache
     *
     * @var array
     */
    private $_tableDescription = array();

    /**
     * DateTime class name (can be changed for testing)
     *
     * @var string
     */
    protected static $_dateTimeClassName = '\DateTime';

    /**
     * Constructor
     *
     * @param Factory $factory DAO factory instance
     */
    public function __construct(Factory $factory)
    {
        $this->setFactory($factory);

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
     * Sets factory instance
     *
     * @param Factory $factory
     */
    private function setFactory(Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Returns factory instance
     *
     * @return Factory
     */
    protected function getFactory(): Factory
    {
        return $this->_factory;
    }

    /**
     * Returns a new DAO instance
     *
     * @param string $name Class name without namespace prefix and postfix
     * @return Dao|EntityDao
     */
    public function createDao(string $name)
    {
        $result = $this->getFactory()->create($name);

        return $result;
    }

    /**
     * Returns the current DBAL Connection
     *
     * @throws Exception
     * @return Db
     */
    protected function getDb(): Db
    {
        return $this->getFactory()->getDb();
    }

    /**
     * Returns the current DateTime class name (can be changed for testing)
     *
     * @param string $className
     * @return string
     */
    public static function setDateTimeClassName(string $className)
    {
        if(!class_exists($className)) {
            throw new \InvalidArgumentException($className . ' does not exist');
        }

        self::$_dateTimeClassName = $className;
    }

    /**
     * Returns a new DateTime instance
     *
     * @param string $time
     * @param \DateTimeZone|NULL $timezone
     * @return DateTime
     */
    protected function getDateTimeInstance(string $time = "now", \DateTimeZone $timezone = null): DateTime
    {
        $result = new self::$_dateTimeClassName($time, $timezone);

        return $result;
    }

    /**
     * Returns the Doctrine DBAL query builder
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->getDb()->createQueryBuilder();
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
     * If an exception occurs during execution of the function or transaction commit,
     * the transaction is rolled back and the exception re-thrown.
     *
     * @param \Closure $func The function to execute transactionally.
     *
     * @throws \Throwable
     */
    public function transactional(Closure $func)
    {
        $this->beginTransaction();

        try {
            $func();

            $this->commit();
        } catch (\Throwable $e) {
            $this->rollBack();

            throw $e;
        }
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
    protected function fetchAll($statement, array $params = array(), array $types = array()): array
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
    protected function fetchPairs($statement, array $params = array(), array $types = array()): array
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
    protected function fetchCol($statement, array $params = array(), array $types = array()): array
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
    protected function describeTable(string $tableName): array
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
