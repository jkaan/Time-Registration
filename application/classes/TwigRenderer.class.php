<?php

require_once('../vendor/autoload.php');

class TwigRenderer {

	private $loader;
	private $twig;

	public function __construct() {
	}

	public function renderTemplate($page, Array $array = array()) {
		$loader = new Twig_Loader_Filesystem('../templates');
		$twig = new Twig_Environment($loader, array(
			'cache', '../templates/cache',
			));
		return $twig->render($page, $array);
	}
}