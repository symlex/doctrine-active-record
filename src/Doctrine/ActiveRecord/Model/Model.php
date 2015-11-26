<?php

namespace Doctrine\ActiveRecord\Model;

use Doctrine\DBAL\Connection as Db;
use Doctrine\ActiveRecord\Exception\Exception;
use Doctrine\ActiveRecord\Dao\Dao as Dao;

/**
 * Business Models are logically located between the controllers, which render
 * the views and validate user input, and the DAOs, that are the low-level
 * interface to the storage backend. The public interface of models is high-level and
 * should reflect the all use cases for the business domain. There are a number of standard
 * use-cases that are pre-implemented in this base class for your convenience.
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
abstract class Model
{
    private $_db; // Reference to the database connection

    protected $_daoName = ''; // Main data access object (DAO) class name (without prefix)
    protected $_dao; // Reference to DAO instance

    protected $_factoryNamespace = '';
    protected $_factoryPostfix = 'Model';

    protected $_daoFactoryNamespace = '';
    protected $_daoFactoryPostfix = 'Dao';

    /**
     * @param $db Db The current database connection instance
     * @param $dao Dao An instance of a DOA to initialize this instance (otherwise, you must call find/search)
     */
    public function __construct(Db $db, Dao $dao = null)
    {
        $this->setDb($db);

        if (!empty($dao)) {
            $this->setDao($dao);
        }
    }

    private function setDb(Db $db)
    {
        $this->_db = $db;
    }

    private function getDb()
    {
        if (empty($this->_db)) {
            throw new Exception ('Doctrine\DBAL\Connection instance not set');
        }

        return $this->_db;
    }

    /**
     * Creates a new data access object (DAO) instance
     *
     * @param string $name Class name without prefix namespace and postfix
     * @throws Exception
     * @return Dao
     */
    protected function daoFactory($name = '')
    {
        $daoName = empty($name) ? $this->_daoName : $name;

        if (empty($daoName)) {
            throw new Exception ('The DAO factory requires a DAO name');
        }

        $className = $this->_daoFactoryNamespace . '\\' . $daoName . $this->_daoFactoryPostfix;

        $dao = new $className ($this->getDb());

        return $dao;
    }

    /**
     * Returns main DAO instance; automatically creates an instance, if $this->_dao is empty
     *
     * @return Dao
     */
    protected function getDao()
    {
        if (empty($this->_dao)) {
            $this->setDao($this->daoFactory());
        }

        return $this->_dao;
    }

    /**
     * Sets DAO instance
     *
     * @param Dao $dao
     * @return $this
     */
    protected function setDao(Dao $dao)
    {
        $this->_dao = $dao;

        return $this;
    }

    /**
     * Resets the internal DAO reference
     */
    protected function resetDao()
    {
        $this->_dao = $this->daoFactory();
    }

    /**
     * Create a new model instance
     *
     * @param string $name Optional model name (current model name if empty)
     * @param Dao $dao DB DAO instance
     * @throws Exception
     * @return Model
     */
    public function factory($name = '', Dao $dao = null)
    {
        $modelName = empty($name) ? $this->getModelName() : $name;

        if (empty($modelName)) {
            throw new Exception ('The model factory requires a model name');
        }

        $className = $this->_factoryNamespace . '\\' . $modelName . $this->_factoryPostfix;

        $model = new $className ($this->getDb(), $dao);

        return $model;
    }

    /**
     * Returns the model name without prefix and postfix
     *
     * @return string
     */
    public function getModelName()
    {
        $className = get_class($this);

        if ($this->_factoryPostfix != '') {
            $result = substr($className, strlen($this->_factoryNamespace) + 1, strlen($this->_factoryPostfix) * -1);
        } else {
            $result = substr($className, strlen($this->_factoryNamespace) + 1);
        }

        return $result;
    }
}