<?php

class Bootstrap
{
    public $request;
    private $controller;
    private $action;
    private $configs;

    private function loadConfigFile($dir)
    {
        $fullFileName = implode(DS, [
            $dir,
            'configs',
            'application.json'
        ]);

        if (file_exists($fullFileName)) {
            return json_decode(file_get_contents($fullFileName));
        } else {
            return new StdClass;
        }
    }

    private function extendsConfigs($newConfigs)
    {
        foreach ($newConfigs as $name => $value) {
            $this->setConfig($name, $value);
        }
    }

    public function getConfig($name) {
        if (array_key_exists($name, $this->configs)) {
            return $this->configs->$name;
        } else {
            return null;
        }
    }

    public function setConfig($name, $value) {
        if (array_key_exists($name, $this->configs)) {
            $this->configs->$name = $value;
        }
    }

    private function loadController()
    {
        $controllerName = $this->request->getControllerName();

        $controllerFile = implode(DS, [
            APPDIR,
            'controllers',
            "$controllerName.class.php"
        ]);

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $this->controller = new $controllerName($this->request->getParameters());

                if (method_exists($this->controller, 'beforeAction')) {
                    $this->controller->beforeAction();
                }

                return true;
            }
        }

        return false;
    }

    private function loadAction()
    {
        $actionName = $this->request->getActionName();
        if (method_exists($this->controller, $actionName)) {
            $this->controller->$actionName();
        } else {
            $this->controller->$actionName();
        }
    }

    private function makeViewName()
    {
        return implode(DS, [
            $this->request->getController(),
            $this->request->getAction()
        ]);
    }

    private function loadTemplate()
    {
        $view = $this->makeViewName();
        $controller = $this->request->getController();
        $templateMap = $this->getConfig('template_map');
        $templateName = 'default';

        if (isset($templateMap->$controller)) {
            $templateName = $templateMap->$controller;
        }

        $template = new Template(
            $templateName,
            $this->makeViewName(),
            $this->controller->view
        );
    }

    private function loadView()
    {
        $view = new View(
            $this->makeViewName(),
            $this->controller->view
        );
    }

    private function setNotLoaded()
    {
        $this->request->setController(
            $this->getConfig('not_found_controller')
        );

        if ($this->loadController()) {
            $this->loadAction();
        } else {
            Utils::error("Controller not found!");
        }
    }

    private function autoLoadModels()
    {

        function loadFile($dir, $fileName)
        {
            $fileName = implode(DS, [$dir, 'models', $fileName]);
            return (file_exists($fileName)) ? $fileName : false;
        }

        function autoload($className)
        {
            $className = ltrim($className, '\\');
            $file  = '';
            $namespace = '';

            if ($lastNsPos = strrpos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $file  = str_replace('\\', DS, $namespace) . DS;
            }

            $file .= str_replace('_', DS, $className) . '.class.php';

            if (!$fileName = loadFile(APPDIR, $file)) {
                $fileName = loadFile(LUNAXDIR, $file);
            }

            if ($fileName) {
                require $fileName;
            }
        }

        spl_autoload_register('autoload');
    }

    private function loadConfigs()
    {
        $defaultConfigs = (object)[
            'name'                  => null,
            'auth'                  => false,
            'template'              => false,
            'save_log'              => false,
            'display_errors'        => false,
            'not_found_controller'  => '',
            'auth_class'            => '',
            'auth_controller'       => '',
            'auth_action'           => '',
            'template_map'          => new StdClass,
            'url_map'               => new StdClass,
            'not_auth'              => []
        ];

        # Low priority default configs
        $this->configs = $defaultConfigs;

        # Medium priority lunax configs (global)
        $this->extendsConfigs($this->loadConfigFile(LUNAXDIR));

        # Hight priority application configs (local)
        $this->extendsConfigs($this->loadConfigFile(APPDIR));
    }

    private function checkAuth()
    {
        $controller = $this->request->getController();
        $allowAuth = $this->getConfig('not_auth');

        if ($this->getConfig('auth') && !in_array($controller, $allowAuth)) {
            $authClass = $this->getConfig('auth_class');
            $auth = new $authClass;
            return (method_exists($auth, 'isAuth') && $auth->isAuth());
        }

        return true;
    }

    public function run()
    {
        $defaultUrlName = $this->request->getDefaultUrlName();

        # Start autoload models
        $this->autoLoadModels();

        # Testing auth
        if ($this->checkAuth()) {
            # Run the controller
            if ($this->loadController()) {
                $this->loadAction();
            }

            elseif ($this->request->getUrlMap($defaultUrlName)) {
                $this->request->setController($defaultUrlName);
                if ($this->loadController()) {
                    $this->loadAction();
                } else {
                    $this->setNotLoaded();
                }
            }

            else {
                $this->setNotLoaded();
            }
        }

        else {
            # Set auth controller and action
            $this->request->setController($this->getConfig('auth_controller'));
            $this->request->setAction($this->getConfig('auth_action'));

            # Load auth controller and action
            if ($this->loadController()) {
                $this->loadAction();
            } else {
                $this->setNotLoaded();
            }
        }

        # Run the view or template
        if ($this->getConfig('template')) {
            $this->loadTemplate();
        } else {
            $this->loadView();
        }

        if (method_exists($this->controller, 'afterAction')) {
            $this->controller->afterAction();
        }
    }

    function __construct()
    {
        $this->loadConfigs();
        $this->request = new RequestURL($this->getConfig('url_map'));
    }
}