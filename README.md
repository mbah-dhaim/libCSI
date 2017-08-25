# libcsi
  
Simple library for connecting database and create query builder model  
version **v1.0.2**  
* only support **mysql/mariadb** syntax

## installatation
  
It's recommended that you use [Composer](https://getcomposer.org/) to install  
```bash  
$ composer require libcsi/libcsi "^1.0"  
```  

**OR**

* create composer.json containing  
```javascript  
{
	"minimum-stability": "dev",
	"require": {
		"php": ">=5.3.0",
		"libcsi/libcsi": "^1.0"
	}
}
```
* run `composer install`  
  
## usage example
  
```php
<?php
  
require_once 'vendor/autoload.php';

// put it somewhere you like  
$config = array (
		'DB' => array (
				'dbdriver' => 'mysql',
				'dbserver' => 'localhost',
				'dbname' => 'dbname',
				'dbuser' => 'dbuser',
				'dbpass' => 'dbpass'
		)
);
$db = new \CSI\Data\DataAdapter ( $config ["DB"] );
try{
	// connect to database
	$db->connect();
}catch(\Exception $e){
	die($e->getMessage());
}
```    
  
### example model
  
```php
<?php

final class TableTest extends \CSI\Data\Model {
	// name of the table in database
	protected $table="table_test";
	// name of primary key of this table
	protected $primaryKey="id";
}
```
  
#### fetching data using model

```php
$table = new TableTest();  
$table->find("a primary key value");  
$fieldvalue = $table->afiedname;
```
  
That's it
