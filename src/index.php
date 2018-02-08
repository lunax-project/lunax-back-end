<?php

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
	}

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	}
}

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
 *   + DBConnect,
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
	'DBConnect',
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
