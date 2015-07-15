<?php

abstract class Controller
{
	# Data to display on view
	public $view;

	# Param of URL request
	public $paramURL;

	/**
	 * Change application display template
	 */
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
