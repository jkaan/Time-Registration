<?php

namespace Application\Config;

class Database {
	private static $instance = null;

	private function __construct() {
		// Can't construct from outside of this class now
	}

	private function __clone() {
		// Can't clone this now
	}
	public static function getInstance() {
		if(!isset($instance)) {
			if(defined('DBHOST')) {
				$instance = new \PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
			}
		}
		return $instance;
	}


}