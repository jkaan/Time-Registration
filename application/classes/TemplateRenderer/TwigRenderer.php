<?php

namespace Application\TemplateRenderer;

require_once('../vendor/autoload.php');

/**
 * This class is responsible for rendering a template
 * This class uses the Twig Templating Engine to render the template correctly
 *
 * @author Joey Kaan
 * @version 1.0.0
 */
class TwigRenderer {

	private $loader;
	private $twig;

	public function __construct() {
	}

	public function renderTemplate($page, Array $array = array()) {
		$loader = new \Twig_Loader_Filesystem('../templates');
		$twig = new \Twig_Environment($loader, array(
			'cache', '../templates/cache',
			));
		return $twig->render($page, $array);
	}
}