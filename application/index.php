<?php

require('../vendor/autoload.php');
require('classes/database.php');
require('classes/application.php');
require('config.php');

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
function studentPage($id) {
	$app = \Slim\Slim::getInstance();
	global $base;
	$app->render('index.php', array('page' => 'Student Page', 'id' => $id, 'base' => $base));
}

function docentPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('index.php', array('page' => 'Docent Page'));
}

function urenPage($id) {
	$app = \Slim\Slim::getInstance();
	isLogged($id);
	$app->render('uren.php', array('page' => 'Uren Page'));
}

function slcPage() {
	$app = \Slim\Slim::getInstance();
	$app->render('slc.php');
}

function isLogged($id){
	$logged = false;
	$db = Database::getInstance();
	$sql = "SELECT user_Online FROM User WHERE user_Id = ".$id;
	$statement = $db->prepare($sql);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	$time = strtotime($results['user_Online']) + 3600; // Add 1 hour
	if($time > strtotime(date('y-m-d G:i:s')))
	{
		$logged = true;
	}
	return $logged;
}

function addStudielast($id) {
	$db = Database::getInstance();

	$sql = "INSERT INTO Uren (onderdeel_Id, uren_Date, uren_Studielast, User_user_Id) VALUES (0, :datum, :studielast, :user_id)";
	$statement = $db->prepare($sql);
	$statement->bindParam('datum', $_POST['date']);
	$statement->bindParam('studielast', $_POST['studielast']);
	$statement->bindParam('user_id', $id);
	$statement->execute();
	$db = null;
}

function loginUser() {
	$db = Database::getInstance();
	global $base;
	$statement = $db->prepare("SELECT rol_Naam, user_Id, user_Name FROM User, Rol WHERE user_Name = :username AND user_Pass = :password AND Rol.rol_Id = User.Rol_rol_Id");
	$statement->bindParam('username', $_POST['username']);
	$statement->bindParam('password', $_POST['password']);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	if($results > 0) {
		switch($results['rol_Naam']) {
			case 'student':
			$app = \Slim\Slim::getInstance();
			$statement = $db->prepare("UPDATE User SET user_Online = NOW() WHERE user_Id=".$results['user_Id']);
			$statement->execute();
			$app->redirect($base.'/student/'.$results['user_Id']);
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