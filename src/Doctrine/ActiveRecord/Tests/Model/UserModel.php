<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\EntityModel;

class UserModel extends EntityModel {
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_daoName = 'Doctrine\ActiveRecord\Tests\Dao\User';

    public function setEmail ($email) {
        $this->getEntityDao()->email = $email;
    }
}