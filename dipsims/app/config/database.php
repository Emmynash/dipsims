<?php

require_once __DIR__ . '/../libraries/Initializer.php';
$subdomain = \Initializer::subdomain();
$domain_info = \Initializer::fetch_subdomain_data($subdomain);

return array(

	

	'default' => 'mysql',

	/*
	|--------------------------------------------------------------------------
	*/

	'connections' => array(
		'mysql' => array(
			'driver'    => 'mysql',
			'host'      => $domain_info["db_host"],
			'database'  => $domain_info["db_name"],
			'username'  => $domain_info["db_user"],
			'password'  => $domain_info["db_pass"],
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => array(

		'cluster' => false,

		'default' => array(
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		),

	),

	);
