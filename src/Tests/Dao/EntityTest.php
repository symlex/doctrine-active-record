<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\ActiveRecord\Dao\Factory;
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
        UserDao::setDateTimeClassName('\TestTools\Util\FixedDateTime');
        $factory = $this->get('dao.factory');
        $this->dao = new UserDao ($factory);
    }

    /**
     * @expectedException \Doctrine\ActiveRecord\Exception\NotFoundException
     */
    public function testFindNotFoundException()
    {
        $this->dao->find(45345);
        $this->assertTrue(true);
    }

    public function testFind()
    {
        $user = $this->dao->createDao('User');

        $user->find(1);

        $this->assertEquals('Foo', $user->username);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals(true, $user->active);
    }

    public function testIsset()
    {
        $user = $this->dao->createDao('User');

        $user->find(1);

        $this->assertTrue(isset($user->username));
        $this->assertTrue(isset($user->email));
        $this->assertTrue(isset($user->active));
        $this->assertFalse(isset($user->foobar));

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

        $factory = new Factory($db);

        $dao = new UserDao ($factory);
        $dao->setData(["username" => "seq"]);
        $dao->save();
        $this->assertTrue(true);
    }

    public function testSequenceName()
    {
        // Create a stub for the SomeClass class.
        $db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('lastInsertId')->with("test_seq");

        $factory = new Factory($db);

        $dao = new UserSequenceDao ($factory);
        $dao->setData(["username" => "seq"]);
        $dao->save();
        $this->assertTrue(true);
    }

    public function testSearch()
    {
        /**
         * @var \Doctrine\ActiveRecord\Tests\Dao\UserDao
         */
        $user = $this->dao->createDao('User');

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
         * @var \Doctrine\ActiveRecord\Tests\Dao\UserDao
         */
        $user = $this->dao->createDao('User');

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
        $this->assertGreaterThanOrEqual(2, $result['total']);
        $this->assertEquals('id', $result['table_pk']);
        $this->assertEquals('u', $result['table_alias']);
        $this->assertInternalType('array', $result['rows']);
        $this->assertGreaterThanOrEqual(2, $result['rows']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInsertInvalidArguments()
    {
        $user = $this->dao->createDao('User');
        $user->save('foo');
    }

    public function testInsert()
    {
        $user = $this->dao->createDao('User');

        $user->username = 'foobar123';
        $user->save();
        $this->assertTrue(true);
    }

    public function testInsertTimestamp()
    {
        $user = $this->dao->createDao('User');

        $user->username = 'foobar234';
        $user->created = new \DateTime('2016-07-13T18:30:08Z');
        $user->save();
        $this->assertTrue(true);
    }

    public function testUpdate()
    {
        $user = $this->dao->createDao('User');

        $user->find(array('username' => 'Foo'));
        $user->active = false;
        $user->update();
        $user->active = true;
        $user->update();
        $this->assertTrue(true);
    }
}
