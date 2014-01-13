<?php

require('../vendor/autoload.php');
require_once('configvariables.php');
require_once('config.php');

use Application\Application;
use Application\Config\Database;

$app = new \Slim\Slim(array('debug' => true));

$application = new Application();

$routes = $application->getRoutes();


foreach($routes as $route) {
	$class = new $route['class'];
	$app->$route['method']($route['URL'], array($class, $route['action']));
}

$db = Database::getInstance();

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
function getUserDetails($id) {
	
	$statement = $GLOBALS['db']->prepare("SELECT user_Name, user_Code, user_email, user_Klas, Rol_rol_Id FROM User, Rol WHERE user_Id = " . $id);
	$statement->execute();
	return $statement->fetch(PDO::FETCH_ASSOC);
}
/*
Controleert of de gebruiker ingelogd is.
De gebruiker is voor een bepaalde tijd ingelogd (gedefinieerd in de config.php).
*/
function isLogged($id) {
	$logged = false;
	
	$sql = "SELECT user_Online FROM User WHERE user_Id = " . $id;
	$statement = $GLOBALS['db']->prepare($sql);
	$statement->execute();
	$results = $statement->fetch(PDO::FETCH_ASSOC);
	$time = strtotime($results['user_Online']) + AUTH_TIME; // Add 1 hour
	$timenow = strtotime(date('Y-m-d G:i:s'));	
	if($time > $timenow) {
		$logged = true;
	}
	return $logged;
}

function updateUserOnlineTime($id) {
	
	$date = date('Y-m-d G:i:s');
	$statement = $GLOBALS['db']->prepare("UPDATE User SET user_Online = '".$date."' WHERE user_Id= " . $id);
	$statement->execute();
}

$app->run();