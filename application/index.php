<?php

require '../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'templates.path' => 'templates',
	));

$app->get('/hello/:name', 'showName');

function showName($name) {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('name' => $name));
}

$app->run();