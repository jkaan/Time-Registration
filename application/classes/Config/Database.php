<?php

namespace Application\Config;

/**
 * This class represents the connection to the database.
 *
 * The Singleton pattern is used here
 *
 * @author Joey Kaan
 * @version 1.0.2
 */
class Database {
	private static $instance = null;

	private function __construct() {
		// Can't construct from outside of this class now
	}

	private function __clone() {
		// Can't clone this now
	}
	/**
	 * Checks if an instance is already created, if so return it.
	 * If not, an instance will be created and returned.
	 * @return PDO PDO-object containing the connection to the MySQL Database
	 */
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