<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\BusinessModel;

class UserModel extends BusinessModel {
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_daoName = 'Doctrine\ActiveRecord\Tests\Dao\User';
}