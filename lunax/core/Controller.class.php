<?php

abstract class Controller
{
	# Data to display on view
	public $view;

	/**
	 * Change application display template
	 */
	public function setTemplate($enabled)
	{
		$GLOBALS['app']->setConfig('template', $enabled);
	}

	public function __construct()
	{
		$this->view = new StdClass;
	}
}
