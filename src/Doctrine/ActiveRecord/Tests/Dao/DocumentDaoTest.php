<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class DocumentDaoTest extends UnitTestCase
{
    /**
     * @var DocumentDao
     */
    protected $dao;

    public function setUp()
    {
        DocumentDao::setDateTimeClassName('\TestTools\Util\FixedDateTime');
        $factory = $this->get('dao.factory');
        $this->dao = new DocumentDao($factory);
    }

    public function testInsert()
    {
        $this->dao->title = 'Foo';
        $this->dao->filename = 'foo.txt';
        $this->dao->unique = 0;
        $this->dao->insert();
    }
}