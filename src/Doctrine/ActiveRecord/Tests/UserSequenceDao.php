<?php

namespace Doctrine\ActiveRecord\Tests;

use Doctrine\ActiveRecord\Entity;
use Doctrine\ActiveRecord\Format;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class UserSequenceDao extends UserDao
{
    protected $_primaryKeySequence = "test_seq";
}
