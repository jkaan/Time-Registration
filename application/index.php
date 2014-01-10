<?php

require('../vendor/autoload.php');
require('classes/database.class.php');
require('classes/application.class.php');
require('classes/TwigRenderer.class.php');
require_once('configvariables.php');
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
		$statement = $db->prepare("SELECT feedback_Id, feedback_wknr, feedback_Titel, Cursus_cursus_Id, (SELECT user_Name FROM User WHERE Docent_Id = user_Id) as docent FROM Feedback WHERE User_user_Id = " . $id);
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
		$statement = $db->prepare("SELECT feedback_wknr, feedback_Titel, feedback_Text, Cursus_cursus_Id, (SELECT user_Name FROM User WHERE Docent_Id = user_Id) as docent FROM Feedback WHERE User_user_Id = " . $id . " AND feedback_Id = " . $itemId );
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
		$totaaluren = 0;
		$parts = null;
		$array = null;
		$weeknumberNow = date("W", strtotime(START_SEMESTER));
		if(!empty($_POST)){
			$parts = explode("-", $_POST['week']);
			$weeknr = $parts[0];
			$startandenddate = getStartAndEndDate($weeknr, $parts[1]);
			$db = Database::getInstance();
			$statement = $db->prepare("SELECT 
											uren_Id, 
											SUM(uren_Studielast) as studielast, 
											(SELECT cursus_Name FROM Cursus WHERE cursus_Id IN(SELECT Cursus_cursus_Id FROM Onderdeel WHERE onderdeel_Id = u.Onderdeel_onderdeel_Id)) as cursus, 
											(SELECT cursus_Id FROM Cursus WHERE cursus_Id IN(SELECT Cursus_cursus_Id FROM Onderdeel WHERE onderdeel_Id = u.Onderdeel_onderdeel_Id)) as cursus_Id
										FROM 
											Uren as u 
										WHERE 
											uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."' 
										AND 
											User_user_Id = " . $id . " 
										GROUP BY 
											cursus"  );
			$statement->execute();
			$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
			$array = array();
			
			foreach($urenoverzichtData as $uren )
			{
				$totaaluren += $uren['studielast'];
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$array[] = array('uren_Id' => $uren['uren_Id'], 'studielast' => $studielast_in_uren, 'cursus_Id' => $uren['cursus_Id'], 'cursus' => $uren['cursus']);
			}
		}
		//var_dump(generateWeeknumbersFromDate($weeknumberNow));
		echo $twigRenderer->renderTemplate('urenoverzicht.twig', array('name' => $result['user_Name'], 'id' => $id, 'weeknr' => $weeknr, 'urenoverzichtarray' => $array, 'jaar' => $parts[1] ,'weeknummers' => generateWeeknumbersFromDate($weeknumberNow), 'totaal' => min_naar_uren($totaaluren)));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function studentOverzichtDetail($id, $weeknr, $jaar, $cursusid){
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			$startandenddate = getStartAndEndDate($weeknr, $jaar);	
			$statement = $db->prepare("SELECT
											(SELECT onderdeel_Name FROM Onderdeel WHERE onderdeel_Id = Onderdeel_onderdeel_Id) AS onderdeel,
											(SELECT cursus_Name FROM Cursus WHERE cursus_Id = '".$cursusid."') as cursus,
											Onderdeel_onderdeel_Id AS onderdeel_Id,
											SUM(uren_Studielast) as studielast										
										FROM
											Uren
										WHERE
											uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."' 
										AND 
											User_user_Id = " . $id . "
										AND
											Onderdeel_onderdeel_Id IN (SELECT onderdeel_Id FROM Onderdeel WHERE Cursus_cursus_Id = '".$cursusid."')
										GROUP BY Onderdeel_onderdeel_Id"  
									);
			$statement->execute();
			$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
			$array = array();
			$cursus = '';
			foreach($urenoverzichtData as $uren )
			{
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$cursus = $uren['cursus'];
				$array[] = array('onderdeel_Id' => $uren['onderdeel_Id'], 'onderdeel' => $uren['onderdeel'], 'studielast' => $studielast_in_uren);
			}
		echo $twigRenderer->renderTemplate('urenoverzichtdetail_student.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array,'jaar' => $jaar ,'weeknr' => $weeknr, 'cursus_Id' => $cursusid, 'cursus_Name' => $cursus));
	}else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function studentOverzichtDetailOnderdeel($id, $weeknr, $jaar, $onderdeelid){
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
	$result = getUserDetails($id);
	if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
		$startandenddate = getStartAndEndDate($weeknr, $jaar);	
		$statement = $db->prepare("SELECT
										(SELECT onderdeel_Name FROM Onderdeel WHERE onderdeel_Id = Onderdeel_onderdeel_Id) AS onderdeel,										
										uren_Studielast as studielast,
										uren_Date AS datum
									FROM
										Uren
									WHERE
										uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."' 
									AND 
										User_user_Id = " . $id . "
									AND
										Onderdeel_onderdeel_Id = '".$onderdeelid."'
									GROUP BY uren_Date"  
								);
		$statement->execute();
		$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
		$array = array();
		$onderdeel = '';
		foreach($urenoverzichtData as $uren )
		{
			$studielast_in_uren = min_naar_uren($uren['studielast']);
			$dag_vd_week = date('w', strtotime($uren['datum'])); 
			$dagen = array('Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag');
			$onderdeel = $uren['onderdeel'];			
			$array[] = array('dag' => $dagen[$dag_vd_week], 'datum' => date('d-M-Y', strtotime($uren['datum'])),'onderdeel' => $uren['onderdeel'], 'studielast' => $studielast_in_uren);
		}
		echo $twigRenderer->renderTemplate('urenoverzichtdetailOnderdeel_student.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array, 'onderdeel' => $onderdeel, 'weeknr' => $weeknr));
	}else {
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

function docentOverzicht($id){
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	if((isLogged($id))) {
		$weeknr = 0;
		$cursus_Id = 1;
		$parts = null;
		$array = null;
		$weeknumberNow = date("W", strtotime(START_SEMESTER));
		if(!empty($_POST)){
			$parts = explode("-", $_POST['week']);
			$weeknr = $parts[0];
			$startandenddate = getStartAndEndDate($weeknr, $parts[1]);
			$db = Database::getInstance();
			
			$statement = $db->prepare("SELECT
				User_user_Id as user_Id,
				(SELECT user_Name FROM User WHERE Uren.User_user_Id = user_Id) as user_Name, 
				SUM(uren_Studielast) as studielast										
				FROM
				Uren
				WHERE
				Onderdeel_onderdeel_Id in (SELECT onderdeel_Id FROM Onderdeel WHERE Onderdeel_onderdeel_Id = onderdeel_Id AND Cursus_cursus_Id IN (SELECT cursus_Id FROM Cursus WHERE cursus_Id = Cursus_cursus_Id AND cursus_Id = '".$cursus_Id."'))
				AND
				User_user_Id in (SELECT user_Id FROM User WHERE User_user_Id = user_Id AND Rol_rol_Id = 1)
				AND
				uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."'
				GROUP BY User_user_Id"  
				);
			$statement->execute();
			$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
			$array = array();
			foreach($urenoverzichtData as $uren )
			{
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$array[] = array('user_Id' => $uren['user_Id'], 'user_Name' => $uren['user_Name'], 'studielast' => $studielast_in_uren);
			}

		}
		//var_dump(generateWeeknumbersFromDate($weeknumberNow));
		echo $twigRenderer->renderTemplate('urenoverzicht_docent.twig', array('cursus_Id' => $cursus_Id, 'name' => $result['user_Name'], 'id' => $id, 'weeknr' => $weeknr, 'jaar' => $parts[1], 'urenoverzichtarray' => $array, 'weeknummers' => generateWeeknumbersFromDate($weeknumberNow)));
		
	}	
	else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

<<<<<<< HEAD
function docentCursusBeheer($id) {
	if(isLogged($id)) {
		$db = Database::getInstance();

		$statement = $db->prepare('SELECT *
			FROM Cursus
			WHERE User_user_Id = :userID');
		$statement->bindParam('userID', $id);
		$statement->execute();
		$courses = $statement->fetchAll(PDO::FETCH_ASSOC);

		echo $twigRenderer->renderTemplate('docentcursusbeheer.twig', array('courses' => $courses));
	} else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function docentOverzichtDetail($id, $userid, $weeknr, $cursusid){
=======
function docentOverzichtDetail($id, $userid, $weeknr, $jaar, $cursusid){
>>>>>>> b0441d8b19203ff499f4a8afc2eec1f4456d2e81
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
<<<<<<< HEAD
		$result = getUserDetails($id);
		if((isLogged($id))) {
			$startandenddate = getStartAndEndDate($weeknr, $jaar);	
			$statement = $db->prepare("SELECT
											(SELECT onderdeel_Name FROM Onderdeel WHERE onderdeel_Id = Onderdeel_onderdeel_Id) AS onderdeel,
											(SELECT onderdeel_Norm FROM Onderdeel WHERE onderdeel_Id = Onderdeel_onderdeel_Id) AS onderdeel_Norm,
											(SELECT user_Name FROM User WHERE user_Id = '".$userid."') AS student,
											(SELECT cursus_Name FROM Cursus WHERE cursus_Id = '".$cursusid."') as cursus,
											SUM(uren_Studielast) as studielast
										FROM
											Uren
										WHERE
											Onderdeel_onderdeel_Id in (SELECT onderdeel_Id FROM Onderdeel WHERE Onderdeel_onderdeel_Id = onderdeel_Id AND Cursus_cursus_Id IN (SELECT cursus_Id FROM Cursus WHERE cursus_Id = Cursus_cursus_Id AND cursus_Id = '".$cursusid."'))
										AND
											User_user_Id = '".$userid."'
										AND
											uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."'
										GROUP BY Onderdeel_onderdeel_Id"										
									);
			$statement->execute();
			$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
			$totaalPerOnderdeel = totaalTotDatum($userid, $startandenddate[1], $cursusid);
			$count = 0;
			$student = '';
			$cursus = '';
			foreach($urenoverzichtData as $uren )
			{
				$onderdeel_norm = $uren['onderdeel_Norm'];
				$berekening = (($onderdeel_norm / 100) * $totaalPerOnderdeel[$count]['totaalOnderdeel']);
				
				if($berekening > 100)
				{
					$berekening = $berekening - 100;
					$berekening = "<font color=\"red\">".$berekening."%</font> boven";
				}
				else{
					$berekening = "<font color=\"green\">".$berekening."%</font> onder";
				}
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$student = $uren['student'];
				$cursus = $uren['cursus'];
				$array[] = array(
									'onderdeel' => $uren['onderdeel'], 
									'studielast' => $studielast_in_uren, 
									'onderdeel_Norm' => min_naar_uren($uren['onderdeel_Norm']), 
									'totaalPerOnderdeel' => min_naar_uren($totaalPerOnderdeel[$count]['totaalOnderdeel']),
									'berekening' => $berekening
								);
				$count++;
			}
		echo $twigRenderer->renderTemplate('urenoverzichtdetail_docent.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array, 'student' => $student, 'weeknr' => $weeknr, 'cursus_Name' => $cursus));
		}else{
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
=======
	$startandenddate = getStartAndEndDate($weeknr, 2013);	
	$statement = $db->prepare("SELECT
		(SELECT onderdeel_Name FROM Onderdeel WHERE onderdeel_Id = Onderdeel_onderdeel_Id) AS onderdeel,
		SUM(uren_Studielast) as studielast										
		FROM
		Uren
		WHERE
		Onderdeel_onderdeel_Id in (SELECT onderdeel_Id FROM Onderdeel WHERE Onderdeel_onderdeel_Id = onderdeel_Id AND Cursus_cursus_Id IN (SELECT cursus_Id FROM Cursus WHERE cursus_Id = Cursus_cursus_Id AND cursus_Id = '".$cursusid."'))
		AND
		User_user_Id = '".$userid."'
		AND
		uren_Date between '".$startandenddate[0]."' and '".$startandenddate[1]."'
		GROUP BY Onderdeel_onderdeel_Id"  
		);
	$statement->execute();
	$urenoverzichtData = $statement->fetchAll(PDO::FETCH_ASSOC);
	var_dump($urenoverzichtData);
	$array = array();
	foreach($urenoverzichtData as $uren )
	{
		$studielast_in_uren = min_naar_uren($uren['studielast']);
		$array[] = array('onderdeel' => $uren['onderdeel'], 'studielast' => $studielast_in_uren);
	}
	echo $twigRenderer->renderTemplate('urenoverzichtdetail_docent.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array));
>>>>>>> 911c8d5c6c6b8e4486f64c7d20c1191c490d7617
}

function totaalTotDatum($userid, $lastdate, $cursusid){
	$db = Database::getInstance();
	$statement = $db->prepare("SELECT 
								SUM(uren_Studielast) AS totaalOnderdeel
							FROM 
								Uren 
							WHERE 
								uren_Date between ".START_SEMESTER." and '".$lastdate."' 
							AND 
								Onderdeel_onderdeel_Id IN (SELECT 
															onderdeel_Id 
														FROM 
															Onderdeel 
														WHERE 
															Cursus_cursus_Id = '".$cursusid."' 
														)
							GROUP BY
								Onderdeel_onderdeel_Id
								");
	$statement->execute();
	return $statement->fetchAll(PDO::FETCH_ASSOC);;
}
function docentFeedback($id, $userid, $weeknr, $cursusid){
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
	if((isLogged($id))) {
		if(!empty($_POST)){
			$pieces = explode("- ", $_POST['titel']);
			if($pieces['1'] == 'Update')
			{
				$sql = "UPDATE Feedback SET feedback_Text = :feedback, feedbackUpdate_Date = NOW() WHERE User_user_Id = :user_id AND Docent_Id = :docent_id AND feedback_wknr = :wknr AND Cursus_cursus_Id = :cursus_id";
				$statement = $db->prepare($sql);			
				$statement->bindParam('user_id', $userid);
				$statement->bindParam('docent_id', $id);
				$statement->bindParam('wknr', $weeknr);
				$statement->bindParam('cursus_id', $cursusid);
				$statement->bindParam('feedback', $_POST['feedback']);;
				$statement->execute();	
			}else{
				$sql = "INSERT INTO Feedback (feedback_wknr, feedback_Titel, feedback_Text, User_user_Id, Docent_Id, Cursus_cursus_Id, feedback_Date) VALUES (:wknr, :titel, :feedback, :user_id, :docent_id, :cursus_id, NOW())";
				$statement = $db->prepare($sql);	
				$statement->bindParam('wknr', $weeknr);
				$statement->bindParam('titel', $_POST['titel']);
				$statement->bindParam('feedback', $_POST['feedback']);
				$statement->bindParam('user_id', $userid);
				$statement->bindParam('docent_id', $id);
				$statement->bindParam('cursus_id', $cursusid);
				$statement->execute();			
			}
		}
<<<<<<< HEAD
			$sql = "SELECT feedback_Id, feedback_titel, feedback_Text FROM Feedback WHERE User_user_ID = '".$userid."' AND feedback_wknr = '".$weeknr."'";
			$statement = $db->prepare($sql);
			$statement->execute();
			$feedbackData = $statement->fetch(PDO::FETCH_ASSOC);
			echo $twigRenderer->renderTemplate('addfeedback.twig', array('id' => $id, 'weeknr' => $weeknr, 'userid' => $userid, 'cursusid' => $cursusid, 'feedbackData' => $feedbackData));	
=======
		$sql = "SELECT feedback_Id, feedback_titel, feedback_Text FROM Feedback WHERE User_user_ID = '".$userid."' AND feedback_wknr = '".$weeknr."'";
		$statement = $db->prepare($sql);
		$statement->execute();
		$feedbackData = $statement->fetch(PDO::FETCH_ASSOC);
		var_dump($feedbackData);
		echo $twigRenderer->renderTemplate('addfeedback.twig', array('id' => $id, 'weeknr' => $weeknr, 'userid' => $userid, 'cursusid' => $cursusid, 'feedbackData' => $feedbackData));

>>>>>>> 911c8d5c6c6b8e4486f64c7d20c1191c490d7617
	}else{
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function slcPage($id) {
	$twigRenderer = new TwigRenderer();
	$result = getUserDetails($id);
	$db = Database::getInstance();

	// Gets the courses
	$statement = $db->prepare('SELECT cursus_Name, cursus_Code, user_Name
		FROM Cursus as C, User as U
		WHERE C.User_user_Id = U.user_Id
		AND C.actief = :actief');
	$statement->bindValue('actief', 1);
	$statement->execute();
	$courses = $statement->fetchAll(PDO::FETCH_ASSOC);

	// Gets all the students
	$statement = $db->prepare('SELECT * FROM User WHERE actief = :actief');
	$statement->bindValue('actief', 1);
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
			$sql = "INSERT INTO Uren (Onderdeel_onderdeel_Id, uren_Date, uren_Studielast, User_user_Id, uren_Created) VALUES (:onderdeel, :datum, :studielast, :user_id, NOW())";
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
	$timenow = strtotime(date('Y-m-d G:i:s'));	
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
	$app = \Slim\Slim::getInstance();
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
	$app = \Slim\Slim::getInstance();
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

function getStudentsOfCourse($id, $courseId) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(isLogged($id)) {
		$db = Database::getInstance();

		// First part, this gets the corresponding course
		$sqlCourseAndTeacher = "SELECT cursus_Name, cursus_Code, user_Name
		FROM Cursus as C, User as U
		WHERE C.User_user_Id = U.user_Id
		AND C.cursus_Id = :courseId";

		$statement = $db->prepare($sqlCourseAndTeacher);
		$statement->bindParam('courseId', $courseId);
		$statement->execute();
		$course = $statement->fetchAll(PDO::FETCH_ASSOC);

		// Second part, this gets the students that are enrolled in the corresponding course
		$sqlStudentsOfCourse = "SELECT user_Name, user_Code, user_Email, user_Klas
		FROM Cursus_has_User as CU, Cursus as C, User as U
		WHERE CU.Cursus_Id = C.cursus_Id
		AND CU.User_Id = U.user_Id
		AND C.cursus_Id = :courseId";

		$statement = $db->prepare($sqlStudentsOfCourse);
		$statement->bindParam('courseId', $courseId);
		$statement->execute();
		$students = $statement->fetchAll(PDO::FETCH_ASSOC);

		// Third part, this gets all of the students whichc
		$sqlAllStudents = "SELECT * FROM User as U WHERE NOT EXISTS (SELECT User_Id FROM Cursus_has_User WHERE U.user_Id = User_Id AND Cursus_Id = :courseId )";

		$statement = $db->prepare($sqlAllStudents);
		$statement->bindParam('courseId', $courseId);
		$statement->execute();
		$allStudents = $statement->fetchAll(PDO::FETCH_ASSOC);

		echo $twigRenderer->renderTemplate('studentsincourse.twig', array('course' => $course, 'students' => $students, 'courseId' => $courseId, 'id' => $id, 'allStudents' => $allStudents));
	} else {
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}

function addStudentToCourse($id, $courseId) {
	if(isLogged($id)) {
		$app = \Slim\Slim::getInstance();

		$db = Database::getInstance();

		$sql = "INSERT INTO Cursus_has_User (Cursus_Id, User_Id) VALUES (:courseId, :userId)";

		$statement = $db->prepare($sql);
		$statement->bindParam('courseId', $courseId);
		$statement->bindParam('userId', $_POST['studentToAdd']);

		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
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

function removeStudent($id, $studentId) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {
		$db = Database::getInstance();
		$sql = "UPDATE User SET actief = :actief WHERE user_Id = :studentId";
		$statement = $db->prepare($sql);
		echo $studentId;
		$statement->bindParam('studentId', $studentId);
		$statement->bindValue('actief', 0);

		if($statement->execute()) {
			$app->redirect('/urenregistratie/application/index.php/slc/' . $id);
		} else {
			print_r($statement->errorInfo());
		} 
	} else {
		if(isLogged($id)) {
			$db = Database::getInstance();
			$sql = "SELECT * FROM User WHERE user_Id = :studentId";
			$statement = $db->prepare($sql);
			$statement->bindParam('studentId', $studentId);
			$statement->execute();
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);

			echo $twigRenderer->renderTemplate('removestudent.twig', array('student' => $results[0], 'id' => $id, 'studentId' => $studentId));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}
function updateUserOnlineTime($id) {
	$db = Database::getInstance();
	$date = date('Y-m-d G:i:s');
	$statement = $db->prepare("UPDATE User SET user_Online = '".$date."' WHERE user_Id= " . $id);
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

function logOut($id){
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	$db = Database::getInstance();
	if(isLogged($id)){
		$date = date('Y-m-d G:i:s');
		$statement = $db->prepare("UPDATE User SET user_Online = null WHERE user_Id= " . $id);
		$statement->execute();
		$app->redirect(BASE . '/login');
	}else{
		echo $twigRenderer->renderTemplate('noaccess.twig');
	}
}
$app->run();