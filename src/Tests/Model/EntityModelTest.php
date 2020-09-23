<?php

namespace Doctrine\ActiveRecord\Tests\Model;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class EntityModelTest extends UnitTestCase
{
    /**
     * @var UserModel
     */
    protected $model;

    protected function setUp(): void
    {
        /**
         * @var \Doctrine\ActiveRecord\Model\Factory
         */
        $factory = $this->get('model.factory');
        $this->model = $factory->create('User');
    }

    public function testFactory()
    {
        $userModel = $this->model->createModel('User');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\UserModel', $userModel);

        $simpleModel = $this->model->createModel('Simple');
        $this->assertInstanceOf('Doctrine\ActiveRecord\Tests\Model\SimpleModel', $simpleModel);
    }

    public function testGetModelName()
    {
        $this->assertEquals('User', $this->model->getModelName());
    }

    public function testFindNotFoundException()
    {
        $this->expectException('\Doctrine\ActiveRecord\Exception\NotFoundException');
        $this->model->find(45345);
    }

    public function testFind()
    {
        $user = $this->model->createModel('User');

        $user->find(1);

        $this->assertEquals('Foo', $user->username);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals(true, $user->active);
    }

    public function testReload()
    {
        $user = $this->model;

        $user->find(1);

        $this->assertEquals('foo@bar.com', $user->email);

        $user->setEmail('bill@gates.com');

        $this->assertEquals('bill@gates.com', $user->email);

        $user->reload();

        $this->assertEquals('foo@bar.com', $user->email);
    }

    public function testFindAll()
    {
        $users = $this->model->findAll();

        $this->assertContainsOnlyInstancesOf('\Doctrine\ActiveRecord\Tests\Model\UserModel', $users);

        $usersArray = $this->model->findAll(array(), false);

        $this->assertIsArray($usersArray);
        $this->assertCount(5, $usersArray);

        $emptyUsersArray = $this->model->findAll(array('username' => 'XXX'), false);

        $this->assertIsArray($emptyUsersArray);
        $this->assertCount(0, $emptyUsersArray);
    }

    public function testSearch()
    {
        $result = $this->model->search(array('username' => 'Foo'), array());

        $this->assertInstanceOf('\Doctrine\ActiveRecord\Search\SearchResult', $result);
        $this->assertEquals(false, $result['order']);
        $this->assertEquals(20, $result['count']);
        $this->assertEquals(0, $result['offset']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(false, $result->getSortOrder());
        $this->assertEquals(20, $result->getSearchCount());
        $this->assertEquals(0, $result->getSearchOffset());
        $this->assertEquals(1, $result->getTotalCount());
        $this->assertEquals("SELECT u.* FROM users u WHERE `u`.`username` = 'Foo'", $result['filter_sql']);
        $this->assertEquals("SELECT u.* FROM users u WHERE (`u`.`username` = 'Foo') AND (active = 1) LIMIT 20", $result['sql']);
        $this->assertEquals('id', $result['table_pk']);
        $this->assertEquals('u', $result['table_alias']);
        $this->assertContainsOnlyInstancesOf('\Doctrine\ActiveRecord\Tests\Model\UserModel', $result['rows']);
        $this->assertCount(1, $result['rows']);
        $this->assertIsArray($result->getAllResultsAsArray()[0]);
    }

    public function searchAll()
    {
        $result = $this->model->searchAll(array('username' => 'Foo'), array());
        $this->assertContainsOnlyInstancesOf('\Doctrine\ActiveRecord\Tests\Model\UserModel', $result);
        $this->assertCount(1, $result);
    }

    public function searchOne()
    {
        $result = $this->model->searchOne(array('username' => 'Foo'));
        $this->assertInstanceOf('\Doctrine\ActiveRecord\Tests\Model\UserModel', $result);
    }

    public function testSearchIds()
    {
        $result = $this->model->searchIds(array('username' => 'Foo'));

        $this->assertInstanceOf('\Doctrine\ActiveRecord\Search\SearchResult', $result);
        $this->assertCount(1, $result['rows']);
        $this->assertEquals(1, $result['rows'][0]);
        $this->assertEquals(false, $result['order']);
        $this->assertEquals(20, $result['count']);
        $this->assertEquals(0, $result['offset']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(false, $result->getSortOrder());
        $this->assertEquals(20, $result->getSearchCount());
        $this->assertEquals(0, $result->getSearchOffset());
        $this->assertEquals(1, $result->getTotalCount());
        $this->assertEquals("SELECT u.id FROM users u WHERE `u`.`username` = 'Foo'", $result['filter_sql']);
        $this->assertEquals("SELECT u.id FROM users u WHERE (`u`.`username` = 'Foo') AND (active = 1) LIMIT 20", $result['sql']);
        $this->assertEquals('id', $result['table_pk']);
        $this->assertEquals('u', $result['table_alias']);
    }

    public function testGetId()
    {
        $this->model->find(1);
        $this->assertEquals(1, $this->model->getId());
    }

    public function testHasId()
    {
        $this->assertEquals(false, $this->model->hasId());
        $this->model->find(1);
        $this->assertEquals(true, $this->model->hasId());
    }

    public function testGetValues()
    {
        $this->model->find(1);
        $values = $this->model->getValues();

        $this->assertIsArray($values);
        $this->assertArrayNotHasKey('password', $values);
        $this->assertEquals(1, $values['id']);
        $this->assertEquals('Foo', $values['username']);
        $this->assertEquals('foo@bar.com', $values['email']);
        $this->assertEquals(1, $values['active']);
        $this->assertInstanceOf('DateTime', $values['created']);
        $this->assertInstanceOf('DateTime', $values['updated']);
        $this->assertEquals('2013-11-04', $values['created']->format('Y-m-d'));
        $this->assertEquals('2016-01-22 23:01:42', $values['updated']->format('Y-m-d H:m:i'));
    }

    public function testGetEntityTitle()
    {
        $this->model->find(1);
        $result = $this->model->getEntityTitle();
        $expected = 'User 1';
        $this->assertEquals($expected, $result);
    }

    public function testIsDeletable()
    {
        $result = $this->model->isDeletable();
        $this->assertTrue($result);
    }

    public function testIsCreatable()
    {
        $result = $this->model->isSavable();
        $this->assertTrue($result);
    }

    public function testIsUpdatable()
    {
        $result = $this->model->isUpdatable();
        $this->assertTrue($result);
    }

    public function testGetTableName()
    {
        $result = $this->model->getTableName();
        $this->assertEquals('users', $result);
    }

    public function testHasTimestampEnabled()
    {
        $result = $this->model->hasTimestampEnabled();
        $this->assertTrue($result);
    }
}