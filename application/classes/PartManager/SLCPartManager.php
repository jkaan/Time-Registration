<?php

namespace Application\PartManager;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;
use Slim\Slim;

/**
 * This class handles all the requests and responses for the SLC part of the application
 *
 * @author  Joey Kaan & Trinco Ingels
 * @version  1.0.0
 */
class SLCPartManager {

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
	 * Responsible for rendering the start page when you login with a SLC account
	 * @param  Integer $id Id of the SLC
	 * @return Template of the SLC
	 */
	public function slcPage($id) {
		$result = getUserDetails($id);
		// Gets the courses
		$statement = $this->db->prepare('SELECT cursus_Id, cursus_Name, cursus_Code, user_Name
			FROM Cursus as C, User as U
			WHERE C.User_user_Id = U.user_Id
			AND C.actief = :actief');
		$statement->bindValue('actief', 1);
		$statement->execute();
		$courses = $statement->fetchAll(\PDO::FETCH_ASSOC);

		// Gets all the students
		$statement = $this->db->prepare('SELECT * FROM User');
		$statement->execute();
		$students = $statement->fetchAll(\PDO::FETCH_ASSOC);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 3)) {
			echo $this->twigRenderer->renderTemplate('slc.twig', array('id' => $id, 'courses' => $courses, 'students' => $students));
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	/**
	 * Renders the template that enables a SLC to add a course
	 * Also responsible for actually adding the course to the database
	 * @param Integer $id Id of the SLC
	 * @return Template that enables a SLC to add a course
	 */
	public function addCourse($id) {
		if(!empty($_POST)){
			$sql = "INSERT INTO Cursus (cursus_Name, cursus_Code, actief, User_user_Id) VALUES (:cursus_name, :cursus_code, :actief, :user_id)";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('cursus_name', $_POST['coursename']);
			$statement->bindParam('cursus_code', $_POST['coursecode']);
			$statement->bindValue('actief', 1);
			$statement->bindParam('user_id', $_POST['docent']);
			
			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			}
		} else {
			if(isLogged($id)){
				$sql = "SELECT user_Name, user_Id
				FROM User
				WHERE Rol_rol_Id = :rolId";
				$statement = $this->db->prepare($sql);
				$statement->bindValue('rolId', 2);
				$statement->execute();
				$teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
				echo $this->twigRenderer->renderTemplate('addcourse.twig', array('id' => $id, 'teachers' => $teachers)); 
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig'); 
			}
		}
	}

	/**
	 * Renders the template that enables a SLC to edit a course
	 * Also responsible for actually updating the course in the database
	 * @param Integer $id Id of the SLC
	 * @param Integer $courseId Id of the course
	 * @return Template that enables a SLC to edit a course
	 */
	public function editCourse($id, $courseId) {

		if(!empty($_POST)) {
			$sql = "UPDATE Cursus SET cursus_Name = :courseName, cursus_Code = :courseCode WHERE cursus_Id = :cursusId";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('courseName', $_POST['courseName']);
			$statement->bindParam('courseCode', $_POST['courseCode']);
			$statement->bindParam('cursusId', $courseId);
			
			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			}
		} else {
			if(isLogged($id)) {

				$sql = "SELECT * FROM Cursus WHERE cursus_Id = :cursusID";
				$statement = $this->db->prepare($sql);
				$statement->bindParam('cursusID', $courseId);
				$statement->execute();
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('editcourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	/**
	 * Renders the template that enables a SLC to remove a course
	 * Also responsible for actually removing the course in the database
	 * @param Integer $id Id of the SLC
	 * @param Integer $courseId Id of the course
	 * @return Template that enables a SLC to remove a course
	 */
	public function removeCourse($id, $courseId) {

		if(!empty($_POST)) {
			$sql = "UPDATE Cursus SET actief = :actief WHERE cursus_Id = :cursusId";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('cursusId', $courseId);
			$statement->bindValue('actief', 0);

			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			} else {
				print_r($statement->errorInfo());
			}
		} else {
			if(isLogged($id)) {

				$sql = "SELECT * FROM Cursus WHERE cursus_Id = :cursusID";
				$statement = $this->db->prepare($sql);
				$statement->bindParam('cursusID', $courseId);
				$statement->execute();
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('removecourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	/**
	 * Renders the template that shows all of the students enrolled in a specific course
	 * @param  Integer $id       Id of the SLC
	 * @param  Integer $courseId Id of the course
	 * @return Template that shows all of the students enrolled in the specific course
	 */
	public function getStudentsOfCourse($id, $courseId) {
		if(isLogged($id)) {
			// First part, this gets the corresponding course
			$sqlCourseAndTeacher = "SELECT cursus_Name, cursus_Code, user_Name
			FROM Cursus as C, User as U
			WHERE C.User_user_Id = U.user_Id
			AND C.cursus_Id = :courseId";

			$statement = $this->db->prepare($sqlCourseAndTeacher);
			$statement->bindParam('courseId', $courseId);
			$statement->execute();
			$course = $statement->fetchAll(\PDO::FETCH_ASSOC);

			// Second part, this gets the students that are enrolled in the corresponding course
			$sqlStudentsOfCourse = "SELECT user_Name, user_Code, user_Email, user_Klas
			FROM Cursus_has_User as CU, Cursus as C, User as U
			WHERE CU.Cursus_Id = C.cursus_Id
			AND CU.User_Id = U.user_Id
			AND C.cursus_Id = :courseId";

			$statement = $this->db->prepare($sqlStudentsOfCourse);
			$statement->bindParam('courseId', $courseId);
			$statement->execute();
			$students = $statement->fetchAll(\PDO::FETCH_ASSOC);

			// Third part, this gets all of the students which exist but are not already enrolled in the corresponding course
			$sqlAllStudents = "SELECT * FROM User as U WHERE NOT EXISTS (SELECT User_Id FROM Cursus_has_User WHERE U.user_Id = User_Id AND Cursus_Id = :courseId )";

			$statement = $this->db->prepare($sqlAllStudents);
			$statement->bindParam('courseId', $courseId);
			$statement->execute();
			$allStudents = $statement->fetchAll(\PDO::FETCH_ASSOC);

			echo $this->twigRenderer->renderTemplate('studentsincourse.twig', array('course' => $course, 'students' => $students, 'courseId' => $courseId, 'id' => $id, 'allStudents' => $allStudents));
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	/**
	 * Responsible for adding a student to a specific course
	 * @param Integer $id       Id of the SLC
	 * @param Integer $courseId Id of the course
	 */
	public function addStudentToCourse($id, $courseId) {
		if(isLogged($id)) {
			$sql = "INSERT INTO Cursus_has_User (Cursus_Id, User_Id) VALUES (:courseId, :userId)";

			$statement = $this->db->prepare($sql);
			$statement->bindParam('courseId', $courseId);
			$statement->bindParam('userId', $_POST['studentToAdd']);

			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			}
		}
	}

	/**
	 * Renders the template that enables an SLC to add a student to the system
	 * Also responsible for actually adding the 
	 * @param Integer $id Id of the SLC
	 * @return Template that allows an SLC to add a student to the system
	 */
	public function addStudent($id) {
		if(!empty($_POST)) {	
			$sql = "INSERT INTO User (user_Name, user_Code, user_Email, user_Pass, user_Klas, Rol_rol_Id, actief) 
			VALUES (:user_name, :user_code, :user_email, :user_pass, :user_klas, :Rol_rol_Id, 1)";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('user_name', $_POST['studentname']);
			$statement->bindParam('user_code', $_POST['studentcode']);
			$statement->bindParam('user_email', $_POST['studentemail']);
			$statement->bindParam('user_pass', $_POST['studentpassword']);
			$statement->bindParam('user_klas', $_POST['studentklas']);
			$statement->bindParam('Rol_rol_Id', $_POST['rol']);

			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			}
		} else {
			if(isLogged($id)) {
				$sql = 'SELECT * FROM Rol';
				$statement = $this->db->prepare($sql);
				$statement->execute();
				$rollen = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('addstudent.twig', array('id' => $id, 'rollen' => $rollen));
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	/**
	 * Renders the template that allows a SLC to edit a student in the system
	 * Also responsible for actually updating the student in the database
	 * @param  Integer $id        Id of the SLC
	 * @param  Integer $studentId Id of the student
	 * @return Template that allows a SLC to edit a student
	 */
	public function editStudent($id, $studentId) {
		if(!empty($_POST)) {
			if(!empty($_POST['actief'])){
				$actief = 1;
			}else{
				$actief = 0;
			}
			$sql = "UPDATE User SET user_Name = :userName, user_Code = :userCode, user_Email = :userEmail, user_Pass = :userPass, user_Klas = :userKlas, Rol_rol_Id = :rolId, actief = :userActief WHERE user_Id = :userID";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('userName', $_POST['studentname']);
			$statement->bindParam('userCode', $_POST['studentcode']);
			$statement->bindParam('userEmail', $_POST['studentemail']);
			$statement->bindParam('userPass', $_POST['studentpassword']);
			$statement->bindParam('userKlas', $_POST['studentklas']);
			$statement->bindParam('rolId', $_POST['rol']);
			$statement->bindParam('userActief', $actief);
			$statement->bindParam('userID', $studentId);
			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			}
		} else {
			if(isLogged($id)) {
				$sql = "SELECT * FROM User WHERE user_Id = :userId";
				$statement = $this->db->prepare($sql);
				$statement->bindParam('userId', $studentId);
				$statement->execute();
				$student = $statement->fetchAll(\PDO::FETCH_ASSOC);

				$sql = "SELECT * FROM Rol";
				$statement = $this->db->prepare($sql);
				$statement->execute();
				$rollen = $statement->fetchAll(\PDO::FETCH_ASSOC);
				echo $this->twigRenderer->renderTemplate('editstudent.twig', array('studentId' => $studentId, 'rollen' => $rollen,'id' => $id, 'student' => $student[0]));
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	/**
	 * Renders the template that allows a SLC to remove a student in the system
	 * Also responsible for actually removing the student in the database
	 * This method doesn't actually remove the student from the database, just sets it as inactive
	 * @param  Integer $id        Id of the SLC
	 * @param  Integer $studentId Id of the student
	 * @return Template that allows a SLC to remove a student
	 */
	public function removeStudent($id, $studentId) {
		if(!empty($_POST)) {
			$sql = "UPDATE User SET actief = :actief WHERE user_Id = :studentId";
			$statement = $this->db->prepare($sql);
			$statement->bindParam('studentId', $studentId);
			$statement->bindValue('actief', 0);

			if($statement->execute()) {
				$this->slim->redirect(BASE . '/slc/' . $id);
			} else {
				print_r($statement->errorInfo());
			}
		} else {
			if(isLogged($id)) {
				$sql = "SELECT * FROM User WHERE user_Id = :studentId";
				$statement = $this->db->prepare($sql);
				$statement->bindParam('studentId', $studentId);
				$statement->execute();
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $this->twigRenderer->renderTemplate('removestudent.twig', array('student' => $results[0], 'id' => $id, 'studentId' => $studentId));
			} else {
				echo $this->twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	/**
	 * Renders the total view where a SLC can see all of the student's hours.
	 * @param  Integer $id Id of the SLC
	 * @return Template that contains all student's hours
	 */
	public function slcOverzicht($id) {
		if((isLogged($id))) {
			$totaaluren = 0;
			$array = null;
			if(!empty($_POST)){

				$statement = $this->db->prepare("SELECT 
					uren_Id, 
					SUM(uren_Studielast) as studielast, 
					(SELECT user_Name FROM User WHERE u.User_user_Id = user_Id) AS user_Name,
					(SELECT cursus_Name FROM Cursus WHERE cursus_Id IN(SELECT Cursus_cursus_Id FROM Onderdeel WHERE onderdeel_Id = u.Onderdeel_onderdeel_Id)) as cursus, 
					(SELECT cursus_Id FROM Cursus WHERE cursus_Id IN(SELECT Cursus_cursus_Id FROM Onderdeel WHERE onderdeel_Id = u.Onderdeel_onderdeel_Id)) as cursus_Id,
					(SELECT SUM(onderdeel_Norm) FROM Onderdeel WHERE Cursus_cursus_Id = o.Cursus_cursus_Id) AS totaleNorm
					FROM 
					Uren as u,
					Onderdeel as o
					WHERE
					User_user_Id = " . $_POST['student_Id'] . " 
					AND
					u.Onderdeel_onderdeel_Id = o.Onderdeel_Id
					GROUP BY 
					cursus"  );
				$statement->execute();
				$urenoverzichtData = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$array = array();
				$count = 0;
				foreach($urenoverzichtData as $uren )
				{
					$onderdeel_norm = $uren['totaleNorm'];
					$berekening = ($uren['studielast'] / $onderdeel_norm) * 100;
					if($berekening > 100)
					{
						$berekening = $berekening - 100;
						$berekening = "<font color=\"red\">".$berekening."%</font> boven";
					}
					else{
						$berekening = 100 - $berekening;
						$berekening = "<font color=\"green\">".$berekening."%</font> onder";
					}
					$totaaluren += $uren['studielast'];
					$studielast_in_uren = min_naar_uren($uren['studielast']);
					$array[] = array('uren_Id' => $uren['uren_Id'], 'studielast' => $studielast_in_uren, 'cursus_Id' => $uren['cursus_Id'], 'cursus' => $uren['cursus'], 'onderdeel_Norm' => min_naar_uren($uren['totaleNorm']), 'berekening' => $berekening);
					$count++;
				}
			}
			$urenoverzichtData = null;
			$sql = "SELECT * FROM User WHERE actief <> 0 AND Rol_rol_Id = 1";
			$statement = $this->db->prepare($sql);
			$statement->execute();
			$students = $statement->fetchAll(\PDO::FETCH_ASSOC);
			echo $this->twigRenderer->renderTemplate('urenoverzicht_slc.twig', array('id' => $id, 'students' => $students, 'student_Name' => $urenoverzichtData[0]['user_Name'], 'urenoverzichtarray' => $array ));
		}	
		else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

}