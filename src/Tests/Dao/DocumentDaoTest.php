<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class DocumentDaoTest extends UnitTestCase
{
    /**
     * @var DocumentDao
     */
    protected $dao;

    protected function setUp(): void
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
        $this->dao->save();
        $this->assertTrue(true);
    }
}