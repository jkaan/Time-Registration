<?php

require('../vendor/autoload.php');
require('classes/database.php');

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
	$db = Database::getInstance();
	$statement = $db->prepare("SELECT rol_Naam, user_Id, user_Name FROM User, Rol WHERE user_Name = :username AND user_Pass = :password AND Rol.rol_Id = User.Rol_rol_Id");
	$statement->bindParam('username', $_POST['username']);
	$statement->bindParam('password', $_POST['password']);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	if($results > 0) {
		switch($results['rol_Naam']) {
			case 'student':
			studentPage();
			break;
			case 'docent':
			docentPage();
			break;
			case 'slc':
			slcPage();
		}
	}
}

$app->run();