Changelog
=========

Release 4
---------

* 4.5.0 Added ISO8601 format & updated dependencies

* 4.4.2 Tested with latest Symfony; Updated docs, added contributing guide & codecov config 

* 4.4.1 Catching Throwable instead of Exception to cover all errors in Dao::transactional() 

* 4.4.0 SearchResult implements \Countable; removed PHP 7.0 support; added CHANGELOG.md & TRADEOFFS.md; improved documentation

* 4.3.0 Fixed Dao->exists() method and recreated file fixtures for tests

* 4.2.1 Code clean-up and fixes for findAll()

* 4.2.0 Added support for CSV column type

* 4.1.1 Updated REST controller example in documentation

* 4.1.0 Moved to Symlex repository

* 4.0.0 Improved API, documentation and PHP7 type hints

Release 3
---------

* 3.0.0 Added PHP 7 type hints

Release 2
---------

* 2.0.2 Added hidden fields feature

* 2.0.1 Removed direct phpunit dependency

* 2.0.0 Refactored for PHP 7, PHPUnit 6 and PSR-4 compatibility

Release 1
---------

* 1.2.0 Automatic escaping for fields during insert/update

* 1.1.8 Improved EntityDao insert() (passed timestamps are not changed anymore)

* 1.1.7 Improved counting of result rows in DAO search()

* 1.1.6 Added support for SQL_CALC_FOUND_ROWS (MySQL)

* 1.1.5 Removed support for PHP 5.4

* 1.1.4 Added support for FixedDateTime

* 1.1.3 Improved inline docs

* 1.1.2 Fixed wrap option for model search()

* 1.1.1 Improved SearchResult

* 1.1.0 Added SearchResult class

* 1.0.5 Added support for isset()

* 1.0.4 Added config option to FactoryAbstract

* 1.0.3 Refactored getClassName()

* 1.0.2 Added FactoryAbstract for better code reuse

* 1.0.1 Update for supporting Symfony 3.0

* 1.0.0 Initial stable release