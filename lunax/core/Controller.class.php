<?php

abstract class Controller
{
	public $view;
	public $paramURL;

	public function setTemplate($enabled)
	{
		$GLOBALS['app']->setConfig('template', $enabled);
	}

	public function __construct($parameters)
	{
		$this->view = new StdClass;
		$this->paramURL = $parameters;
	}
}