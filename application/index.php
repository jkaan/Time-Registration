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

function studentProfiel($id){
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		echo $twigRenderer->renderTemplate('profiel.twig', array('name' => $result['user_Name'], 'code' => $result['user_Code'], 'email' => $result['user_email'], 'klas' => $result['user_Klas']));
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}
function loginPage() {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	echo $twigRenderer->renderTemplate('login.twig', array('page' => 'Start Page'));
}
function studentPage($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		echo $twigRenderer->renderTemplate('student.twig', array('id' => $id));
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function docentPage($id) {
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 2)) {
		echo $twigRenderer->renderTemplate('docent.twig', array('id' => $id));
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function slcPage($id) {
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	$db = Database::getInstance();
	$statement = $db->prepare('SELECT * FROM Cursus');
	$statement->execute();
	$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 3)) {
		echo $twigRenderer->renderTemplate('slc.twig', array('id' => $id, 'courses' => $courses));
	} else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function urenPage($id) {
	$twigRenderer = new TwigRenderer();
	if(isLogged($id)){
		echo $twigRenderer->renderTemplate('uren.twig', array('id' => $id));
	}
	else {		
		echo $twigRenderer->renderTemplate('noaccess.twig'); 
	}
}



function getUserDetails($id) {
	$db = Database::getInstance();
	$statement = $db->prepare("SELECT user_Name, user_Code, user_email, user_Klas, Rol_rol_Id FROM User, Rol WHERE user_Id = " . $id);
	$statement->execute();
	return $statement->fetch(PDO::FETCH_ASSOC);
}
/*
Controleert of de gebruiker ingelogd is.
De gebruiker is voor een bepaalde tijd ingelogd (gedefinieerd in de config.php).
*/
function isLogged($id) {
	$logged = false;
	$db = Database::getInstance();
	$sql = "SELECT user_Online FROM User WHERE user_Id = " . $id;
	$statement = $db->prepare($sql);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	$time = strtotime($results['user_Online']) + AUTH_TIME; // Add 1 hour
	if($time > strtotime(date('y-m-d G:i:s'))) {
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
}

function addCourse($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)){
		$db = Database::getInstance();
		$sql = "INSERT INTO Cursus (cursus_Name, cursus_Code, User_user_Id) VALUES (:cursus_name, :cursus_code, :user_id)";
		$statement = $db->prepare($sql);
		$statement->bindParam('cursus_name', $_POST['coursename']);
		$statement->bindParam('cursus_code', $_POST['coursecode']);
		$statement->bindParam('user_id', $id);
		
		if($statement->execute()) {
			echo $twigRenderer->renderTemplate('slc.twig', array('message' => 'Added a new course.', 'id' => $id));
		}
	} else {
		if(isLogged($id)){
			echo $twigRenderer->renderTemplate('addcourse.twig', array('page' => 'Toevoegen van een nieuwe course', 'id' => $id)); 
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig'); 
		}
	}
}

function addStudent($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {	
		$db = Database::getInstance();
		$sql = "INSERT INTO User (user_Name, user_Code, user_Email, user_Pass, user_Klas, Rol_rol_Id) 
		VALUES (:user_name, :user_code, :user_email, :user_pass, :user_klas, 1)"; // 1 is hardcoded because a student always has a role of 1
		$statement = $db->prepare($sql);
		$statement->bindParam('user_name', $_POST['studentname']);
		$statement->bindParam('user_code', $_POST['studentcode']);
		$statement->bindParam('user_email', $_POST['studentemail']);
		$statement->bindParam('user_pass', $_POST['studentpassword']);
		$statement->bindParam('user_klas', $_POST['studentklas']);
		$statement->execute();

		if($statement->execute()) {
			echo $twigRenderer->renderTemplate('slc.twig', array('message' => 'Added a new user.', 'id' => $id));
		}
	} else {
		if(isLogged($id)) {
			echo $twigRenderer->renderTemplate('addstudent.twig', array('id' => $id));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}

function updateUserOnlineTime($id) {
	$db = Database::getInstance();
	$statement = $db->prepare("UPDATE User SET user_Online = NOW() WHERE user_Id= " . $id);
	$statement->execute();
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
			updateUserOnlineTime($results['user_Id']);
			$app = \Slim\Slim::getInstance();
			$app->redirect(BASE . '/student/' . $results['user_Id']);
			break;
			case 'docent':
			updateUserOnlineTime($results['user_Id']);
			$app = \Slim\Slim::getInstance();
			$app->redirect(BASE . '/docent/' . $results['user_Id']);
			break;
			case 'slc':
			updateUserOnlineTime($results['user_Id']);
			$app = \Slim\Slim::getInstance();
			$app->redirect(BASE . '/slc/' . $results['user_Id']);
		}
	}
}

$app->run();