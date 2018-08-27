Doctrine ActiveRecord
=====================

[![Build Status](https://travis-ci.org/symlex/doctrine-active-record.png?branch=master)](https://travis-ci.org/symlex/doctrine-active-record)
[![Latest Stable Version](https://poser.pugx.org/symlex/doctrine-active-record/v/stable.svg)](https://packagist.org/packages/symlex/doctrine-active-record)
[![Total Downloads](https://poser.pugx.org/lastzero/doctrine-active-record/downloads.svg)](https://packagist.org/packages/symlex/doctrine-active-record)
[![License](https://poser.pugx.org/symlex/doctrine-active-record/license.svg)](https://packagist.org/packages/symlex/doctrine-active-record)

As a lightweight alternative to Doctrine ORM, this library provides Business Model and Database Access Object (DAO) classes 
that encapsulate **Doctrine DBAL** to provide high-performance, object-oriented CRUD (create, read, update, delete) 
functionality for relational databases. It is a lot faster and less complex than Datamapper ORM implementations. See [TRADEOFFS.md](TRADEOFFS.md).

Basic example
-------------

```php
use Doctrine\ActiveRecord\Dao\Factory as DaoFactory;
use Doctrine\ActiveRecord\Model\Factory;

$daoFactory = new DaoFactory($db); // $db is a Doctrine\DBAL\Connection

$modelFactory = new Factory($daoFactory);
$modelFactory->setFactoryNamespace('App\Model');
$modelFactory->setFactoryPostfix('Model');

$user = $modelFactory->create('User'); // Returns instance of App\Model\UserModel

$user->find(123); // Throws exception, if not found

if ($user->email == '') {
    $user->update(array('email' => 'bender@ilovebender.com')); // Update email
}

$group = $user->createModel('Group'); // Returns instance of App\Model\GroupModel
```

Usage in REST controller context
--------------------------------

Doctrine ActiveRecord is perfectly suited for building high-performance REST services.

This example shows how to work with the EntityModel in a REST controller context. Note, how easy it is to avoid deeply 
nested structures. User model and form factory (provided by the [InputValidation](https://github.com/symlex/input-validation) 
package) are injected as dependencies.

```php
namespace App\Controller\Rest;

use Symfony\Component\HttpFoundation\Request;
use App\Exception\FormInvalidException;
use App\Form\FormFactory;
use App\Model\User;

class UsersController
{
    protected $user;
    protected $formFactory;

    public function __construct(User $user, FormFactory $formFactory)
    {
        $this->user = $user;
        $this->formFactory = $formFactory;
    }
    
    public function cgetAction(Request $request)
    {
        $options = array(
            'count' => $request->query->get('count', 50),
            'offset' => $request->query->get('offset', 0)
        );
        
        return $this->user->search(array(), $options);
    }

    public function getAction($id)
    {
        return $this->user->find($id)->getValues();
    }

    public function deleteAction($id)
    {
        return $this->user->find($id)->delete();
    }

    public function putAction($id, Request $request)
    {
        $this->user->find($id);
        
        $form = $this->formFactory->create('User\Edit');
        $form->setDefinedWritableValues($request->request->all())->validate();

        if($form->hasErrors()) {
            throw new FormInvalidException($form->getFirstError());
        } 
        
        $this->user->update($form->getValues());

        return $this->user->getValues();
    }

    public function postAction(Request $request)
    {
        $form = $this->formFactory->create('User\Create');
        $form->setDefinedWritableValues($request->request->all())->validate();

        if($form->hasErrors()) {
            throw new FormInvalidException($form->getFirstError());
        }
        
        $this->user->save($form->getValues());

        return $this->user->getValues();
    }
}
```

See also: [InputValidation for PHP – Easy & secure whitelist validation for input data of any origin](https://github.com/symlex/input-validation)

Workflow
--------

This diagram illustrates how Controller, Model and DAO interact with each other:

![Architecture](https://www.lucidchart.com/publicSegments/view/5461d17e-f5a8-4166-9e43-47200a00dd77/image.png)

Data Access Objects
-------------------
DAOs directly deal with **database tables** and **raw SQL**, if needed. `Doctrine\ActiveRecord\Dao\Dao` is suited to implement custom methods using raw SQL. All DAOs expose the following public methods by default:
- `createDao(string $name)`: Returns a new DAO instance
- `beginTransaction()`: Start a database transaction
- `commit()`: Commit a database transaction
- `rollBack()`: Roll back a database transaction

In addition, `Doctrine\ActiveRecord\Dao\EntityDao` offers many powerful methods to easily deal with database table rows:
- `setData(array $data)`: Set raw data (changes can not be detected, e.g. when calling update())
- `setValues(array $data)`: Set multiple values
- `setDefinedValues(array $data)`: Set values that exist in the table schema only (slower than setValues())
- `getValues()`: Returns all values as array
- `find($id)`: Find a row by primary key
- `reload()`: Reload row values from database
- `getValues()`: Returns all values as associative array
- `exists($id)`: Returns true, if a row with the given primary key exists
- `save()`: Insert a new row
- `update()`: Updates changed values in the database
- `delete()`: Delete entity from database
- `getId()`: Returns the ID of the currently loaded record (throws exception, if empty)
- `hasId()`: Returns true, if the DAO instance has an ID assigned (primary key)
- `setId($id)`: Set primary key
- `findAll(array $cond = array(), $wrapResult = true)`: Returns all instances that match $cond (use search() or searchAll(), if you want to limit or sort the result set)
- `search(array $params)`: Returns a `SearchResult` object (see below for supported parameters)
- `wrapAll(array $rows)`: Create and return a new DAO for each array element
- `updateRelationTable(string $relationTable, string $primaryKeyName, string $foreignKeyName, array $existing, array $updated)`: Helper function to update n-to-m relationship tables
- `hasTimestampEnabled()`: Returns true, if this DAO automatically adds timestamps when creating and updating rows
- `findList(string $colName, string $order = '', string $where = '', string $indexName = '')`: Returns a key/value array (list) of all matching rows
- `getTableName()`: Returns the name of the underlying database table
- `getPrimaryKeyName()`: Returns the name of the primary key column (throws an exception, if primary key is an array)

Search Parameters
-----------------
`search()` accepts the following optional parameters to limit, filter and sort search results:
- `table`: Table name
- `table_alias`: Alias name for "table" (table reference for join and join_left)
- `cond`: Search conditions as array (key/value or just values for raw SQL)
- `count`: Maximum number of results (integer)
- `offset`: Result offset (integer)
- `join`: List of joined tables incl join condition e.g. `array(array('u', 'phonenumbers', 'p', 'u.id = p.user_id'))`, see Doctrine DBAL manual
- `left_join`: See join
- `columns`: List of columns (array)
- `order`: Sort order (if not false)
- `group`: Group by (if not false)
- `wrap`: If false, raw arrays are returned instead of DAO instances
- `ids_only`: Return primary key values only
- `sql_filter`: Raw SQL filter (WHERE)
- `id_filter`: If not empty, limit result to this list of primary key IDs

Entity Configuration
--------------------
DAO entities are configured using protected class properties:

```php
protected $_tableName = ''; // Database table name
protected $_primaryKey = 'id'; // Name or array of primary key(s)
protected $_fieldMap = array(); // 'db_column' => 'object_property'
protected $_hiddenFields = array(); // Fields that should be hidden for getValues(), e.g. 'password'
protected $_formatMap = array(); // 'db_column' => Format::TYPE
protected $_valueMap = array(); // 'object_property' => 'db_column'
protected $_timestampEnabled = false; // Automatically update timestamps?
protected $_timestampCreatedCol = 'created';
protected $_timestampUpdatedCol = 'updated';
```

Possible values for $_formatMap are defined as constants in `Doctrine\ActiveRecord\Dao\Format`:

```php
const NONE = '';
const INT = 'int';
const FLOAT = 'float';
const STRING = 'string';
const ALPHANUMERIC = 'alphanumeric';
const SERIALIZED = 'serialized';
const JSON = 'json';
const CSV = 'csv';
const BOOL = 'bool';
const TIME = 'H:i:s';
const TIMEU = 'H:i:s.u'; // Support for microseconds (up to six digits)
const TIMETZ = 'H:i:sO'; // Support for timezone (e.g. "+0230")
const TIMEUTZ = 'H:i:s.uO'; // Support for microseconds & timezone
const DATE = 'Y-m-d';
const DATETIME = 'Y-m-d H:i:s';
const DATETIMEU = 'Y-m-d H:i:s.u'; // Support for microseconds (up to six digits)
const DATETIMETZ = 'Y-m-d H:i:sO'; // Support for timezone (e.g. "+0230")
const DATETIMEUTZ = 'Y-m-d H:i:s.uO'; // Support for microseconds & timezone
const TIMESTAMP = 'U';
```

Example:

```php    
<?php

namespace App\Dao;

use Doctrine\ActiveRecord\Dao\EntityDao;

class UserDao extends EntityDao
{
    protected $_tableName = 'users';
    protected $_primaryKey = 'user_id';
    protected $_timestampEnabled = true;
}
```

Business Models
---------------

**Business Models** are logically located between **Controllers** - which render views and validate user input - and **Data Access Objects** (DAOs), that are low-level interfaces to a storage backend or Web service.

Public interfaces of models are high-level and should reflect all use cases within their domain. There are a number of standard use-cases that are pre-implemented in the base class `Doctrine\ActiveRecord\Model\EntityModel`:
- `createModel(string $name = '', Dao $dao = null)`: Create a new model instance
- `find($id)`: Find a record by primary key
- `reload()`: Reload values from database
- `findAll(array $cond = array(), $wrapResult = true)`: Find multiple records; if `$wrapResult` is false, plain DAOs are returned instead of model instances
- `search(array $cond, array $options = array())`: Returns a `SearchResult` object ($options can contain count, offset, sort order etc, see search() in the DAO documentation above)
- `searchAll(array $cond = array(), $order = false)`: Simple version of search(), similar to findAll()
- `searchOne(array $cond = array())`: Search a single record; throws an exception if 0 or more than one record are found
- `searchIds(array $cond, array $options = array())`: Returns an array of matching primary keys for the given search condition
- `getModelName()`: Returns the model name without prefix and postfix
- `getId()`: Returns the ID of the currently loaded record (throws exception, if empty)
- `hasId()`: Returns true, if the model instance has an ID assigned (primary key)
- `getValues()`: Returns all model properties as associative array
- `getEntityTitle()`: Returns the common name of this entity
- `isDeletable()`: Returns true, if the model instance can be deleted with delete()
- `isUpdatable()`: Returns true, if the model instance can be updated with update($values)
- `isCreatable()`: Returns true, if new entities can be created in the database with create($values)
- `batchEdit(array $ids, array $properties)`: Update data for multiple records
- `getTableName()`: Returns the name of the associated main database table
- `hasTimestampEnabled()`: Returns true, if timestamps are enabled for the associated DAO
- `delete()`: Permanently delete the entity record from the database
- `save(array $values)`: Create a new record using the values provided
- `update(array $values)`: Update model instance database record; before assigning multiple values to a model instance, data should be validated using a form class

**How much validation should be implemented within a model?** Wherever invalid data can lead to security issues or major inconsistencies, some core validation rules must be implemented in the model layer. Model exception messages usually don’t require translation (in multilingual applications), since invalid values should be recognized beforehand by a form class. If you expect certain exceptions, you should catch and handle them in your controllers.

Models are associated with their respective Dao using a protected class property:

```
protected $_daoName = ''; // DAO class name without namespace or postfix
```

Example:

```php
<?php

namespace App\Model;

use Doctrine\ActiveRecord\Model\EntityModel;

class User extends EntityModel
{
    protected $_daoName = 'User';

    public function delete() 
    {
        $dao = $this->getEntityDao();
        $dao->is_deleted = 1;
        $dao->update();
    }

    public function undelete() 
    {
        $dao = $this->getEntityDao();
        $dao->is_deleted = 0;
        $dao->update();
    }

    public function search(array $cond, array $options = array()) 
    {
        $cond['is_deleted'] = 0;
        return parent::search($cond, $options);
    }

    public function getValues()
    {
        $result = parent::getValues();
        unset($result['password']);
        return $result;
    }
}
```

Search Result
-------------
When calling `search()` on a `EntityDao` or `EntityModel`, you'll get a `SearchResult` instance as return value.
It implements `ArrayAccess`, `Serializable`, `IteratorAggregate` and `Countable` and can be used either as array
or object with the following methods:

- `getAsArray()`: Returns search result as array
- `getSortOrder()`: Returns sort order as string
- `getSearchCount()`: Returns search count (limit) as integer
- `getSearchOffset()`:  Returns search offset as integer
- `getResultCount()`: Returns the number of actual query results (<= limit)
- `getTotalCount()`: Returns total result count (in the database)
- `getAllResults()`: Returns all results as array of `EntityDao` or `EntityModel` instances
- `getAllResultsAsArray()`: Returns all results as nested array (e.g. to serialize it as JSON)
- `getFirstResult()`: Returns first result `EntityDao` or `EntityModel` instance or throws an exception

Unit Tests
----------

This library comes with a `docker-compose.yml` file for MySQL and database fixtures to run unit tests (MySQL will bind to 127.0.0.1:3308):

```
localhost# docker-compose up -d
localhost# docker-compose exec mysql sh
docker# cd /share/src/Tests/_fixtures
docker# mysql -u root --password=doctrine doctrine-active-record < schema.sql
docker# exit
localhost# bin/phpunit 
PHPUnit 7.3.2 by Sebastian Bergmann and contributors.

................................................................. 65 / 91 ( 71%)
..........................                                        91 / 91 (100%)

Time: 251 ms, Memory: 8.00MB

OK (91 tests, 249 assertions)
localhost# docker-compose down

```

Composer
--------

If you are using composer, simply add "symlex/doctrine-active-record" to your composer.json file and run `composer update`:

```
"require": {
    "symlex/doctrine-active-record": "*"
}
```
    
*Note: This library is part of [Symlex](https://github.com/symlex/symlex) (a framework stack for agile Web development 
based on Symfony) and not an official Doctrine project.*

