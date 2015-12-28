<?php

/*
 * Index of controllers: RequestURL::getIndexControllers();
 * Index of actions:     RequestURL::getIndexActions();
 * Using LunAjax:        RequestURL::getUsingLunajax();
 * Full request:         RequestURL::getFullRequest();
 * Server root:          RequestURL::getServerRoot();
 * Request:              RequestURL::getRequest();
 * Default url name:     RequestURL::getDefaultUrlName();
 * Router url:         	 RequestURL::getRouterUrl();
 * Use restful:          RequestURL::getUseRestful();
 * Base absolute url:    RequestURL::getAbsoluteUrl();
 * Base url:             RequestURL::getBaseUrl();
 * Allow restful:        RequestURL::getAllowRestful($name);
 * Denny restful:        RequestURL::getDennyRestful($name);
 * Controller:           RequestURL::getController();
 * Controller name:      RequestURL::getControllerName();
 * Action:               RequestURL::getAction();
 * Action name:          RequestURL::getActionName();
 * Parameter by name:    RequestURL::getParameter($name);
 * Parameters:           RequestURL::getParameters();
 */

class RequestURL
{
	# Index of controllers and actions
	private static $indexControllers = 0;
	private static $indexActions = 1;

	private static $usingLunajax = false;

	private static $fullRequest;
	private static $absoluteUrl;
	private static $serverRoot;
	private static $baseUrl;
	private static $partsRequest;
	private static $defaultUrlName;

	# Restful variables
	private static $usingRestful;
	private static $allowRestful;
	private static $dennyRestful;

	private static $router;

	private static $controller;
	private static $controllerName;
	private static $action;
	private static $actionName;
	private static $parameters;

	private static function setIndexControllers($indexControllers)
	{
		self::$indexControllers = $indexControllers;
	}

	private static function getIndexControllers()
	{
		return self::$indexControllers;
	}

	# -------------------------------------------

	private static function setIndexActions($indexActions)
	{
		self::$indexActions = $indexActions;
	}

	private static function getIndexActions()
	{
		return self::$indexActions;
	}

	# -------------------------------------------

	private static function setUsingLunajax($usingLunajax)
	{
		self::$usingLunajax = $usingLunajax;
	}

	public static function getUsingLunajax()
	{
		return self::$usingLunajax;
	}

	# -------------------------------------------

	private static function setDefaultUrlName($defaultUrlName)
	{
		self::$defaultUrlName = $defaultUrlName;
	}

	public static function getDefaultUrlName()
	{
		return self::$defaultUrlName;
	}

	# -------------------------------------------

	private static function setAbsoluteUrl($absoluteUrl)
	{
		self::$absoluteUrl = $absoluteUrl;
	}

	public static function getAbsoluteUrl()
	{
		return self::$absoluteUrl;
	}

	# -------------------------------------------

	private static function setBaseUrl($baseUrl)
	{
		self::$baseUrl = $baseUrl;
	}

	public static function getBaseUrl()
	{
		return self::$baseUrl;
	}

	# -------------------------------------------

	private static function setRouterUrl($router)
	{
		self::$router = $router;
	}

	public static function getRouterUrl($name)
	{
		return isset(self::$router->$name) ?
			self::$router->$name : false;
	}

	# -------------------------------------------

	private static function setUseRestful($usingRestful)
	{
		self::$usingRestful = $usingRestful;
	}

	public static function getUseRestful()
	{
		return self::$usingRestful;
	}

	# -------------------------------------------

	private static function setAllowRestful($allowRestful)
	{
		self::$allowRestful = $allowRestful;
	}

	public static function getAllowRestful($name)
	{
		return in_array($name, self::$allowRestful);
	}

	# -------------------------------------------

	private static function setDennyRestful($dennyRestful)
	{
		self::$dennyRestful = $dennyRestful;
	}

	public static function getDennyRestful($name)
	{
		return in_array($name, self::$dennyRestful);
	}

	# -------------------------------------------

	private static function setFullRequest($fullRequest)
	{
		self::$fullRequest = $fullRequest;
	}

	public static function getFullRequest()
	{
		return self::$fullRequest;
	}

	# -------------------------------------------

	private static function setServerRoot($serverRoot)
	{
		self::$serverRoot = $serverRoot;
	}

	public static function getServerRoot()
	{
		return self::$serverRoot;
	}

	# -------------------------------------------

	/**
	 * Gets the value of controller.
	 *
	 * @return mixed
	 */
	public static function getController()
	{
		return self::$controller;
	}

	public static function setController($controller)
	{
		self::$controller = $controller;

		self::setControllerName(self::makeRequestName(
			'',
			$controller,
			'controller'
		));

		if ($paramUrl = self::getRouterUrl(self::getController())) {

			/*
			 * Sempre que o controller tiver salvo no mapa de url
			 * a ação será a padrão
			 */
			self::setAction(self::$defaultUrlName);

			/*
			 *	Se o controller for o primeiro nome na URL começa
			 *	a pegar os parâmetros, senão começa a pegar do primeiro
			 *	para que toda a url seja considerada parâmetro,
			 *	isso acontece quando o controller é o index e o primeiro
			 *	parâmetro não é index
			 */
			$init = (self::getUrlName(
				self::getIndexControllers()) == $controller
			) ? self::getIndexControllers() + 1 : self::getIndexControllers();

			# Se a ação padrão já tiver na url não será passada como parâmetro
			if (self::getUrlName($init) == self::$defaultUrlName) {
				$init++;
			}
		}

		else {
			self::setAction(self::getUrlName(self::getIndexActions()));
			$init = self::getIndexActions() + 1;

			$paramUrl = self::getRouterUrl(
				self::getController() . '/' .
				self::getAction()
			);
		}

		self::setParameters(self::prepareParameters($paramUrl, $init));
	}

	private static function setControllerName($controllerName)
	{
		self::$controllerName = $controllerName;
	}

	/**
	 * Gets the value of controllerName.
	 *
	 * @return mixed
	 */
	public static function getControllerName()
	{
		return self::$controllerName;
	}

	/**
	 * Gets the value of action.
	 *
	 * @return mixed
	 */
	public static function getAction()
	{
		return self::$action;
	}

	public static function setAction($action)
	{
		# Save current action
		self::$action = $action;

		# Used if using restful
		$requestMethod = '';

		# Check if is allow or denny restful
		if (
			!self::getDennyRestful(self::$controller) &&
			(self::getUseRestful() || self::getAllowRestful(self::$controller))
		) {
			$requestMethod = $_SERVER['REQUEST_METHOD'];
		}

		self::setActionName(lcfirst(self::makeRequestName(
			$requestMethod,
			$action,
			'action'
		)));

	}

	/**
	 * Gets the value of actionName.
	 *
	 * @return mixed
	 */
	public static function getActionName()
	{
		return self::$actionName;
	}

	private static function setActionName($actionName)
	{
		self::$actionName = $actionName;
	}

	/**
	 * Gets the value of parameters.
	 *
	 * @return mixed
	 */
	public static function getParameters()
	{
		return self::$parameters;
	}

	private static function setParameters($parameters)
	{
		self::$parameters = $parameters;
	}

	public static function getParameter($name)
	{
		$parameters = self::getParameters();
		return array_key_exists($name, $parameters)
			? $parameters[$name] : null;
	}

	# -------------------------------------------

	/**
	 * Replace accents to equivalent letters
	 * @return string
	 */
	private static function replaceAccents($string)
	{
		$search = explode(',',
			'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,
			À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ');

		$replace = explode(',',
			'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,
			A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE');

		return str_replace($search, $replace, $string);
	}


	private static function camelCase($str)
	{
		// Deixa só textos e números da string sem acento
		// Converte o que não for em espaço
		$str = preg_replace('/[^a-z0-9]+/i', ' ', self::replaceAccents($str));

		// Deixa maiúscula o primeiro caractere de cada palavra
		return str_replace(' ', '', ucwords(trim(strtolower($str))));
	}

	private static function makeRequestName($before, $name, $after)
	{
		return self::camelCase(implode(' ', [$before, $name, $after]));
	}

	public static function getRequest()
	{
		$regex = '/^(' . str_replace('/', '\/', self::getServerRoot() ) .')/';
		return preg_replace($regex, '', self::getFullRequest());
	}

	private static function getPartsRequest()
	{
		preg_match_all('/\/([\w-]*)/', self::getRequest(), $exp);
		return $exp[1];
	}

	private static function prepareParameters($paramsNames, $init)
	{
		$countData	= count(self::$partsRequest);
		$parameters	= [];

		if ($paramsNames) {
			$paramsNames = explode("/", $paramsNames);
		}

		for ($i = $init; $i < $countData; $i++) {
			if (self::$partsRequest[$i] !== '') {
				if ($paramsNames && isset($paramsNames[$i - $init])) {
					$paramName = $paramsNames[$i - $init];
					$parameters[$paramName] = self::$partsRequest[$i];
				} else {
					array_push($parameters, self::$partsRequest[$i]);
				}
			}
		}

		return $parameters;
	}

	/**
	 * Get the URL name by index
	 */
	private static function getUrlName($index)
	{
		return (
			isset(self::$partsRequest[$index]) &&
			!empty(self::$partsRequest[$index])
		) ? self::$partsRequest[$index] : self::$defaultUrlName;
	}

	/**
	 * Detect page refresh
	 * @return boolean
	 */
	public static function isRefresh()
	{
		return isset($_SERVER['HTTP_CACHE_CONTROL']) &&
				   $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
	}

	/**
	 * Detect secure connection
	 * @return boolean
	 */
	public static function isSecure()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| $_SERVER['SERVER_PORT'] == 443;
	}

	public static function configure()
	{
		$regexServerRoot = '/(.*)[\\\\\/]index\.php$/';
		$uri = $_SERVER['REQUEST_URI'];
		self::setDefaultUrlName('index');
		self::setFullRequest(parse_url($uri)['path']);

		self::setServerRoot(preg_replace(
			$regexServerRoot,
			'$1',
			$_SERVER['SCRIPT_NAME']
		));

		self::setAbsoluteUrl(
			# http or https
			'http' . (self::isSecure() ? 's' : '') .
			'://' . $_SERVER['SERVER_NAME']
		);

		self::setBaseUrl(self::getAbsoluteUrl() . self::getServerRoot());

		self::$partsRequest = self::getPartsRequest();

		// Set values
		self::setRouterUrl(configs::get('router_url'));
        self::setUseRestful(configs::get('use_restful'));
		self::setAllowRestful(configs::get('allow_restful'));
		self::setDennyRestful(configs::get('denny_restful'));

		if (configs::get('lunajax') &&
			(self::getUrlName(0) == configs::get('lunajax_controller'))) {
			self::setIndexControllers(1);
			self::setIndexActions(2);
			self::setUsingLunajax(true);
		}

		# Set controller
		self::setController(self::getUrlName(self::getIndexControllers()));
	}
}
