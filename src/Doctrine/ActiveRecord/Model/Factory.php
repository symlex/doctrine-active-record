<?php

namespace Doctrine\ActiveRecord\Model;

use Doctrine\ActiveRecord\Exception\FactoryException;
use Doctrine\ActiveRecord\Dao\Dao as Dao;
use Doctrine\ActiveRecord\Dao\Factory as DaoFactory;
use Doctrine\ActiveRecord\Factory\FactoryAbstract;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Factory extends FactoryAbstract
{
    /**
     * Private reference to the DAO factory
     *
     * @var DaoFactory
     */
    protected $_daoFactory;

    /**
     * Namespace used by Model instance factory method
     *
     * @var string
     */
    protected $_factoryNamespace = '';

    /**
     * Class name postfix by Model instance factory method
     *
     * @var string
     */
    protected $_factoryPostfix = 'Model';

    /**
     * @param DaoFactory $daoFactory DAO factory instance
     */
    public function __construct(DaoFactory $daoFactory)
    {
        $this->setDaoFactory($daoFactory);
    }

    /**
     * @param DaoFactory $daoFactory
     */
    protected function setDaoFactory(DaoFactory $daoFactory)
    {
        $this->_daoFactory = $daoFactory;
    }

    /**
     * @return DaoFactory
     */
    protected function getDaoFactory()
    {
        return $this->_daoFactory;
    }

    /**
     * Creates a new data access object (DAO) instance
     *
     * @param string $name Class name without prefix namespace and postfix
     * @throws FactoryException
     * @return Dao
     */
    public function getDao($name = '')
    {
        if (empty($name)) {
            throw new FactoryException ('The DAO factory requires a DAO name');
        }

        $result = $this->getDaoFactory()->getDao($name);

        return $result;
    }

    /**
     * Creates a new model instance
     *
     * @param string $name Optional model name (current model name if empty)
     * @param Dao $dao DB DAO instance
     * @throws FactoryException
     * @return Model
     */
    public function getModel($name, Dao $dao = null)
    {
        $className = $this->getClassName($name);

        $result = $this->createModelInstance($className, $dao);

        return $result;
    }

    /**
     * Returns new model instance of $className
     *
     * @param string $className
     * @param Dao $dao
     * @return Model
     */
    protected function createModelInstance($className, $dao)
    {
        $result = new $className ($this, $dao);

        return $result;
    }
}