<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\ActiveRecord\Dao\Factory;
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

    protected function setUp(): void
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
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->create('Doctrine\ActiveRecord\Tests\Dao\TestDao'));

        $this->factory->setFactoryNamespace('');
        $this->factory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->create('Doctrine\ActiveRecord\Tests\Dao\Test'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->factory->setFactoryPostfix('Dao');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->create('Test'));

        $this->factory->setFactoryNamespace('Doctrine\ActiveRecord\Tests\Dao');
        $this->factory->setFactoryPostfix('');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Dao\TestDao', $this->factory->create('TestDao'));
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

    public function testGetDaoException()
    {
        $this->expectException('\Doctrine\ActiveRecord\Exception\FactoryException');
        $this->factory->create('FooBar');
    }
}