<?php

require('../vendor/autoload.php');
require('classes/database.class.php');
require('classes/application.class.php');
require('classes/TwigRenderer.class.php');
require_once('configvariables.php');

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
	$db = Database::getInstance();
	// Gets the courses
	$statement = $db->prepare('SELECT * FROM Cursus WHERE actief = :actief');
	$statement->bindValue('actief', 1);
	$statement->execute();
	$courses = $statement->fetchAll(PDO::FETCH_ASSOC);

	// Gets all the students
	$statement = $db->prepare('SELECT * FROM User');
	$statement->execute();
	$students = $statement->fetchAll(PDO::FETCH_ASSOC);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 3)) {
		echo $twigRenderer->renderTemplate('slc.twig', array('id' => $id, 'courses' => $courses, 'students' => $students));
	} else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function urenPage($id) {
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
	if(isLogged($id)){
		if(!empty($_POST)){
			$sql = "INSERT INTO Uren (Onderdeel_onderdeel_Id, uren_Date, uren_Studielast, User_user_Id) VALUES (:onderdeel, :datum, :studielast, :user_id)";
			$statement = $db->prepare($sql);	
			$statement->bindParam('datum', $_POST['date']);
			$statement->bindParam('onderdeel', $_POST['onderdeel']);
			$statement->bindParam('studielast', $_POST['studielast']);
			$statement->bindParam('user_id', $id);
			$statement->execute();
			
		}			
		$statement = $db->prepare("SELECT cursus_Id, cursus_Name FROM Cursus WHERE actief <> 0");
		$statement->execute();
		$coursearray = $statement->fetchALL(PDO::FETCH_ASSOC);
		//var_dump($coursearray);
		echo $twigRenderer->renderTemplate('uren.twig', array('id' => $id, 'courses' => $coursearray));
		
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
	$timenow = date('Y-m-d G:i:s');	
	if($time > $timenow) {
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
		$sql = "INSERT INTO Cursus (cursus_Name, cursus_Code, actief, User_user_Id) VALUES (:cursus_name, :cursus_code, :actief, :user_id)";
		$statement = $db->prepare($sql);
		$statement->bindParam('cursus_name', $_POST['coursename']);
		$statement->bindParam('cursus_code', $_POST['coursecode']);
		$statement->bindValue('actief', 1);
		$statement->bindParam('user_id', $id);
		
		if($statement->execute()) {
			$app->flash('message', 'test');
			session_start();
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		}
	} else {
		if(isLogged($id)){
			echo $twigRenderer->renderTemplate('addcourse.twig', array('id' => $id)); 
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig'); 
		}
	}
}

function editCourse($id, $courseId) {
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {
		$db = Database::getInstance();
		$sql = "UPDATE Cursus SET cursus_Name = :courseName, cursus_Code = :courseCode WHERE cursus_Id = :cursusId";
		$statement = $db->prepare($sql);
		$statement->bindParam('courseName', $_POST['courseName']);
		$statement->bindParam('courseCode', $_POST['courseCode']);
		$statement->bindParam('cursusId', $courseId);
		
		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		}
	} else {
		if(isLogged($id)) {
			$db = Database::getInstance();
			$sql = "SELECT * FROM Cursus WHERE cursus_Id = :cursusID";
			$statement = $db->prepare($sql);
			$statement->bindParam('cursusID', $courseId);
			$statement->execute();
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);

			echo $twigRenderer->renderTemplate('editcourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}

function removeCourse($id, $courseId) {
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {
		$db = Database::getInstance();
		$sql = "UPDATE Cursus SET actief = :actief WHERE cursus_Id = :cursusId";
		$statement = $db->prepare($sql);
		$statement->bindParam('cursusId', $courseId);
		$statement->bindValue('actief', 0);

		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		} else {
			print_r($statement->errorInfo());
		}
	} else {
		if(isLogged($id)) {
			$db = Database::getInstance();
			$sql = "SELECT * FROM Cursus WHERE cursus_Id = :cursusID";
			$statement = $db->prepare($sql);
			$statement->bindParam('cursusID', $courseId);
			$statement->execute();
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);

			echo $twigRenderer->renderTemplate('removecourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
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

		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		}
	} else {
		if(isLogged($id)) {
			echo $twigRenderer->renderTemplate('addstudent.twig', array('id' => $id));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}

function editStudent($id, $studentId) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {
		$db = Database::getInstance();
		$sql = "UPDATE User SET user_Name = :userName, user_Code = :userCode, user_Email = :userEmail, user_Pass = :userPass, user_Klas = :userKlas WHERE user_Id = :userID";
		$statement = $db->prepare($sql);
		$statement->bindParam('userName', $_POST['studentname']);
		$statement->bindParam('userCode', $_POST['studentcode']);
		$statement->bindParam('userEmail', $_POST['studentemail']);
		$statement->bindParam('userPass', $_POST['studentpassword']);
		$statement->bindParam('userKlas', $_POST['studentklas']);
		$statement->bindParam('userID', $studentId);

		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		}
	} else {
		if(isLogged($id)) {
			$db = Database::getInstance();
			$sql = "SELECT * FROM User WHERE user_Id = :userId";
			$statement = $db->prepare($sql);
			$statement->bindParam('userId', $studentId);
			$statement->execute();
			$student = $statement->fetchAll(PDO::FETCH_ASSOC);
			echo $twigRenderer->renderTemplate('editstudent.twig', array('studentId' => $studentId, 'id' => $id, 'student' => $student[0]));
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