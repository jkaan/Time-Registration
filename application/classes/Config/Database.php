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
				try {
					$instance = new \PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS, array(
						\PDO::ATTR_PERSISTENT => true
						));
					if(!$instance){
						$statement = $instance->prepare("CREATE DATABASE IF NOT EXISTS ". DBNAME);
						$statement->execute();
					}

				} catch (PDOException $e) {
					print "Error: " . $e->getMessage();
					die();
				}
			}
			return $instance;
		}
	}
}