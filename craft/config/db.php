<?php

/**
 * Database Configuration
 *
 * All of your system's database configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/db.php
 */

$url=parse_url(getenv("CLEARDB_DATABASE_URL"));

return array(
	'*' => array(
		'tablePrefix' => 'craft'
	),
	'public' => array(
		'server' => $url["host"],
		'user' => $url["user"],
		'password' => $url["pass"],
		'database' => substr($url["path"],1),
	),
	'live' => array(
		'server' => $url["host"],
		'user' => $url["user"],
		'password' => $url["pass"],
		'database' => substr($url["path"],1),
	),
	'dev' => array(
		'server' => localhost,
		'user' => 'root',
		'password' => 'root',
		'database' => oaklanddb,
	),
);
