<?php

namespace Doctrine\ActiveRecord\Tests;

use Doctrine\ActiveRecord\Dao;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class TestDao extends Dao
{
    protected $_factoryNamespace = __NAMESPACE__;

    public function getTables()
    {
        $query = 'SHOW TABLES';
        $result = $this->fetchCol($query);
        return $result;
    }

    public function describeUsersTable()
    {
        return $this->describeTable('users');
    }
}