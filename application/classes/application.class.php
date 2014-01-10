<?php

class Application {
	private $slim;
	private $routes;

	public function __construct() {
		$this->setRoutes();
	}

	private function setRoutes() {
		$this->routes = array(
			array(
				'method' => 'get',
				'URL' => '/',
				'action' => 'startPage',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id',
				'action' => 'studentPage',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id',
				'action' => 'slcPage',
				),
			array(
				'method' => 'get',
				'URL' => '/slc',
				'action' => 'slcPage',
				),
			array(
				'method' => 'get',
				'URL' => '/login',
				'action' => 'loginPage',
				),
			array(
				'method' => 'post',
				'URL' => '/login',
				'action' => 'loginUser',
				),
			array(
				'method' => 'get',
				'URL' => '/uren',
				'action' => 'urenPage',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/uren/add',
				'action' => 'urenPage',
				),
			array(
				'method' => 'post',
				'URL' => '/student/:id/uren/add',
				'action' => 'urenPage',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/course/add',
				'action' => 'addCourse',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/course/add',
				'action' => 'addCourse',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/course/edit/:courseId',
				'action' => 'editCourse',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/course/edit/:courseId',
				'action' => 'editCourse',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/course/remove/:courseId',
				'action' => 'removeCourse',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/course/remove/:courseId',
				'action' => 'removeCourse',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/course/students/:courseId', // Not sure about this URL yet
				'action' => 'getStudentsOfCourse',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/course/students/:courseId/add',
				'action' => 'addStudentToCourse',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/student/add',
				'action' => 'addStudent',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/student/add',
				'action' => 'addStudent',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/student/edit/:studentId',
				'action' => 'editStudent',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/student/edit/:studentId',
				'action' => 'editStudent',
				),
			array(
				'method' => 'get',
				'URL' => '/slc/:id/student/remove/:studentId',
				'action' => 'removeStudent',
				),
			array(
				'method' => 'post',
				'URL' => '/slc/:id/student/remove/:studentId',
				'action' => 'removeStudent',
				),
			array(
				'method' => 'get',
				'URL' => '/docent/:id/',
				'action' => 'docentPage',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/profiel',
				'action' => 'studentProfiel',
				),
			array(
				'method' => 'post',
				'URL' => '/student/:id/profiel',
				'action' => 'studentProfiel',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/feedback',
				'action' => 'studentFeedback',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/feedback/:itemId',
				'action' => 'studentFeedbackItem',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/overzicht',
				'action' => 'studentOverzicht',
				),
			array(
				'method' => 'post',
				'URL' => '/student/:id/overzicht',
				'action' => 'studentOverzicht',
				),
			array(
				'method' => 'get',
				'URL' => '/docent/:id/overzicht',
				'action' => 'docentOverzicht',
				),
			array(
				'method' => 'post',
				'URL' => '/docent/:id/overzicht',
				'action' => 'docentOverzicht',
				),
			array(
				'method' => 'get',
<<<<<<< HEAD
				'URL' => '/docent/:id/cursus',
				'action' => 'docentCursusBeheer',
				),
			array(
				'method' => 'get',
				'URL' => '/docent/:id/overzicht/details/:userid-:weeknr-:cursusid',
=======
				'URL' => '/docent/:id/overzicht/details/:userid-:weeknr-:jaar-:cursusid',
>>>>>>> b0441d8b19203ff499f4a8afc2eec1f4456d2e81
				'action' => 'docentOverzichtDetail',
				),
			array(
				'method' => 'get',
				'URL' => '/docent/:id/overzicht/feedback/:userid-:weeknr-:cursusid',
				'action' => 'docentFeedback',
				),
			array(
				'method' => 'post',
				'URL' => '/docent/:id/overzicht/feedback/:userid-:weeknr-:cursusid',
				'action' => 'docentFeedback',
				),
			array(
				'method' => 'get',
				'URL' => '/:id/logout',
				'action' => 'logOut',
				),
			array(
				'method' => 'get',
				'URL' => '/student/:id/overzicht/details/:weeknr-:jaar-:cursusid',
				'action' => 'studentOverzichtDetail',
				),
			array(
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