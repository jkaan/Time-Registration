<?php

namespace Application\Installation;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;
use Slim\Slim;

/**
 * This class is responsible for initializing the database environment on a server 
 * which hasn't already been used to host this UrenRegistratie application
 *
 * @author Trinco Ingels
 * @version 1.0.0
 */
class Install {

	private $slim;
	private $twigRenderer;
	private $db;

	/**
	 * Initializes this class
	 * Sets the connection to the database, the template renderer and slim instance for routing
	 */
	public function __construct() {
		$this->slim = Slim::getInstance();
		$this->twigRenderer = new TwigRenderer();
		$this->db = Database::getInstance();
	}
	
	/**
	 * Calls all the methods neccesary to create the database on the server
	 */
	public function installDatabase(){ 
		$this->createUserTable();
		$this->createCursusTable();
		$this->createCursusHasUserTable();
		$this->createFeedbackTable();
		$this->createOnderdeelTable();
		$this->createRolTable();
		$this->createUrenTable();
		$this->insertDefaultUser();		
	}

	/**
	 * This method will create the users table which holds all users
	 */
	public function createUserTable(){
		$sql = "CREATE TABLE IF NOT EXISTS `User` (
			`user_Id` int(11) NOT NULL AUTO_INCREMENT,
			`user_Name` varchar(50) DEFAULT NULL,
			`user_Code` decimal(10,0) DEFAULT NULL,
			`user_Email` varchar(50) DEFAULT NULL,
			`user_Pass` varchar(50) DEFAULT NULL,
			`user_Klas` varchar(10) DEFAULT NULL,
			`Rol_rol_Id` int(11) NOT NULL,
			`user_Online` datetime NOT NULL,
			`actief` int(1) NOT NULL,
			PRIMARY KEY (`user_Id`),
			KEY `fk_User_Rol1_idx` (`Rol_rol_Id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
$statement = $this->db->prepare($sql);
$statement->execute();	
}

/**
 * This method will create the cursus table which holds all courses
 */
public function createCursusTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Cursus` (
		`cursus_Id` int(11) NOT NULL AUTO_INCREMENT,
		`cursus_Name` varchar(50) DEFAULT NULL,
		`cursus_Code` varchar(10) DEFAULT NULL,
		`actief` int(1) NOT NULL,
		`User_user_Id` int(11) NOT NULL,
		PRIMARY KEY (`cursus_Id`),
		KEY `fk_Cursus_User1_idx` (`User_user_Id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;";
$statement = $this->db->prepare($sql);
$statement->execute();	
}

/**
 * This method will create the CursusHasUser table which holds all users that are enrolled in an course
 */
public function createCursusHasUserTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Cursus_has_User` (
		`Cursus_Id` int(11) NOT NULL,
		`User_Id` int(11) NOT NULL,
		KEY `User_Id` (`User_Id`),
		KEY `Cursus_Id` (`Cursus_Id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Deze tabel koppelt de studenten(users) aan de cursussen';";
$statement = $this->db->prepare($sql);
$statement->execute();
}

/**
 * This method will create the feedback table which holds all the feedback teachers gave to students
 */
public function createFeedbackTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Feedback` (
		`feedback_Id` int(11) NOT NULL AUTO_INCREMENT,
		`feedback_wknr` int(11) NOT NULL,
		`feedback_Titel` varchar(50) DEFAULT NULL,
		`feedback_Text` text,
		`User_user_Id` int(11) NOT NULL,
		`Docent_Id` int(11) NOT NULL,
		`Cursus_cursus_Id` int(11) NOT NULL,
		`feedback_Date` datetime NOT NULL,
		`feedbackUpdate_Date` date NOT NULL,
		PRIMARY KEY (`feedback_Id`),
		KEY `fk_Feedback_User1_idx` (`User_user_Id`),
		KEY `fk_Feedback_Cursus1_idx` (`Cursus_cursus_Id`),
		KEY `Docent_Id` (`Docent_Id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;";
$statement = $this->db->prepare($sql);
$statement->execute();
}

/**
 * This method will create the Onderdeel table which holds all assignments/exams for every course
 */
public function createOnderdeelTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Onderdeel` (
		`onderdeel_Id` int(11) NOT NULL AUTO_INCREMENT,
		`onderdeel_Name` varchar(50) DEFAULT NULL,
		`onderdeel_Norm` int(11) DEFAULT NULL,
		`Cursus_cursus_Id` int(11) NOT NULL,
		PRIMARY KEY (`onderdeel_Id`),
		KEY `fk_Onderdeel_Cursus1_idx` (`Cursus_cursus_Id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;";
$statement = $this->db->prepare($sql);
$statement->execute();
}

/**
 * This method will create the Rol table which holds all the users with their corresponding role(student, teacher, SLC)
 */
public function createRolTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Rol` (
		`rol_Id` int(11) NOT NULL,
		`rol_Naam` varchar(20) DEFAULT NULL,
		PRIMARY KEY (`rol_Id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
$statement = $this->db->prepare($sql);
$statement->execute();
}	

/**
 * This method will create the Uren table which holds all the Uren that students have reported
 */
public function createUrenTable() {
	$sql = "CREATE TABLE IF NOT EXISTS `Uren` (
		`uren_Id` int(11) NOT NULL AUTO_INCREMENT,
		`uren_Date` date DEFAULT NULL,
		`uren_Studielast` int(11) DEFAULT NULL,
		`User_user_Id` int(11) NOT NULL,
		`Onderdeel_onderdeel_Id` int(11) NOT NULL,
		`uren_Created` datetime NOT NULL,
		PRIMARY KEY (`uren_Id`),
		KEY `fk_Uren_User_idx` (`User_user_Id`),
		KEY `fk_Uren_Onderdeel1_idx` (`Onderdeel_onderdeel_Id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;";
$statement = $this->db->prepare($sql);
$statement->execute();
}

/**
 * This method will create a user in the User table for admin access
 */
public function insertDefaultUser() {
	$sql = "INSERT INTO `User` (`user_Id`, `user_Name`, `user_Code`, `user_Email`, `user_Pass`, `user_Klas`, `Rol_rol_Id`, `user_Online`, `actief`) VALUES
	(1, 'admin', '12', 'admin@hz.nl', 'admin', 'null', 3, NOW(), 1);";
	$statement = $this->db->prepare($sql);
	$statement->execute();
}
}