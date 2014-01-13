<?php

namespace Application\PartManager;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;

class SLCPartManager {

	public function slcPage($id) {
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
		$courses = $statement->fetchAll(\PDO::FETCH_ASSOC);

		// Gets all the students
		$statement = $db->prepare('SELECT * FROM User WHERE actief = :actief');
		$statement->bindValue('actief', 1);
		$statement->execute();
		$students = $statement->fetchAll(\PDO::FETCH_ASSOC);
		if((isLogged($id)) && ($result['Rol_rol_Id'] == 3)) {
			echo $twigRenderer->renderTemplate('slc.twig', array('id' => $id, 'courses' => $courses, 'students' => $students));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function addCourse($id) {
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

	public function editCourse($id, $courseId) {
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
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $twigRenderer->renderTemplate('editcourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
			} else {
				echo $twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	public function removeCourse($id, $courseId) {
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
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

				echo $twigRenderer->renderTemplate('removecourse.twig', array('course' => $results[0], 'id' => $id, 'courseId' => $courseId));
			} else {
				echo $twigRenderer->renderTemplate('noaccess.twig');
			}
		}
	}

	public function getStudentsOfCourse($id, $courseId) {
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
			$course = $statement->fetchAll(\PDO::FETCH_ASSOC);

		// Second part, this gets the students that are enrolled in the corresponding course
			$sqlStudentsOfCourse = "SELECT user_Name, user_Code, user_Email, user_Klas
			FROM Cursus_has_User as CU, Cursus as C, User as U
			WHERE CU.Cursus_Id = C.cursus_Id
			AND CU.User_Id = U.user_Id
			AND C.cursus_Id = :courseId";

			$statement = $db->prepare($sqlStudentsOfCourse);
			$statement->bindParam('courseId', $courseId);
			$statement->execute();
			$students = $statement->fetchAll(\PDO::FETCH_ASSOC);

		// Third part, this gets all of the students which exist but are not already enrolled in the corresponding course
			$sqlAllStudents = "SELECT * FROM User as U WHERE NOT EXISTS (SELECT User_Id FROM Cursus_has_User WHERE U.user_Id = User_Id AND Cursus_Id = :courseId )";

			$statement = $db->prepare($sqlAllStudents);
			$statement->bindParam('courseId', $courseId);
			$statement->execute();
			$allStudents = $statement->fetchAll(\PDO::FETCH_ASSOC);

			echo $twigRenderer->renderTemplate('studentsincourse.twig', array('course' => $course, 'students' => $students, 'courseId' => $courseId, 'id' => $id, 'allStudents' => $allStudents));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	public function addStudentToCourse($id, $courseId) {
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

	public function addStudent($id) {
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

public function editStudent($id, $studentId) {
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
			$student = $statement->fetchAll(\PDO::FETCH_ASSOC);
			echo $twigRenderer->renderTemplate('editstudent.twig', array('studentId' => $studentId, 'id' => $id, 'student' => $student[0]));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}

public function removeStudent($id, $studentId) {
	$app = \Slim\Slim::getInstance();
	$twigRenderer = new TwigRenderer();
	if(!empty($_POST)) {
		$db = Database::getInstance();
		$sql = "UPDATE User SET actief = :actief WHERE user_Id = :studentId";
		$statement = $db->prepare($sql);
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
			$results = $statement->fetchAll(\PDO::FETCH_ASSOC);

			echo $twigRenderer->renderTemplate('removestudent.twig', array('student' => $results[0], 'id' => $id, 'studentId' => $studentId));
		} else {
			echo $twigRenderer->renderTemplate('noaccess.twig');
		}
	}
}

}