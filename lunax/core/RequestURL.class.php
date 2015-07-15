<?php

/*
 * $request = RequestURL(object);
 * Full request:         $request->getFullRequest();
 * Server root:          $request->getServerRoot();
 * Request:              $request->getRequest();
 * Default url name:     $request->getDefaultUrlName();
 * Url map:              $request->getUrlMap();
 * Controller:           $request->getController();
 * Controller name:      $request->getControllerName();
 * Action:               $request->getAction();
 * Action name:          $request->getActionName();
 * Parameters:           $request->getParameters();
 */

class RequestURL
{
	private $fullRequest;
	private $absoluteUrl;
	private $serverRoot;
	private $partsRequest;
	private $defaultUrlName;

	private $urlMap;

	private $controller;
	private $controllerName;
	private $action;
	private $actionName;
	private $parameters;

	private function setDefaultUrlName($defaultUrlName)
	{
		$this->defaultUrlName = $defaultUrlName;
		return $this;
	}

	public function getDefaultUrlName()
	{
		return $this->defaultUrlName;
	}

	# -------------------------------------------

	public function getAbsoluteUrl()
	{
		return $this->absoluteUrl;
	}

	private function setAbsoluteUrl($absoluteUrl)
	{
		$this->absoluteUrl = $absoluteUrl;
		return $this;
	}

	# -------------------------------------------

	private function setUrlMap($urlMap)
	{
		$this->urlMap = $urlMap;
		return $this;
	}

	public function getUrlMap($name)
	{
		return isset($this->urlMap->$name) ?
			$this->urlMap->$name : false;
	}

	# -------------------------------------------

	public function getFullRequest()
	{
		return $this->fullRequest;
	}

	private function setFullRequest($fullRequest)
	{
		$this->fullRequest = $fullRequest;
		return $this;
	}

	# -------------------------------------------

	public function getServerRoot()
	{
		return $this->serverRoot;
	}

	private function setServerRoot($serverRoot)
	{
		$this->serverRoot = $serverRoot;
		return $this;
	}

	# -------------------------------------------

	/**
	 * Gets the value of controller.
	 *
	 * @return mixed
	 */
	public function getController()
	{
		return $this->controller;
	}

	public function setController($controller)
	{
		$this->controller = $controller;

		$this->setControllerName($this->makeRequestName(
			'',
			$controller,
			'controller'
		));

		if ($paramUrl = $this->getUrlMap($this->getController())) {

			/*
			 * Sempre que o controller tiver salvo no mapa de url
			 * a ação será a padrão
			 */
			$this->setAction($this->defaultUrlName);

			/*
			 *	Se o controller for o primeiro nome na URL começa
			 *	a pegar os parâmetros, senão começa a pegar do primeiro
			 *	para que toda a url seja considerada parâmetro,
			 *	isso acontece quando o controller é o index e o primeiro
			 *	parâmetro não é index
			 */
			$init = ($this->getUrlName(0) == $controller) ? 1 : 0;

			# Se a ação padrão já tiver na url não será passada como parâmetro
			if ($this->getUrlName($init) == $this->defaultUrlName) {
				$init ++;
			}
		}

		else {
			$this->setAction($this->getUrlName(1));
			$init = 2;

			$paramUrl = $this->getUrlMap(
				$this->getController() . '/' .
				$this->getAction()
			);
		}

		$this->setParameters($this->prepareParameters($paramUrl, $init));
		return $this;
	}

	/**
	 * Gets the value of controllerName.
	 *
	 * @return mixed
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	private function setControllerName($controllerName)
	{
		$this->controllerName = $controllerName;
		return $this;
	}

	/**
	 * Gets the value of action.
	 *
	 * @return mixed
	 */
	public function getAction()
	{
		return $this->action;
	}

	public function setAction($action)
	{
		$this->action = $action;
		$this->setActionName(lcfirst($this->makeRequestName(
			$_SERVER['REQUEST_METHOD'],
			$action,
			'action'
		)));
		return $this;
	}

	/**
	 * Gets the value of actionName.
	 *
	 * @return mixed
	 */
	public function getActionName()
	{
		return $this->actionName;
	}

	private function setActionName($actionName)
	{
		$this->actionName = $actionName;
		return $this;
	}

	/**
	 * Gets the value of parameters.
	 *
	 * @return mixed
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	private function setParameters($parameters)
	{
		$this->parameters = $parameters;
		return $this;
	}

	# -------------------------------------------

	private function replaceAccents($string)
	{
		$search = explode(',',
			'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,
			À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ');

		$replace = explode(',',
			'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,
			A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE');

		return str_replace($search, $replace, $string);
	}


	private function camelCase($str)
	{
		// Deixa só textos e números da string sem acento
		// Converte o que não for em espaço
		$str = preg_replace('/[^a-z0-9]+/i', ' ', $this->replaceAccents($str));

		// Deixa maiúscula o primeiro caractere de cada palavra
		return str_replace(' ', '', ucwords(trim(strtolower($str))));
	}

	private function makeRequestName($before, $name, $after)
	{
		return $this->camelCase(implode(' ', [
			$before,
			$name,
			$after
		]));
	}

	public function getRequest()
	{
		$regex = '/^(' . str_replace('/', '\/', $this->getServerRoot() ) .')/';
		return preg_replace($regex, '', $this->getFullRequest());
	}

	private function getPartsRequest()
	{
		preg_match_all('/\/([\w-]*)/', $this->getRequest(), $exp);
		return $exp[1];
	}

	private function prepareParameters($paramsNames, $init)
	{
		$countData	= count($this->partsRequest);
		$parameters	= [];

		if ($paramsNames) {
			$paramsNames = explode("/", $paramsNames);
		}

		for ($i = $init; $i < $countData; $i++) {
			if ($this->partsRequest[$i] !== '') {
				if ($paramsNames && isset($paramsNames[$i - $init])) {
					$paramName = $paramsNames[$i - $init];
					$parameters[$paramName] = $this->partsRequest[$i];
				} else {
					array_push($parameters, $this->partsRequest[$i]);
				}
			}
		}

		return $parameters;
	}

	private function getUrlName($index)
	{
		return (isset($this->partsRequest[$index]) && !empty($this->partsRequest[$index])) ?
			$this->partsRequest[$index] : $this->defaultUrlName;
	}

	/**
	 * Detect page refresh
	 * @return boolean
	 */
	public function isRefresh()
	{
		return isset($_SERVER['HTTP_CACHE_CONTROL']) &&
				   $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
	}

	/**
	 * Detect secure connection
	 * @return boolean
	 */
	public function isSecure()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
					|| $_SERVER['SERVER_PORT'] == 443;
	}

	public function __construct($urlMap = [])
	{
		$uri = $_SERVER['REQUEST_URI'];
		$this->setDefaultUrlName('index');
		$this->setFullRequest(parse_url($uri)['path']);
		$this->setServerRoot(preg_replace('/(.*)\/index\.php$/',  '$1', $_SERVER['SCRIPT_NAME']));
		$this->setAbsoluteUrl('http' . ($this->isSecure() ? 's' : '') .  '://' . $_SERVER['SERVER_NAME']);
		$this->partsRequest = $this->getPartsRequest();
		$this->setUrlMap($urlMap);
		$this->setController($this->getUrlName(0));
	}
}