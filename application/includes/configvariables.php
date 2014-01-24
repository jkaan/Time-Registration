<?php

/**
 * This file contains all of the variables that are used by the application
 *
 * Time a user stays logged in (1 hour)
 * The date when a semester starts (7th September)
 * The school year (2013-2014)
 *
 * Database information and credentials
 */

if(!defined('AUTH_TIME')) {
	DEFINE('AUTH_TIME', 3600);
}

if(!defined('START_SEMESTER')) {
	DEFINE('START_SEMESTER', '2013-09-07');
}

if(!defined('LEERJAAR')) {
	DEFINE('LEERJAAR', '2013-2014');
}

if(!defined('DBHOST')) {
	DEFINE('DBHOST', 'sql4.freemysqlhosting.net');
}

if(!defined('DBNAME')) {
	DEFINE('DBNAME', 'sql420872');
}

if(!defined('DBUSER')) {
	DEFINE('DBUSER', 'sql420872');
}

if(!defined('DBPASS')) {
	DEFINE('DBPASS', 'zG5*xE3%');
}