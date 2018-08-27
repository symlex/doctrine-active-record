<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class UserSequenceDao extends UserDao
{
    protected $_primaryKeySequence = "test_seq";
}
