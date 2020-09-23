<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class DaoTest extends UnitTestCase
{
    /**
     * @var TestDao
     */
    protected $dao;

    protected function setUp(): void
    {
        $factory = $this->get('dao.factory');
        $this->dao = $factory->create('Test');
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
            'password' => 'varchar(255)',
            'active' => 'tinyint(4)',
            'created' => 'datetime',
            'updated' => 'datetime',
        );

        $result = $this->dao->describeUsersTable();

        $this->assertEquals($expected, $result);
    }

    public function testFactory()
    {
        $result = $this->dao->createDao('Test');

        $this->assertInstanceOf('\Doctrine\ActiveRecord\Tests\Dao\TestDao', $result);
    }

    public function testPublicFetchAll()
    {
        $expected = array(
            0 =>
                array(
                    'id' => '1',
                    'username' => 'Foo',
                    'email' => 'foo@bar.com',
                    'password' => 'abc',
                    'active' => '1',
                    'created' => '2013-11-04 18:34:49',
                    'updated' => '2013-11-04 19:34:49',
                ),
            1 =>
                array(
                    'id' => '2',
                    'username' => 'Michael',
                    'email' => 'michael@bar.com',
                    'password' => 'abc',
                    'active' => '1',
                    'created' => '2013-11-05 18:34:49',
                    'updated' => '2013-11-06 18:34:49',
                ),
            2 =>
                array(
                    'id' => '3',
                    'username' => 'Alex',
                    'email' => 'alex@bar.com',
                    'password' => 'abc',
                    'active' => '0',
                    'created' => '2013-11-06 18:34:49',
                    'updated' => '2013-11-07 18:34:49',
                ),
        );

        $result = $this->dao->publicFetchAll('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testPublicFetchPairs()
    {
        $expected = array(
            1 => 'Foo',
            2 => 'Michael',
            3 => 'Alex',
        );

        $result = $this->dao->publicFetchPairs('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testPublicFetchSingleValue()
    {
        $expected = 1;

        $result = $this->dao->publicFetchSingleValue('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testPublicFetchCol()
    {
        $expected = array(
            0 => 1,
            1 => 2,
            2 => 3,
        );

        $result = $this->dao->publicFetchCol('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testQuoteIdentifier ()
    {
        $db = $this->get('dbal.connection');

        $identifier = 'A';

        $quotedIdentifier = $db->quoteIdentifier($identifier);

        $this->assertEquals('`' . $identifier . '`', $quotedIdentifier);

        $identifier = 'a';

        $quotedIdentifier = $db->quoteIdentifier($identifier);

        $this->assertEquals('`' . $identifier . '`', $quotedIdentifier);
    }
}