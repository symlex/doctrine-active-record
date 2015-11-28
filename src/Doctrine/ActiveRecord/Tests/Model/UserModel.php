<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\EntityModel;

class UserModel extends EntityModel {
    protected $_daoName = 'User';

    public function setEmail ($email) {
        $this->getEntityDao()->email = $email;
    }
}