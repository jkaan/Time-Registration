<?php

namespace Application;

use Application\TemplateRenderer\TwigRenderer;
use Application\Config\Database;
use Slim\Slim;

/**
 * This class represents the whole application
 * 
 * Contains all of the routes used by Slim to generate the correct pages
 *
 * @author Joey Kaan & Trinco Ingels
 * @version  1.0.1
 */
class Application {
	private $slim;
	private $twigRenderer;
	private $db;
	private $routes;

	/**
	 * Initializes all of the dependencies, Slim, TwigRenderer and the database connection
	 * Also sets all of the routes
	 */
	public function __construct() {
		$this->slim = Slim::getInstance();
		$this->twigRenderer = new TwigRenderer();
		$this->db = Database::getInstance();
		$this->setRoutes();
	}

	/**
	 * Responsible for rendering the login page
	 * @return Template that shows the login page
	 */
	public function loginPage() {
		echo $this->twigRenderer->renderTemplate('login.twig', array('page' => 'Start Page'));
	}

	/**
	 * When you login on the login page and click submit this method handles the request
	 *
	 * Checks for a valid username and password and redirects you to a page based on the role the user has (student, SLC, teacher)
	 */
	public function loginUser() {
		$statement = $this->db->prepare("SELECT rol_Naam, user_Id, user_Name FROM User, Rol WHERE user_Name = :username AND user_Pass = :password AND Rol.rol_Id = User.Rol_rol_Id AND actief <> 0 ");
		$statement->bindParam('username', $_POST['username']);
		$statement->bindParam('password', $_POST['password']);
		$statement->execute();
		$results = $statement->fetch(\PDO::FETCH_ASSOC);
		if($results > 0) {
			switch($results['rol_Naam']) {
				case 'student':
				updateUserOnlineTime($results['user_Id']);
				
				$this->slim->redirect(BASE . '/student/' . $results['user_Id']);
				break;
				case 'docent':
				updateUserOnlineTime($results['user_Id']);
				
				$this->slim->redirect(BASE . '/docent/' . $results['user_Id']);
				break;
				case 'slc':
				updateUserOnlineTime($results['user_Id']);
				
				$this->slim->redirect(BASE . '/slc/' . $results['user_Id']);
			}
		}else{
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	/**
	 * Responsible for logging out
	 * @param  Integer $id Id of the user
	 */
	public function logOut($id) {
		if(isLogged($id)) {
			$date = date('Y-m-d G:i:s');
			$statement = $this->db->prepare("UPDATE User SET user_Online = null WHERE user_Id= " . $id);
			$statement->execute();
			$this->slim->redirect(BASE . '/login');
		} else {
			echo $this->twigRenderer->renderTemplate('noaccess.twig');
		}
	}

	/**
	 * Sets all the routes used by the Slim framework
	 */
	private function setRoutes() {
		$this->routes = array(			
			array(
				'class' => 'Application\\Installation\\Install',
				'method' => 'get',
				'URL' => '/install',
				'action' => 'installDatabase',
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
				'URL' => '/slc/:id/overzicht',
				'action' => 'slcOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\SLCPartManager',
				'method' => 'post',
				'URL' => '/slc/:id/overzicht',
				'action' => 'slcOverzicht',
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
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/gebruikers',
				'action' => 'gebruikersOverzicht',
				),
			array(
				'class' => 'Application\\PartManager\\DocentPartManager',
				'method' => 'get',
				'URL' => '/docent/:id/gebruikers/:gebruikerId',
				'action' => 'profielVanStudent',
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

/**
 * Gets all of the routes used by Slim
 * @return Array that contains all of the Slim routes
 */
public function getRoutes() {
	return $this->routes;
}

}