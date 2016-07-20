<?php

namespace Doctrine\ActiveRecord\Tests\Dao;

use Doctrine\ActiveRecord\Dao\EntityDao;
use Doctrine\ActiveRecord\Dao\Format;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class DocumentDao extends EntityDao
{
    protected $_tableName = 'documents';
    protected $_primaryKey = 'id';
    protected $_timestampEnabled = true;
    protected $_timestampUpdatedCol = 'modified';
    protected $_formatMap = array(
        'id' => Format::INT,
        'title' => Format::STRING,
        'filename' => Format::STRING,
        'unique' => Format::BOOL,
        'modified' => Format::DATETIME,
        'created' => Format::DATETIME
    );
}
