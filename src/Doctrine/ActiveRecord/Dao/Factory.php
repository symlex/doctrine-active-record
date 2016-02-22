<?php

namespace Doctrine\ActiveRecord\Dao;

use Doctrine\ActiveRecord\Factory\FactoryAbstract;
use Doctrine\DBAL\Connection as Db;
use Doctrine\ActiveRecord\Exception\FactoryException;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Factory extends FactoryAbstract
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

        $className = $this->getClassName($name);

        $result = $this->createDaoInstance($className);

        return $result;
    }

    /**
     * Returns new DAO instance of $className
     *
     * @param string $className
     * @return Dao
     */
    protected function createDaoInstance($className)
    {
        $result = new $className ($this);

        return $result;
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