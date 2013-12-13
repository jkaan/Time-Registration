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
				'action' => 'addStudieLast',
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
				)
			);
}

public function getRoutes() {
	return $this->routes;
}

}