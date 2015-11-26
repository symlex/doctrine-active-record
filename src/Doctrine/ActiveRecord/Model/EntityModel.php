<?php

namespace Doctrine\ActiveRecord\Model;

use Doctrine\DBAL\Connection as Db;
use Doctrine\ActiveRecord\Exception\Exception;
use Doctrine\ActiveRecord\Exception\ModelException;
use Doctrine\ActiveRecord\Exception\FindException;
use Doctrine\ActiveRecord\Exception\CreateException;
use Doctrine\ActiveRecord\Exception\UpdateException;
use Doctrine\ActiveRecord\Exception\DeleteException;
use Doctrine\ActiveRecord\Exception\NotFoundException;
use Doctrine\ActiveRecord\Dao\EntityDao as Dao;

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
abstract class EntityModel extends Model
{
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
        parent::__construct($db, $dao);
    }

    /**
     * Returns entity DAO
     *
     * @throws ModelException
     * @return Dao
     */
    protected function getEntityDao()
    {
        $dao = $this->getDao();

        if($dao instanceof Dao) {
            return $dao;
        }

        throw new ModelException('DAO is not an EntityDao');
    }

    /**
     * Find a record by primary key
     *
     * @param int $id
     * @return $this
     */
    public function find($id)
    {
        $this->getEntityDao()->find($id);

        return $this;
    }

    /**
     * Reload values from database
     *
     * @return $this
     */
    public function reload()
    {
        $this->getEntityDao()->reload();

        return $this;
    }

    /**
     * @param array $cond
     * @param bool $wrapResult
     * @throws FindException
     * @return array
     */
    public function findAll(array $cond = array(), $wrapResult = true)
    {
        $result = $this->getEntityDao()->findAll($cond, $wrapResult);

        if (!is_array($result)) {
            throw new FindException('DAO findAll() return value is not an array');
        }

        if ($wrapResult) {
            $result = $this->wrapAll($result);
        }

        return $result;
    }

    /**
     * Wraps all DAO result entities in model instances
     *
     * @param array $result
     * @return array
     */
    protected function wrapAll(array $result)
    {
        $modelName = $this->getModelName();

        foreach ($result as &$entity) {
            $entity = $this->factory($modelName, $entity);
        }

        return $result;
    }

    /**
     * Perform a search ($options can contain count, offset and/or sort order; the return value array
     * also contains count, offset, sort order plus the total number of results; see DAO documentation)
     *
     * @param array $cond The search conditions as array
     * @param array $options The optional search options as array
     * @throws FindException
     * @return array
     */
    public function search(array $cond, array $options = array())
    {
        $params = $options + array('cond' => $cond);

        $result = $this->getEntityDao()->search($params);

        if (!is_array($result)) {
            throw new FindException('DAO search() return value is not an array');
        }

        if (!isset($options['ids_only']) || $options['ids_only'] == false) {
            $result['rows'] = $this->wrapAll($result['rows']);
        }

        return $result;
    }

    /**
     * Simple version of search(), similar to findAll()
     *
     * @param array $cond The search conditions as array
     * @param mixed $order The sort order (use an array for multiple columns)
     * @return array
     */
    public function searchAll(array $cond = array(), $order = false)
    {
        $options = array(
            'order' => $order,
            'count' => 0,
            'offset' => 0
        );

        $result = $this->search($cond, $options);

        return $result['rows'];
    }

    /**
     * Search a single record; throws an exception if 0 or more than one record are found
     *
     * @param array $cond The search conditions as array
     * @throws NotFoundException
     * @return array
     */
    public function searchOne(array $cond = array())
    {
        $options = array(
            'count' => 1,
            'offset' => 0
        );

        $result = $this->search($cond, $options);

        if ($result['total'] != 1) {
            throw new NotFoundException($result['total'] . ' matching items found');
        }

        return $result['rows'][0];
    }

    /**
     * Returns an array of matching primary keys for the given search condition
     *
     * @param array $cond
     * @param array $options
     * @throws FindException
     * @return array
     */
    public function searchIds(array $cond, array $options = array())
    {
        $params = $options + array('cond' => $cond);

        $params['ids_only'] = true;

        $result = $this->getEntityDao()->search($params);

        if (!is_array($result)) {
            throw new FindException('DAO search() return value is not an array');
        }

        return $result;
    }

    /**
     * Return the ID of the currently loaded entity (throws exception, if empty)
     *
     * @return mixed Primary key
     */
    public function getId()
    {
        return $this->getEntityDao()->getId();
    }

    /**
     * Returns true, if the model instance has an ID assigned (primary key)
     *
     * @return bool
     */
    public function hasId()
    {
        return $this->getEntityDao()->hasId();
    }

    /**
     * Return all model instance values
     * Note: Result is empty for new instances: you must assign values or call find/search first!
     *
     * @return array Model property values
     */
    public function getValues()
    {
        return $this->getEntityDao()->getValues();
    }

    /**
     * Return the common name of this entity (for lists or box titles)
     *
     * Should be overwritten by inherited classes
     *
     * @return string
     */
    public function getEntityTitle()
    {
        return $this->_daoName . ' ' . $this->getId();
    }

    /**
     * Returns true, if this model instance can be deleted
     * (not related to user's specific rights, which can be different)
     *
     * @return bool
     */
    public function isDeletable()
    {
        return true;
    }

    /**
     * Returns true, if this model instance can be updated
     * (not related to user's specific rights, which can be different)
     *
     * @return bool
     */
    public function isUpdatable()
    {
        return true;
    }

    /**
     * Returns true, if new entities can be created in the database
     * (not related to user's specific rights, which can be different)
     *
     * @return bool
     */
    public function isCreatable()
    {
        return true;
    }

    /**
     * Update the data of multiple DAO instances
     *
     * @param array $ids The IDs (primary keys) of the entities to be changed
     * @param array $properties The properties to be changed
     * @throws UpdateException
     * @return object this
     */
    public function batchEdit(array $ids, array $properties)
    {
        $this->getEntityDao()->beginTransaction();

        try {
            foreach ($ids as $id) {
                $dao = $this->daoFactory()->find($id);

                foreach ($properties as $key => $value) {
                    $dao->$key = $value;
                }

                $dao->update();
            }

            $this->getEntityDao()->commit();
        } catch (Exception $e) {
            $this->getEntityDao()->rollBack();

            throw new UpdateException ('Batch edit was not successful: ' . $e->getMessage());
        }


        return $this;
    }

    /**
     * Returns the name of the associated main database table
     * Note: Needed for search filters or security checks
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->getEntityDao()->getTableName();
    }

    /**
     * Magic getter
     *
     */
    public function __get($name)
    {
        return $this->getEntityDao()->$name;
    }

    /**
     * Magic setter
     *
     * Throws exception, because Models should implement use cases and not just
     * change data based on field names. Each specific use case needs a separate
     * function.
     */
    public function __set($name, $value)
    {
        throw new ModelException (
            'A use case specific method must be implemented to change any ' .
            'model data. Magic setters are therefore not available. ' .
            'Model: ' . $this->getModelName() . ', Property: ' . $name
        );
    }

    /**
     * Returns true, if timestamps are enabled in the associated main DAO
     *
     * @return bool
     */
    public function hasTimestampEnabled()
    {
        return $this->getEntityDao()->hasTimestampEnabled();
    }

    /**
     * Deletes the stored data without any checks
     */
    protected function _delete()
    {
        $dao = $this->getEntityDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            $dao->delete();

            $dao->commit();
        } catch (Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }
    }

    /**
     * Permanently deletes the entity instance
     *
     * @throws DeleteException
     */
    public function delete()
    {
        if (!$this->hasId() || !$this->isDeletable()) {
            throw new DeleteException('Entity can not be deleted');
        }

        $this->_delete();
        $this->resetDao();
        return $this;
    }

    /**
     * Updates entity data
     *
     * @param array $values
     * @throws \Exception
     * @return EntityModel
     */
    public function update(array $values)
    {
        if (!$this->hasId() || !$this->isUpdatable()) {
            throw new UpdateException('Entity can not be updated');
        }

        $dao = $this->getEntityDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            $this->_update($values);

            $dao->commit();
        } catch (\Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }

        return $this;
    }

    protected function _update(array $values)
    {
        $dao = $this->getEntityDao();

        $dao->setValues($values);

        $dao->update();
    }

    /**
     * Permanently store entity data
     *
     * @param array $values
     * @throws \Exception
     * @return EntityModel
     */
    public function create(array $values)
    {
        if (!$this->isCreatable()) {
            throw new CreateException('New entities can not be created');
        }

        $dao = $this->getEntityDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            $this->_create($values);

            $dao->commit();
        } catch (\Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }

        return $this;
    }

    protected function _create(array $values)
    {
        $dao = $this->getEntityDao();

        $dao->setValues($values);

        $dao->insert();

        $dao->reload();
    }
}