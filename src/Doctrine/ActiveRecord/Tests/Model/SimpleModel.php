<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\Model;

class SimpleModel extends Model {
    protected $_daoName = 'Test';

    public function getTables () {
        return $this->getDao()->getTables();
    }
}