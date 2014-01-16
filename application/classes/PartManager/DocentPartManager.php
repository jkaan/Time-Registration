<?php

namespace Application\PartManager;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;
use Slim\Slim;

class DocentPartManager {

	private $slim;
	private $twigRenderer;
	private $db;

	public function __construct() {
		$this->slim = Slim::getInstance();
		$this->twigRenderer = new TwigRenderer();
		$this->db = Database::getInstance();
	}

	public function docentPage($id) {
		$result = getUserDetails($id);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 2)) {
			echo $this->twigRenderer->renderTemplate('docent.twig', array('id' => $id));
		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function docentOverzicht($id){
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

				
				$statement = $this->db->prepare("SELECT
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
				$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$array = array();
				foreach($urenoverzichtData as $uren )
				{
					$studielast_in_uren = min_naar_uren($uren['studielast']);
					$array[] = array('user_Id' => $uren['user_Id'], 'user_Name' => $uren['user_Name'], 'studielast' => $studielast_in_uren);
				}

			}
			echo $this->twigRenderer->renderTemplate('urenoverzicht_docent.twig', array('cursus_Id' => $cursus_Id, 'name' => $result['user_Name'], 'id' => $id, 'weeknr' => $weeknr, 'jaar' => $parts[1], 'urenoverzichtarray' => $array, 'weeknummers' => generateWeeknumbersFromDate($weeknumberNow)));
		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function docentCursusBeheer($id) {
		if(isLogged($id)) {


			$statement = $this->db->prepare('SELECT *
				FROM Cursus
				WHERE User_user_Id = :userID');
			$statement->bindParam('userID', $id);
			$statement->execute();
			$courses = $statement->fetchAll(\PDO::FETCH_ASSOC);

			echo $this->twigRenderer->renderTemplate('docentcursusbeheer.twig', array('courses' => $courses, 'id' => $id));
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function cursusOnderdelen($id, $cursusId) {

		if(isLogged($id)) {


			// First part, gets the corresponding course
			$statement = $this->db->prepare('SELECT *
				FROM Cursus
				WHERE cursus_Id = :cursusId');
			$statement->bindParam('cursusId', $cursusId);
			$statement->execute();
			$cursus = $statement->fetchAll(\PDO::FETCH_ASSOC);

			// Second part, gets all assignments for the corresponding course
			$statement = $this->db->prepare('SELECT *
				FROM Onderdeel
				WHERE Cursus_cursus_Id = :cursusId');
			$statement->bindParam('cursusId', $cursusId);
			$statement->execute();
			$onderdelen = $statement->fetchAll(\PDO::FETCH_ASSOC);

			echo $this->twigRenderer->renderTemplate('onderdelenincursus.twig', array('cursus' => $cursus, 'onderdelen' => $onderdelen, 'id' => $id, 'cursusId' => $cursusId));
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function addOnderdeelToCursus($id, $cursusId) {

		if(isLogged($id)) {


			$statement = $this->db->prepare('INSERT INTO Onderdeel (onderdeel_Name, onderdeel_Norm, Cursus_cursus_Id) VALUES (:onderdeelNaam, :onderdeelNorm, :cursusId)');
			$statement->bindParam('onderdeelNaam', $_POST['onderdeelNaam']);
			$statement->bindParam('onderdeelNorm', $_POST['onderdeelNorm']);
			$statement->bindParam('cursusId', $cursusId);

			if($statement->execute()) {
				$this->slim->redirect('/urenregistratie/application/index.php/docent/' . $id . '/cursus/' . $cursusId . '/onderdelen');
			}
		}
	}

	public function editOnderdeelFromCursus($id, $cursusId, $onderdeelId) {
		if(isLogged($id)) {
			if(empty($_POST)) {



				$statement = $this->db->prepare('SELECT * FROM Onderdeel WHERE onderdeel_Id = :onderdeelId');
				$statement->bindParam('onderdeelId', $onderdeelId);
				$statement->execute();
				$onderdeel = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('editonderdeel.twig', array('id' => $id, 'cursusId' => $cursusId, 'onderdeelId' => $onderdeelId, 'onderdeel' => $onderdeel));
			} else {


				$statement = $this->db->prepare('UPDATE Onderdeel SET onderdeel_Name = :onderdeelNaam, onderdeel_Norm = :onderdeelNorm WHERE onderdeel_Id = :onderdeelId');
				$statement->bindParam('onderdeelNaam', $_POST['onderdeelNaam']);
				$statement->bindParam('onderdeelNorm', $_POST['onderdeelNorm']);
				$statement->bindParam('onderdeelId', $onderdeelId);

				if($statement->execute()) {

					$this->slim->redirect('/urenregistratie/application/index.php/docent/' . $id . '/cursus/' . $cursusId . '/onderdelen');
				}
			}
		} else {

			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function removeOnderdeelFromCursus($id, $cursusId, $onderdeelId) {
		if(isLogged($id)) {
			if(empty($_POST)) {



				$statement = $this->db->prepare('SELECT * FROM Onderdeel WHERE onderdeel_Id = :onderdeelId');
				$statement->bindParam('onderdeelId', $onderdeelId);
				$statement->execute();
				$onderdeel = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('removeonderdeel.twig', array('id' => $id, 'cursusId' => $cursusId, 'onderdeelId' => $onderdeelId, 'onderdeel' => $onderdeel));
			} else {


				$statement = $this->db->prepare('DELETE FROM Onderdeel WHERE onderdeel_Id = :onderdeelId');
				$statement->bindParam('onderdeelId', $onderdeelId);

				if($statement->execute()) {

					$this->slim->redirect('/urenregistratie/application/index.php/docent/' . $id . '/cursus/' . $cursusId . '/onderdelen');
				}
			}
		} else {

			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function docentOverzichtDetail($id, $userid, $weeknr, $jaar, $cursusid){
		$result = getUserDetails($id);
		if((isLogged($id))) {
			$startandenddate = getStartAndEndDate($weeknr, $jaar);	
			$statement = $this->db->prepare("SELECT
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
			$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
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
					$onderdeel_norm = $uren['onderdeel_Norm'];
					$berekening = ($totaalPerOnderdeel[$count]['totaalOnderdeel'] / $onderdeel_norm) * 100;
					if($berekening > 100)
					{
						$berekening = $berekening - 100;
						$berekening = "<font color=\"red\">".$berekening."%</font> boven";
					}
					else{
						$berekening = 100 - $berekening;
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
			echo $this->twigRenderer->renderTemplate('urenoverzichtdetail_docent.twig', array('id' => $id, 'onderdeeloverzichtarray' => $array, 'student' => $student, 'weeknr' => $weeknr, 'cursus_Name' => $cursus));
		}else{
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function totaalTotDatum($userid, $lastdate, $cursusid){
		$statement = $this->db->prepare("SELECT 
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
		return $statement->fetchAll(\PDO::FETCH_ASSOC);;
	}

	public function docentFeedback($id, $userid, $weeknr, $cursusid){
		if((isLogged($id))) {
			if(!empty($_POST)){
				$pieces = explode("- ", $_POST['titel']);
				if($pieces['1'] == 'Update')
				{
					$sql = "UPDATE Feedback SET feedback_Text = :feedback, feedbackUpdate_Date = NOW() WHERE User_user_Id = :user_id AND Docent_Id = :docent_id AND feedback_wknr = :wknr AND Cursus_cursus_Id = :cursus_id";
					$statement = $this->db->prepare($sql);			
					$statement->bindParam('user_id', $userid);
					$statement->bindParam('docent_id', $id);
					$statement->bindParam('wknr', $weeknr);
					$statement->bindParam('cursus_id', $cursusid);
					$statement->bindParam('feedback', $_POST['feedback']);;
					$statement->execute();	
				}else{
					$sql = "INSERT INTO Feedback (feedback_wknr, feedback_Titel, feedback_Text, User_user_Id, Docent_Id, Cursus_cursus_Id, feedback_Date) VALUES (:wknr, :titel, :feedback, :user_id, :docent_id, :cursus_id, NOW())";
					$statement = $this->db->prepare($sql);	
					$statement->bindParam('wknr', $weeknr);
					$statement->bindParam('titel', $_POST['titel']);
					$statement->bindParam('feedback', $_POST['feedback']);
					$statement->bindParam('user_id', $userid);
					$statement->bindParam('docent_id', $id);
					$statement->bindParam('cursus_id', $cursusid);
					$statement->execute();			
				}
			}
			$sql = "SELECT feedback_Id, feedback_titel, feedback_Text FROM Feedback WHERE User_user_ID = '".$userid."' AND feedback_wknr = '".$weeknr."'";
			$statement = $this->db->prepare($sql);
			$statement->execute();
			$feedbackData = $statement->fetch(\PDO::FETCH_ASSOC);
			echo $this->twigRenderer->renderTemplate('addfeedback.twig', array('id' => $id, 'weeknr' => $weeknr, 'userid' => $userid, 'cursusid' => $cursusid, 'feedbackData' => $feedbackData));	
		}else{
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function gebruikersOverzicht() {
		
	}

}