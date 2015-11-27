<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\ActiveRecord\Dao\Dao;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class TestDao extends Dao
{
    protected $_factoryNamespace = __NAMESPACE__;

    public function getTables()
    {
        $statement = 'SHOW TABLES';
        $result = $this->fetchCol($statement);
        return $result;
    }

    public function describeUsersTable()
    {
        return $this->describeTable('users');
    }

    /**
     * Note: For testing only
     *
     * @param $statement
     * @return array
     * @throws \Doctrine\ActiveRecord\Exception\Exception
     */
    public function publicFetchAll($statement)
    {
        return $this->fetchAll($statement);
    }

    /**
     * Note: For testing only
     *
     * @param $statement
     * @return array
     * @throws \Doctrine\ActiveRecord\Exception\Exception
     */
    public function publicFetchPairs($statement)
    {
        return $this->fetchPairs($statement);
    }

    /**
     * Note: For testing only
     *
     * @param $statement
     * @return mixed
     * @throws \Doctrine\ActiveRecord\Exception\Exception
     */
    public function publicFetchSingleValue($statement)
    {
        return $this->fetchSingleValue($statement);
    }

    /**
     * Note: For testing only
     *
     * @param $statement
     * @return array
     * @throws \Doctrine\ActiveRecord\Exception\Exception
     */
    public function publicFetchCol($statement)
    {
        return $this->fetchCol($statement);
    }
}