<?php

namespace Doctrine\ActiveRecord\Tests;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class EntityTest extends UnitTestCase
{
    /**
     * @var UserDao
     */
    protected $dao;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->dao = new UserDao ($db);
    }

    /**
     * @expectedException \Doctrine\ActiveRecord\NotFoundException
     */
    public function testFindNotFoundException()
    {
        $this->dao->find(45345);
    }

    public function testFind()
    {
        $user = $this->dao->factory('User');

        $user->find(1);

        $this->assertEquals('Foo', $user->username);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals(true, $user->active);
    }

    public function testSequenceDefaultsNull()
    {
        // Create a stub for the SomeClass class.
        $db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('lastInsertId')->with(null);

        $dao = new UserDao ($db);
        $dao->setData(["username" => "seq"]);
        $dao->insert();
    }

    public function testSequenceName()
    {
        // Create a stub for the SomeClass class.
        $db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('lastInsertId')->with("test_seq");

        $dao = new UserSequenceDao ($db);
        $dao->setData(["username" => "seq"]);
        $dao->insert();
    }

    public function testSearch()
    {
        /**
         * @var \Doctrine\ActiveRecord\Tests\UserDao
         */
        $user = $this->dao->factory('User');

        $params = array('cond' => array('username' => 'Foo'));

        $result = $user->search($params);

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filter_sql', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('table_pk', $result);
        $this->assertArrayHasKey('table_alias', $result);
        $this->assertEquals(20, $result['count']);
        $this->assertEquals(0, $result['offset']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('id', $result['table_pk']);
        $this->assertEquals('u', $result['table_alias']);
        $this->assertInternalType('array', $result['rows']);

        $user = $result['rows'][0];
        $this->assertEquals('Foo', $user->username);

        $this->assertContains('AND (active = 1)', $result['sql']);
    }

    public function testSearchCountTotal()
    {
        /**
         * @var \Doctrine\ActiveRecord\Tests\UserDao
         */
        $user = $this->dao->factory('User');

        $params = array('cond' => array(), 'count_total' => false);

        $result = $user->search($params);

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filter_sql', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('table_pk', $result);
        $this->assertArrayHasKey('table_alias', $result);
        $this->assertEquals(20, $result['count']);
        $this->assertEquals(0, $result['offset']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('id', $result['table_pk']);
        $this->assertEquals('u', $result['table_alias']);
        $this->assertInternalType('array', $result['rows']);
        $this->assertCount(1, $result['rows']);
    }
}
