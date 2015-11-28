<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\DBAL\Connection as Db;
use Doctrine\ActiveRecord\Exception\FactoryException;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Factory
{
    /**
     * @var Db
     */
    protected $_db;

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
    }

    /**
     * Returns a new DAO instance
     *
     * @param string $name Class name without namespace prefix and postfix
     * @throws FactoryException
     * @return Dao
     */
    public function getDao($name)
    {
        if (empty($name)) {
            throw new FactoryException ('getDao() requires a DAO name as first argument');
        }

        $className = $this->getFactoryNamespace() . '\\' . $name . $this->getFactoryPostfix();

        if(!class_exists($className)) {
            throw new FactoryException ('DAO class "' . $className . '" does not exist');
        }

        $result = new $className ($this);

        return $result;
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
     * Returns absolute namespace used by the DAO factory method
     *
     * @return string
     */
    public function getFactoryNamespace()
    {
        $result = $this->_factoryNamespace;

        if ($result && strpos($result, '\\') !== 0) {
            $result = '\\' . $result;
        }

        return $result;
    }

    /**
     * Returns class name postfix used by the DAO factory method
     *
     * @return string
     */
    public function getFactoryPostfix()
    {
        return $this->_factoryPostfix;
    }

    /**
     * Returns the current DBAL Connection
     *
     * @throws FactoryException
     * @return Db
     */
    public function getDb()
    {
        if (empty($this->_db)) {
            throw new FactoryException ('No database adapter set');
        }

        return $this->_db;
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
}