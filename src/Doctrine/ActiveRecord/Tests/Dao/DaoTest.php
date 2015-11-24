<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class DaoTest extends UnitTestCase
{
    /**
     * @var TestDao
     */
    protected $dao;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->dao = new TestDao ($db);
    }

    public function testGetTables()
    {
        $expected = array(
            'documents',
            'userAddresses',
            'userDocuments',
            'users',
        );

        $result = $this->dao->getTables();

        $this->assertEquals($expected, $result);
    }

    public function testDescribeUsersTable()
    {
        $expected = array(
            'id' => 'int(11)',
            'username' => 'varchar(45)',
            'email' => 'varchar(120)',
            'active' => 'tinyint(4)',
            'created' => 'datetime',
            'updated' => 'datetime',
        );

        $result = $this->dao->describeUsersTable();

        $this->assertEquals($expected, $result);
    }

    public function testFactory()
    {
        $result = $this->dao->factory('Test');

        $this->assertInstanceOf('\Doctrine\ActiveRecord\Tests\Dao\TestDao', $result);
    }
}