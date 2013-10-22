<?php

require '../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'templates.path' => '../templates',
	));


$app->get('/', 'startPage');
$app->get('/login', 'loginPage');
$app->get('/student', 'studentPage');
$app->get('/docent', 'docentPage');
$app->get('/slc', 'slcPage');
$app->post('/login', 'loginUser');

function startPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('page' => 'Start Page'));
}

function loginPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('login.php');
}
function studentPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('page' => 'Student Page'));
}

function docentPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('page' => 'Docent Page'));
}

function slcPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('page' => 'SLC Page'));
}

function loginUser() {
	$db = new PDO('mysql:dbname=sql420872;host=sql4.freemysqlhosting.net', 'sql420872', 'zG5*xE3%');
	$statement = $db->prepare('SELECT * FROM User WHERE user_Name = :username AND user_Pass = :password');
	$statement->bindParam('username', $_POST['username']);
	$statement->bindParam('password', $_POST['password']);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	if($results > 0) {
		echo $results['user_Id'];
	}
}

$app->run();