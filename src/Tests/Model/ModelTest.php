<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class ModelTest extends UnitTestCase
{
    /**
     * @var SimpleModel
     */
    protected $model;

    public function setUp()
    {
        $factory = $this->get('model.factory');
        $this->model = new SimpleModel ($factory);
    }

    public function testType()
    {
        $this->assertInstanceOf('Doctrine\ActiveRecord\Model\Model', $this->model);
    }

    public function testFactory()
    {
        $userModel = $this->model->createModel('User');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $userModel);
    }

    public function testGetModelName()
    {
        $this->assertEquals('Simple', $this->model->getModelName());
    }

    public function testGetTables()
    {
        $result = $this->model->getTables();

        $this->assertInternalType('array', $result);

        $this->assertEquals('documents', $result[0]);
    }
}