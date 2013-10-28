<?php

require('../vendor/autoload.php');
require('classes/database.class.php');
require('classes/application.class.php');
require('classes/TwigRenderer.class.php');
require_once('config.php');

$app = new \Slim\Slim();

$application = new Application();

$routes = $application->getRoutes();

foreach($routes as $route) {
	$app->$route['method']($route['URL'], $route['action']);
}

function startPage() {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('index.twig', array('page' => 'Start Page'));
}

function loginPage() {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('login.twig', array('page' => 'Start Page'));
}
function studentPage($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('student.twig', array('id' => $id));
}

function docentPage() {
	$app = \Slim\Slim::getInstance();
	// $app->render('index.php', array('page' => 'Docent Page'));
}

function urenPage($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('uren.twig', array('id' => $id));
}

function slcPage() {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('slc.twig');
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
	$statement = $db->prepare("SELECT rol_Naam, user_Id, user_Name FROM User, Rol WHERE user_Name = :username AND user_Pass = :password AND Rol.rol_Id = User.Rol_rol_Id");
	$statement->bindParam('username', $_POST['username']);
	$statement->bindParam('password', $_POST['password']);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	if($results > 0) {
		switch($results['rol_Naam']) {
			case 'student':
			$app = \Slim\Slim::getInstance();
			$app->redirect(BASE . '/student/' . $results['user_Id']);
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