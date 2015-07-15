<?php

define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once implode(DS, [
	ROOT,
	'lunax',
	'Defines.php'
]);

foreach ($lunaxClasses as $className) {
	require_once(implode(DS, [
		LUNAXDIR,
		'core',
		"$className.class.php"
	]));
}

$GLOBALS['app'] = new Bootstrap;
$GLOBALS['app']->run();