<?php

require('../vendor/autoload.php');
require('classes/database.php');
require('classes/application.php');
include('functions.php');

error_reporting(-1);

$app = new \Slim\Slim(array(
	'templates.path' => '../templates',
	));

$application = new Application();

$routes = $application->getRoutes();


foreach($routes as $route) {
	$app->$route['method']($route['URL'], $route['action']);
}

$app->run();