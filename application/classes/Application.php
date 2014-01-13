<?php

namespace Application;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;

class Application {
	private $slim;
	private $routes;

	public function __construct() {
		$this->setRoutes();
	}

	public function startPage() {
		$app = \Slim\Slim::getInstance();
		$twigRenderer = new TwigRenderer();
		echo $twigRenderer->renderTemplate('index.twig', array('page' => 'Start Page'));
	}

	public function loginPage() {
		$app = \Slim\Slim::getInstance();
		$twigRenderer = new TwigRenderer();
		echo $twigRenderer->renderTemplate('login.twig', array('page' => 'Start Page'));
	}

	public function loginUser() {
		$db = Database::getInstance();
		$statement = $db->prepare("SELECT rol_Naam, user_Id, user_Name FROM User, Rol WHERE user_Name = :username AND user_Pass = :password AND Rol.rol_Id = User.Rol_rol_Id");
		$statement->bindParam('username', $_POST['username']);
		$statement->bindParam('password', $_POST['password']);
		$statement->execute();
		$results = $statement->fetch(\PDO::FETCH_ASSOC);
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

	public function logOut($id){
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

	private function setRoutes() {
		$this->routes = array(
			array(
				'class' => 'Application\\Application',
				'method' => 'get',
				'URL' => '/',
				'action' => 'startPage',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id',
				'action' => 'studentPage',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id',
				'action' => 'slcPage',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc',
				'action' => 'slcPage',
				),
			array(
				'class' => 'Application\\Application',
				'method' => 'get',
				'URL' => '/login',
				'action' => 'loginPage',
				),
			array(
				'class' => 'Application\\Application',
				'method' => 'post',
				'URL' => '/login',
				'action' => 'loginUser',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/uren/add',
				'action' => 'urenPage',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'post',
				'URL' => '/student/:id/uren/add',
				'action' => 'urenPage',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/course/add',
				'action' => 'addCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/course/add',
				'action' => 'addCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/course/edit/:courseId',
				'action' => 'editCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/course/edit/:courseId',
				'action' => 'editCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/course/remove/:courseId',
				'action' => 'removeCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/course/remove/:courseId',
				'action' => 'removeCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/course/students/:courseId', // Not sure about this URL yet
				'action' => 'getStudentsOfCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/course/students/:courseId/add',
				'action' => 'addStudentToCourse',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/student/add',
				'action' => 'addStudent',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/student/add',
				'action' => 'addStudent',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/student/edit/:studentId',
				'action' => 'editStudent',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/student/edit/:studentId',
				'action' => 'editStudent',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'get',
				'URL' => '/slc/:id/student/remove/:studentId',
				'action' => 'removeStudent',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/student/remove/:studentId',
				'action' => 'removeStudent',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/',
				'action' => 'docentPage',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/profiel',
				'action' => 'studentProfiel',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'post',
				'URL' => '/student/:id/profiel',
				'action' => 'studentProfiel',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/feedback',
				'action' => 'studentFeedback',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/feedback/:itemId',
				'action' => 'studentFeedbackItem',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/overzicht',
				'action' => 'studentOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'post',
				'URL' => '/student/:id/overzicht',
				'action' => 'studentOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/overzicht',
				'action' => 'docentOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'post',
				'URL' => '/docent/:id/overzicht',
				'action' => 'docentOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/cursus',
				'action' => 'docentCursusBeheer',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen',
				'action' => 'cursusOnderdelen',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'post',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen/add',
				'action' => 'addOnderdeelToCursus',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen/:onderdeelId/edit',
				'action' => 'editOnderdeelFromCursus',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'post',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen/:onderdeelId/edit',
				'action' => 'editOnderdeelFromCursus',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen/:onderdeelId/remove',
				'action' => 'removeOnderdeelFromCursus',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'post',
				'URL' => '/docent/:id/cursus/:cursusId/onderdelen/:onderdeelId/remove',
				'action' => 'removeOnderdeelFromCursus',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/overzicht/details/:userid-:weeknr-:jaar-:cursusid',
				'action' => 'docentOverzichtDetail',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/overzicht/feedback/:userid-:weeknr-:cursusid',
				'action' => 'docentFeedback',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'post',
				'URL' => '/docent/:id/overzicht/feedback/:userid-:weeknr-:cursusid',
				'action' => 'docentFeedback',
				),
			array(
				'class' => 'Application\\Application',
				'method' => 'get',
				'URL' => '/:id/logout',
				'action' => 'logOut',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/overzicht/details/:weeknr-:jaar-:cursusid',
				'action' => 'studentOverzichtDetail',
				),
			array(
				'class' => 'Application\\PartManager\\StudentPartManager',
				'method' => 'get',
				'URL' => '/student/:id/overzicht/details/:weeknr-:jaar-:onderdeelid/onderdeel',
				'action' => 'studentOverzichtDetailOnderdeel',
				)
			);
}

public function getRoutes() {
	return $this->routes;
}

}