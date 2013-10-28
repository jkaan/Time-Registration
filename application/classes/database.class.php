<?php

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
			$instance = new PDO('mysql:dbname=sql420872;host=sql4.freemysqlhosting.net', 'sql420872', 'zG5*xE3%');
		}
		return $instance;
	}


}