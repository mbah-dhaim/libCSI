# libcsi  
simple library for connecting database and create query builder model  
version **dev**  
* only support mysql/mariadb syntax

## installatation

It's recommended that you use [Composer](https://getcomposer.org/) to install  
```bash  
$ composer require libcsi/libcsi "dev-master"  
```  

**OR**

* create composer.json containing  
`{  "minimum-stability": "dev",  "require": {  "php": ">=5.3.0",  "libcsi/libcsi": "dev-master"  }  }`
* run `composer install`  

## usage example

`<?php`  
`use CSI\Data\DataAdapter;`  
`require_once __DIR__ . '/vendor/autoload.php';`  
`$config = array (`  
`'DB' => array (`  
`'dbdriver' => 'mysql',`  
`'dbserver' => 'localhost',`  
`'dbname' => 'dbname',`  
`'dbuser' => 'dbuser',`  
`'dbpass' => 'dbpass',`   
`'fieldcasing' => 1)`  
`);`  
`$db = new \CSI\Data\DataAdapter ( $config ["DB"] );`  
`try{`  
`$db->connect();`  
`}catch(Exception $e)`
`die($e->getMessage());`  
`}`  

### example model
`<?php`  
`final class TableTest extends \CSI\Data\Model {`  
`//change to what table in database`  
`protected $table="table_test";`  
`//change to table primary key name`    
`protected $primaryKey="id";`  
`}`  

#### fetching data using model
`$table = new TableTest();`  
`$table->find("a primary key value");`  
`$fieldvalue = $table->afiedname;`

