<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\Model;

class SimpleModel extends Model {
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_daoName = 'Doctrine\ActiveRecord\Tests\Dao\Test';

    public function getTables () {
        return $this->getDao()->getTables();
    }
}