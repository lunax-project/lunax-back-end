<?php

abstract class Controller
{
	# Data to display on view
	public $view;

	# Custom view file
	public $viewFile;

	/**
	 * Change application display template
	 */
	public function setTemplate($enabled)
	{
		configs::set('template', $enabled);
	}

	public function __construct()
	{
		$this->view = new StdClass;
	}
}
