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
	$error = false;
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		if(!empty($_POST)){
			if($_POST['wachtwoord1'] == $_POST['wachtwoord2'])
			{
				$newpass = $_POST['wachtwoord2'];
				$db = Database::getInstance();
				$sql = "UPDATE User SET user_Pass = :pass WHERE user_Id =" .$id;
				$statement = $db->prepare($sql);
				$statement->bindParam('pass', $newpass);
				$statement->execute();
			}else{
				$error = true;
			}
		}
		echo $twigRenderer->renderTemplate('profiel.twig', array('name' => $result['user_Name'], 'code' => $result['user_Code'], 'email' => $result['user_email'], 'klas' => $result['user_Klas'], 'id' => $id, 'online' => $error));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function studentFeedback($id) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		$db = Database::getInstance();
		$statement = $db->prepare("SELECT feedback_Id, feedback_wknr, feedback_Titel, Cursus_cursus_Id FROM Feedback WHERE User_user_Id = " . $id);
		$statement->execute();
		$feedbackData = $statement->fetchAll(PDO::FETCH_ASSOC);
		echo $twigRenderer->renderTemplate('feedback.twig', array('name' => $result['user_Name'], 'data' => $feedbackData));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function studentFeedbackItem($id, $itemId) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		$db = Database::getInstance();
		$statement = $db->prepare("SELECT feedback_wknr, feedback_Titel, feedback_Text, Cursus_cursus_Id FROM Feedback WHERE User_user_Id = " . $id . " AND feedback_Id = " . $itemId );
		$statement->execute();
		$feedbackItemData = $statement->fetchAll(PDO::FETCH_ASSOC);
		echo $twigRenderer->renderTemplate('feedbackItem.twig', array('name' => $result['user_Name'], 'data' => $feedbackItemData));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

// verkrijg de eerste - en laatste dag van de gegeven week. 
function getStartAndEndDate($week, $year)
{

    $time = strtotime("1 January $year", time());
    $day = date('w', $time);
    $time += ((7*$week)+1-$day)*24*3600;
    $return[0] = date('Y-n-j', $time);
    $time += 6*24*3600;
    $return[1] = date('Y-n-j', $time);
    return $return;
}

function min_naar_uren($minuten){ 
	return sprintf("%d:%02d", floor($minuten / 60), (abs($minuten) % 60));
}
function studentOverzicht($id){
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
	$weeknr = 0;
	$array = null;
	$weeknumberNow = date("W", strtotime(START_SEMESTER));
		if(!empty($_POST)){
			$parts = explode("-", $_POST['week']);
			$weeknr = $parts[0];
			$startandenddate = getStartAndEndDate($weeknr, $parts[1]);
			$db = Database::getInstance();
			$statement = $db->prepare("SELECT uren_Id, SUM(uren_Studielast) as studielast, (SELECT cursus_Name FROM Cursus WHERE cursus_Id IN(SELECT Cursus_cursus_Id FROM Onderdeel WHERE onderdeel_Id = u.Onderdeel_onderdeel_Id)) as cursus FROM Uren as u WHERE uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."' AND User_user_Id = " . $id . " GROUP BY cursus"  );
			$statement->execute();
			$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
			$array = array();
			foreach($urenoverzichtData as $uren )
			{
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$array[] = array('uren_Id' => $uren['uren_Id'], 'studielast' => $studielast_in_uren, 'cursus' => $uren['cursus']);
			}
		}
		//var_dump(generateWeeknumbersFromDate($weeknumberNow));
		echo $twigRenderer->renderTemplate('urenoverzicht.twig', array('name' => $result['user_Name'], 'id' => $id, 'weeknr' => $weeknr, 'urenoverzichtarray' => $array, 'weeknummers' => generateWeeknumbersFromDate($weeknumberNow)));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function generateWeeknumbersFromDate($weeknr)
{
	$maxWeekNr = 52;
	$array = array();
	$newweeknr = $weeknr;
	$leerjaar = explode("-", LEERJAAR);
	$l = 0;
	for($i=1; $i <= $maxWeekNr; $i++)
	{
		if($newweeknr < $maxWeekNr){
			$array[] = array('week' => $newweeknr, 'jaar' => $leerjaar[$l]);
		}else
		{
			$newweeknr = 0;
			$l = 1;
		}
		$newweeknr = $newweeknr +1;
	}
	return $array;
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
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 3)) {
		echo $twigRenderer->renderTemplate('slc.twig', array('id' => $id));
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
		$statement->execute();
	}
	else{
		if(isLogged($id)){
			echo $twigRenderer->renderTemplate('addcourse.twig', array('page' => 'Toevoegen van een nieuwe course')); 
			//$app->render('addcourse.twig', array('page' => 'Toevoegen van een nieuwe course'));
		}
		else
			echo $twigRenderer->renderTemplate('noaccess.twig'); 
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