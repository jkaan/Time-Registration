<?php

class Application {
	private $slim;
	private $routes;

	public function __construct() {
		$slim = new \Slim\Slim(array(
			'templates.path' => '../templates',
			));
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
				'URL' => '/student',
				'action' => 'studentPage',
				),
			array(
				'method' => 'get',
				'URL' => '/docent',
				'action' => 'docentPage',
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
				'method' => 'post',
				'URL' => '/uren',
				'action' => 'addStudieLast',
				)
			);
	}

	public function getRoutes() {
		return $this->routes;
	}
}