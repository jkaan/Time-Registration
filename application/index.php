<?php

/**
 * This file is the main file of the application
 *
 * It requires all of the classes by requiring the autoload.php file
 *
 * Initializes Slim, gets the routes and adds them to the Slim instance
 */

require('../vendor/autoload.php');

use Application\Application;
use Application\Config\Database;
use Slim\Slim;

$app = new Slim(array('debug' => true));

error_reporting(-1);

$application = new Application();

$routes = $application->getRoutes();

foreach($routes as $route) {
	$class = new $route['class'];
	$app->$route['method']($route['URL'], array($class, $route['action']));
}

$app->run();