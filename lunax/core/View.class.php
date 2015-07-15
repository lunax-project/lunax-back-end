<?php

class View
{
	public function __construct($name, $dataView)
	{
		$filename = implode(DS, [
			APPDIR,
			'views',
			"$name.phtml"
		]);

		# Setting data of controller to view
		foreach ($dataView as $key => $value) {
			$this->$key = $value;
		}

		if (file_exists($filename)) {
			include $filename;
			Utils::log("View file \"$filename\" readed!");
		} else {
			Utils::error("View file \"$filename\" not found!");
		}
	}
}
