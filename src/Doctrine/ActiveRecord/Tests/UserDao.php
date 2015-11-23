<?php

namespace Doctrine\ActiveRecord\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Entity;
use Doctrine\ActiveRecord\Format;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class UserDao extends Entity
{
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_tableName = 'users';
    protected $_primaryKey = 'id';
    protected $_timestampEnabled = true;
    protected $_formatMap = array(
        'id' => Format::INT,
        'username' => Format::STRING,
        'email' => Format::STRING,
        'active' => Format::BOOL,
        'updated' => Format::DATETIME,
        'created' => Format::DATETIME
    );

    protected function optimizeSearchQuery (QueryBuilder $query, array $params) {
        $query->andWhere('active = 1');
        return $query;
    }
}
