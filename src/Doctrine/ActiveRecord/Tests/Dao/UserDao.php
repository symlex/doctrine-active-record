<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ActiveRecord\Dao\EntityDao;
use Doctrine\ActiveRecord\Dao\Format;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class UserDao extends EntityDao
{
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

    protected function optimizeSearchQuery (QueryBuilder $statement, array $params) {
        $statement->andWhere('active = 1');
        return $statement;
    }
}
