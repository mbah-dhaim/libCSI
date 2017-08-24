<?php
use CSI\Data\DataAdapter;
require_once __DIR__ . '/vendor/autoload.php';
$config = array (
		'DB' => array (
				'dbdriver' => 'mysql',
				'dbserver' => 'localhost',
				'dbname' => 'dbname',
				'dbuser' => 'dbuser',
				'dbpass' => 'dbpass',
				'fieldcasing' => 1
		)
);
$DB = new DataAdapter ( $config ["DB"] );
try {
	$DB->connect ();
	$test = new \example\Model\TableTest ();
	$test->find ( "anId" );
} catch ( Exception $e ) {
	die ( $e->getMessage () );
}