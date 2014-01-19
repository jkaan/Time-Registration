<?php

namespace Application\PartManager;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;
use Slim\Slim;

class StudentPartManager {

	private $slim;
	private $twigRenderer;
	private $db;

	public function __construct() {
		$this->slim = Slim::getInstance();
		$this->twigRenderer = new TwigRenderer();
		$this->db = Database::getInstance();
	}

	public function studentPage($id) {
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			echo $this->twigRenderer->renderTemplate('student.twig', array('id' => $id));
		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}
	
	public function installDatabase() {
		echo 'hoi';
	}
	
	public function addStudielast($id) {
		

		$sql = "INSERT INTO Uren (onderdeel_Id, uren_Date, uren_Studielast, User_user_Id) VALUES (0, :datum, :studielast, :user_id)";
		$statement = $this->db->prepare($sql);	
		$statement->bindParam('datum', $_POST['date']);
		$statement->bindParam('studielast', $_POST['studielast']);
		$statement->bindParam('user_id', $id);
		$statement->execute();
	}

	public function urenPage($id) {
		
		if(isLogged($id)){
			if(!empty($_POST)){
				$sql = "INSERT INTO Uren (Onderdeel_onderdeel_Id, uren_Date, uren_Studielast, User_user_Id, uren_Created) VALUES (:onderdeel, :datum, :studielast, :user_id, NOW())";
				$statement = $this->db->prepare($sql);	
				$statement->bindParam('datum', $_POST['date']);
				$statement->bindParam('onderdeel', $_POST['onderdeel']);
				$statement->bindParam('studielast', $_POST['studielast']);
				$statement->bindParam('user_id', $id);
				$statement->execute();

			}			
			$statement = $this->db->prepare("SELECT cursus_Id, cursus_Name FROM Cursus WHERE actief <> 0");
			$statement->execute();
			$coursearray = $statement->fetchALL(\PDO::FETCH_ASSOC);
			echo $this->twigRenderer->renderTemplate('uren.twig', array('id' => $id, 'courses' => $coursearray));

		}
		else {		
			echo $this->twigRenderer->renderTemplate('noaccess.twig'); 
		}
	}

	public function studentProfiel($id){
		$result = getUserDetails($id);
		$error = false;
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			if(!empty($_POST)){
				if($_POST['wachtwoord1'] == $_POST['wachtwoord2'])
				{
					$newpass = $_POST['wachtwoord2'];
					
					$sql = "UPDATE User SET user_Pass = :pass WHERE user_Id =" .$id;
					$statement = $this->db->prepare($sql);
					$statement->bindParam('pass', $newpass);
					$statement->execute();
				}else{
					$error = true;
				}
			}
			echo $this->twigRenderer->renderTemplate('profiel.twig', array('name' => $result['user_Name'], 'code' => $result['user_Code'], 'email' => $result['user_email'], 'klas' => $result['user_Klas'], 'id' => $id, 'online' => $error));

		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function studentFeedback($id) {
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			
			$statement = $this->db->prepare("SELECT feedback_Id, feedback_wknr, feedback_Titel, Cursus_cursus_Id, (SELECT user_Name FROM User WHERE Docent_Id = user_Id) as docent FROM Feedback WHERE User_user_Id = " . $id);
			$statement->execute();
			$feedbackData = $statement->fetchAll(\PDO::FETCH_ASSOC);
			echo $this->twigRenderer->renderTemplate('feedback.twig', array('name' => $result['user_Name'], 'data' => $feedbackData));
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function studentFeedbackItem($id, $itemId) {
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			
			$statement = $this->db->prepare("SELECT feedback_wknr, feedback_Titel, feedback_Text, Cursus_cursus_Id, (SELECT user_Name FROM User WHERE Docent_Id = user_Id) as docent FROM Feedback WHERE User_user_Id = " . $id . " AND feedback_Id = " . $itemId );
			$statement->execute();
			$feedbackItemData = $statement->fetchAll(\PDO::FETCH_ASSOC);
			echo $this->twigRenderer->renderTemplate('feedbackItem.twig', array('name' => $result['user_Name'], 'data' => $feedbackItemData));
		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function studentOverzicht($id){
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
				
				$statement = $this->db->prepare("SELECT 
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
				$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$array = array();

				foreach($urenoverzichtData as $uren )
				{
					$totaaluren += $uren['studielast'];
					$studielast_in_uren = min_naar_uren($uren['studielast']);
					$array[] = array('uren_Id' => $uren['uren_Id'], 'studielast' => $studielast_in_uren, 'cursus_Id' => $uren['cursus_Id'], 'cursus' => $uren['cursus']);
				}
			}
			echo $this->twigRenderer->renderTemplate('urenoverzicht.twig', array('name' => $result['user_Name'], 'id' => $id, 'weeknr' => $weeknr, 'urenoverzichtarray' => $array, 'jaar' => $parts[1] ,'weeknummers' => generateWeeknumbersFromDate($weeknumberNow), 'totaal' => min_naar_uren($totaaluren)));

		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function studentOverzichtDetail($id, $weeknr, $jaar, $cursusid){
		
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			$startandenddate = getStartAndEndDate($weeknr, $jaar);	
			$statement = $this->db->prepare("SELECT
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
			$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
			$array = array();
			$cursus = '';
			foreach($urenoverzichtData as $uren )
			{
				$studielast_in_uren = min_naar_uren($uren['studielast']);
				$cursus = $uren['cursus'];
				$array[] = array('onderdeel_Id' => $uren['onderdeel_Id'], 'onderdeel' => $uren['onderdeel'], 'studielast' => $studielast_in_uren);
			}
			echo $this->twigRenderer->renderTemplate('urenoverzichtdetail_student.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array,'jaar' => $jaar ,'weeknr' => $weeknr, 'cursus_Id' => $cursusid, 'cursus_Name' => $cursus));
		}else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function studentOverzichtDetailOnderdeel($id, $weeknr, $jaar, $onderdeelid){
		
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 1)) {
			$startandenddate = getStartAndEndDate($weeknr, $jaar);	
			$statement = $this->db->prepare("SELECT
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
			$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
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
			echo $this->twigRenderer->renderTemplate('urenoverzichtdetailOnderdeel_student.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array, 'onderdeel' => $onderdeel, 'weeknr' => $weeknr));
		}else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

}