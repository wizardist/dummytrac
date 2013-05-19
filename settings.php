<?php

// FIXME: remove before push
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

if ( !defined( 'DUMMYTRACKER' ) ) {
	exit;
}

$settings = array(
	// URLs & paths
	'host' => 'test.project',
	'path' => '',
	'resources' => 'resources/',

	// DB
	'dbhost' => 'localhost',
	'dbname' => 'dummytracker',
	'dbuser' => 'root',
	'dbpassword' => '',

	'sitename' => 'Dummy Tracker',
	'timezone' => 'Europe/Minsk',
);
