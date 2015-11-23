<?php

namespace Doctrine\ActiveRecord\Tests;

use Doctrine\ActiveRecord\Model;

class UserModel extends Model {
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_daoName = 'Doctrine\ActiveRecord\Tests\User';
}