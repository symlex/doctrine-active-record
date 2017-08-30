<?php

namespace Doctrine\ActiveRecord\Search;

use Doctrine\ActiveRecord\Exception\NotFoundException;
use Doctrine\ActiveRecord\Model\EntityModel;
use Doctrine\ActiveRecord\Dao\EntityDao;

/**
 * Search result object
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class SearchResult implements \ArrayAccess, \Serializable, \IteratorAggregate
{
    private $result = array(
        'rows' => array(),
        'order' => false,
        'count' => 0,
        'offset' => 0,
        'total' => 0
    );

    public function __construct(array $searchResult = array())
    {
        $this->result = $searchResult + $this->result;
    }

    /**
     * Returns PHP serialized data
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->result);
    }

    public function unserialize($data)
    {
        $this->result = unserialize($data);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->result);
    }

    /**
     * Returns search result as array
     *
     * @return array
     */
    public function getAsArray()
    {
        return $this->result;
    }

    /**
     * Returns sort order as string
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->result['order'];
    }

    /**
     * Returns search count as integer
     *
     * @return integer
     */
    public function getSearchCount()
    {
        return (int)$this->result['count'];
    }

    /**
     * Returns search offset as integer
     *
     * @return integer
     */
    public function getSearchOffset()
    {
        return (int)$this->result['offset'];
    }

    /**
     * @return integer
     */
    public function getResultCount()
    {
        return count($this->result['rows']);
    }

    /**
     * Returns actual result count as integer
     *
     * @return integer
     */
    public function getTotalCount()
    {
        return (int)$this->result['total'];
    }

    /**
     * Returns all results as array of objects
     *
     * @return EntityDao[]|EntityModel[]
     */
    public function getAllResults()
    {
        return $this->result['rows'];
    }

    /**
     * Returns all results as nested array
     *
     * @return array
     */
    public function getAllResultsAsArray()
    {
        $result = array();

        $entities = $this->getAllResults();

        // Convert search results to array
        foreach ($entities as $entity) {
            if (is_object($entity) && method_exists($entity, 'getValues')) {
                $result[] = $entity->getValues();
            } else {
                $result[] = (array)$entity;
            }
        }

        return $result;
    }

    /**
     * Returns first result object or throws an exception
     *
     * @return EntityDao|EntityModel
     * @throws NotFoundException
     */
    public function getFirstResult()
    {
        if ($this->getResultCount() == 0) {
            throw new NotFoundException('Search result is empty');
        }

        return $this->result['rows'][0];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->result[] = $value;
        } else {
            $this->result[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->result[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->result[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->result[$offset]) ? $this->result[$offset] : null;
    }
}