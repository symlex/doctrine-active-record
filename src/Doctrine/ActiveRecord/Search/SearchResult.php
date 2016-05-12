<?php

namespace Doctrine\ActiveRecord\Search;

use Doctrine\ActiveRecord\Exception\NotFoundException;

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

    public function serialize()
    {
        return serialize($this->result);
    }

    public function unserialize($data)
    {
        $this->result = unserialize($data);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->result);
    }

    public function getAsArray()
    {
        return $this->result;
    }

    public function getSortOrder()
    {
        return $this->result['order'];
    }

    public function getSearchCount()
    {
        return $this->result['count'];
    }

    public function getSearchOffset()
    {
        return $this->result['offset'];
    }

    public function getResultCount()
    {
        return count($this->result['rows']);
    }

    public function getTotalCount()
    {
        return $this->result['total'];
    }

    public function getAllResults()
    {
        return $this->result['rows'];
    }

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