<?php

# Version of lunax
define('LUNAX_VERSION', 2.0);

# Super directory
define('ROOT', dirname(__FILE__));

# Less directory separator name
define('DS', DIRECTORY_SEPARATOR);

# Lunax files
define('LUNAXDIR', ROOT . DS . 'lunax');

# Application files
define('APPDIR', ROOT . DS . 'app');

/**
 * The lunax clases:
 *   + Configs,
 *   + Utils,
 *   + RequestURL,
 *   + Model,
 *   + View,
 *   + Template,
 *   + Controller,
 *   + Bootstrap
 */
$lunaxClasses = [
	'Configs',
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
