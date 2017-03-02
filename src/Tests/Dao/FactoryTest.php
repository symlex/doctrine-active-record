<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\ActiveRecord\Dao\Factory;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class FactoryTest extends UnitTestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->factory = new Factory ($db);
    }

    public function testGetDb()
    {
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $this->factory->getDb());
    }

    public function testCreateDao()
    {
        $this->factory->setFactoryNamespace('');
        $this->factory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Doctrine\ActiveRecord\Tests\Dao\TestDao'));

        $this->factory->setFactoryNamespace('');
        $this->factory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Doctrine\ActiveRecord\Tests\Dao\Test'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->factory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('Test'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->factory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->createDao('TestDao'));
    }

    public function testGetFactoryNamespace()
    {
        $this->assertEquals('', $this->factory->getFactoryNamespace());
        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->assertEquals('\Doctrine\ActiveRecord\Tests\Dao', $this->factory->getFactoryNamespace());
    }

    public function testGetFactoryPostfix()
    {
        $this->assertEquals('Dao', $this->factory->getFactoryPostfix());
        $this->factory->setFactoryPostfix('');
        $this->assertEquals('', $this->factory->getFactoryPostfix());
    }

    /**
     * @expectedException \Doctrine\ActiveRecord\Exception\FactoryException
     */
    public function testGetDaoException()
    {
        $this->factory->createDao('FooBar');
    }
}