<?php

class View
{
	private function includeFile($dir, $file)
	{
		$filename = implode(DS, [
			APPDIR,
			'views',
			$dir,
			$file
		]);

		if (file_exists($filename)) {
			if (include $filename) {
				Utils::log("File \"$filename\" included!");
				return true;
			}
		} else {
			return false;
		}
	}

	public function __construct($name, $dataView)
	{
		# Setting data of controller to view
		foreach ($dataView as $key => $value) {
			$this->$key = $value;
		}

		# Include the logical file
		$this->includeFile('logical', "$name.php");

		# Include the output file
		if (!$this->includeFile('output', "$name.phtml")) {
			Utils::error("View file \"$name.phtml\" not found on output path!");
		}
	}
}
