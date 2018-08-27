<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use Doctrine\ActiveRecord\Model\Factory;
use Doctrine\ActiveRecord\Dao\Factory as DaoFactory;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class FactoryTest extends UnitTestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var DaoFactory
     */
    protected $daoFactory;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->daoFactory = new DaoFactory($db);
        $this->factory = new Factory ($this->daoFactory);
    }

    public function testCreateDao()
    {
        $this->daoFactory->setFactoryNamespace('');
        $this->daoFactory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Doctrine\ActiveRecord\Tests\Dao\TestDao'));

        $this->daoFactory->setFactoryNamespace('');
        $this->daoFactory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Doctrine\ActiveRecord\Tests\Dao\Test'));

        $this->daoFactory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->daoFactory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Test'));

        $this->daoFactory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->daoFactory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('TestDao'));
    }

    public function testCreateModel()
    {
        $this->factory->setFactoryNamespace('');
        $this->factory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $this->factory->create('Doctrine\ActiveRecord\Tests\Model\UserModel'));

        $this->factory->setFactoryNamespace('');
        $this->factory->setFactoryPostfix('Model');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $this->factory->create('Doctrine\ActiveRecord\Tests\Model\User'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Model');
        $this->factory->setFactoryPostfix('Model');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $this->factory->create('User'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Model');
        $this->factory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $this->factory->create('UserModel'));
    }

    public function testGetFactoryNamespace()
    {
        $this->assertEquals('', $this->factory->getFactoryNamespace());
        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Model');
        $this->assertEquals('\Doctrine\ActiveRecord\Tests\Model', $this->factory->getFactoryNamespace());
    }

    public function testGetFactoryPostfix()
    {
        $this->assertEquals('Model', $this->factory->getFactoryPostfix());
        $this->factory->setFactoryPostfix('');
        $this->assertEquals('', $this->factory->getFactoryPostfix());
    }

    /**
     * @expectedException \Doctrine\ActiveRecord\Exception\FactoryException
     */
    public function testCreateDaoException()
    {
        $this->factory->createDao('FooBar');
    }

    /**
     * @expectedException \Doctrine\ActiveRecord\Exception\FactoryException
     */
    public function testCreateModelException()
    {
        $this->factory->create('FooBar');
    }
}