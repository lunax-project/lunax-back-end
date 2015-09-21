<?php

# Super directory
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

# Lunax files
define('LUNAXDIR', ROOT . DS . 'lunax');

# Application files
define('APPDIR', ROOT . DS . 'app');

# Include lunax definitions
require_once implode(DS, [
	LUNAXDIR,
	'Defines.php'
]);

/**
 * Para segurança de sua aplicação não remova as classes
 * As classes do lunax são:
 *   + Utils,
 *   + RequestURL,
 *   + Model,
 *   + View,
 *   + Template,
 *   + Controller,
 *   + Bootstrap
 */
$lunaxClasses = [
	'Utils',
	'RequestURL',
	'Model',
	'View',
	'Template',
	'Controller',
	'Bootstrap'
];

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
