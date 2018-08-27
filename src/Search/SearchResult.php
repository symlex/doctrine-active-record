<?php

namespace Doctrine\ActiveRecord\Search;

use Doctrine\ActiveRecord\Exception\NotFoundException;
use Doctrine\ActiveRecord\Model\EntityModel;
use Doctrine\ActiveRecord\Dao\EntityDao;

/**
 * Search result
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class SearchResult implements \ArrayAccess, \Serializable, \IteratorAggregate, \Countable
{
    private $result = array(
        'rows' => array(),
        'order' => false,
        'count' => 0,
        'offset' => 0,
        'total' => 0
    );

    /**
     * SearchResult constructor.
     *
     * @param array $searchResult
     */
    public function __construct(array $searchResult = array())
    {
        $this->result = $searchResult + $this->result;
    }

    /**
     * Returns search result as array.
     *
     * @return array
     */
    public function getAsArray(): array
    {
        return $this->result;
    }

    /**
     * Returns sort order as string.
     *
     * @return string
     */
    public function getSortOrder(): string
    {
        return $this->result['order'];
    }

    /**
     * Returns search count (limit) as integer.
     *
     * @return int
     */
    public function getSearchCount(): int
    {
        return (int)$this->result['count'];
    }

    /**
     * Returns search offset as integer.
     *
     * @return int
     */
    public function getSearchOffset(): int
    {
        return (int)$this->result['offset'];
    }

    /**
     * Returns the number of actual query results (<= limit)
     *
     * @return int
     */
    public function getResultCount(): int
    {
        return count($this->result['rows']);
    }

    /**
     * Alias for getResultCount() (implements \Countable).
     *
     * @return int
     */
    public function count()
    {
        return $this->getResultCount();
    }

    /**
     * Returns total result count (in the database).
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return (int)$this->result['total'];
    }

    /**
     * Returns all results as array of objects.
     *
     * @return EntityDao[]|EntityModel[]
     */
    public function getAllResults(): array
    {
        return $this->result['rows'];
    }

    /**
     * Returns all results as nested array.
     *
     * @return array
     */
    public function getAllResultsAsArray(): array
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
     * Returns first result object or throws an exception.
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

    /**
     * Returns PHP serialized data (implements \Serializable).
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->result);
    }

    /**
     * Sets data from serialized value (implements \Serializable).
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->result = unserialize($data);
    }

    /**
     * Implements \IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->result);
    }

    /**
     * Implements \ArrayAccess.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->result[] = $value;
        } else {
            $this->result[$offset] = $value;
        }
    }

    /**
     * Implements \ArrayAccess.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->result[$offset]);
    }

    /**
     * Implements \ArrayAccess.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->result[$offset]);
    }

    /**
     * Implements \ArrayAccess.
     *
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->result[$offset]) ? $this->result[$offset] : null;
    }
}