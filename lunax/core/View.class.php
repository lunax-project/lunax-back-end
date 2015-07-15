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

		foreach ($dataView as $key => $value) {
			$this->$key = $value;
		}

		if (file_exists($filename)) {
			include $filename;
		}
	}
}