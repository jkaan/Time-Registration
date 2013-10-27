<?php

require('../vendor/autoload.php');
require('classes/database.php');
require('classes/application.php');

$app = new \Slim\Slim(array(
	'templates.path' => '../templates',
	));

$application = new Application();

$routes = $application->getRoutes();


foreach($routes as $route) {
	$app->$route['method']($route['URL'], $route['action']);
}

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

function urenPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('uren.php', array('page' => 'Uren Page'));
}

function slcPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('slc.php');
}

function addStudielast() {
	$db = Database::getInstance();

	$sql = "INSERT INTO Uren (onderdeel_Id, uren_Date, uren_Studielast, User_user_Id) VALUES (0, :datum, :studielast, 2)";
	$statement = $db->prepare($sql);
	$statement->bindParam('datum', $_POST['date']);
	$statement->bindParam('studielast', $_POST['studielast']);
	$statement->execute();
	$db = null;
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
			$_POST['userId'] = $results['user_Id'];
			$_POST['userNaam'] = $results['user_Name'];
			$_POST['rolNaam'] = $results['rol_Naam'];
			studentPage();
			break;
			case 'docent':
			$_POST['userId'] = $results['user_Id'];
			$_POST['userNaam'] = $results['user_Name'];
			$_POST['rolNaam'] = $results['rol_Naam'];
			docentPage();
			break;
			case 'slc':
			$_POST['userId'] = $results['user_Id'];
			$_POST['userNaam'] = $results['user_Name'];
			$_POST['rolNaam'] = $results['rol_Naam'];
			slcPage();
		}
	}
}

function logoutUser() {
	unset($_POST['userId']);
	unset($_POST['userNaam']);
	unset($_POST['rolNaam']);
}

$app->run();