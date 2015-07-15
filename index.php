<?php

# Super directory
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

# Include lunax definitions
require_once implode(DS, [
	ROOT,
	'lunax',
	'Defines.php'
]);

# Loading lunax classes
foreach ($lunaxClasses as $className) {
	require_once(implode(DS, [
		LUNAXDIR,
		'core',
		"$className.class.php"
	]));
}

# Prepare and run application
$GLOBALS['app'] = new Bootstrap;
$GLOBALS['app']->run();
