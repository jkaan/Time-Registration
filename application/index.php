<?php

require('../vendor/autoload.php');
require_once('configvariables.php');
require_once('config.php');
require_once('helpfunctions.php');

use Application\Application;
use Application\Config\Database;
use Slim\Slim;

$app = new Slim(array('debug' => true));

$application = new Application();

$routes = $application->getRoutes();

foreach($routes as $route) {
	$class = new $route['class'];
	$app->$route['method']($route['URL'], array($class, $route['action']));
}

$app->run();