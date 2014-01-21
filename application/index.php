<?php

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