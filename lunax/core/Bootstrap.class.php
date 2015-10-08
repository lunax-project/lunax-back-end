<?php

class Bootstrap
{
    public $request;
    private $controller;
    private $action;

    /**
     * Load application controller
     * @return Boolean  true if success
     */
    private function loadController()
    {
        $controllerName = RequestURL::getControllerName();

        $controllerFile = implode(DS, [
            APPDIR,
            'controllers',
            "$controllerName.class.php"
        ]);

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $this->controller = new $controllerName();

                if (method_exists($this->controller, 'beforeAction')) {
                    $this->controller->beforeAction();
                }

                Utils::log("Controller \"$controllerName\" loaded!");
                return true;
            }
        }

        Utils::log("Controller \"$controllerName\" not loaded!");
        return false;
    }

    /**
     * Load page action
     */
    private function loadAction()
    {
        $actionName = RequestURL::getActionName();

        if (method_exists($this->controller, $actionName)) {
            $this->controller->$actionName();
            Utils::log("Action \"$actionName\" loaded!");
            return true;
        } else {
            Utils::error("Action \"$actionName\" not found!", true);
            return false;
        }
    }

    /**
     * Make a name to view
     * @return String
     */
    private function makeViewName()
    {
        return implode(DS, [
            RequestURL::getController(),
            RequestURL::getAction()
        ]);
    }

    /**
     * Load page template
     */
    private function loadTemplate()
    {
        $view = $this->makeViewName();
        $controller = RequestURL::getController();
        $templateMap = configs::get('template_map');

        # Template name default
        $templateName = 'default';

        # Verify template map to make template name
        if (isset($templateMap->$controller)) {
            $templateName = $templateMap->$controller;
        }

        $template = new Template(
            $templateName,
            $this->makeViewName(),
            $this->controller->view
        );
    }

    /**
     * Load page view
     */
    private function loadView()
    {
        $view = new View(
            $this->makeViewName(),
            isset($this->controller->view) ? $this->controller->view : []
        );
    }

    /**
     * Action if controller is not loaded
     */
    private function setNotLoaded()
    {
        RequestURL::setController(
            configs::get('not_found_controller')
        );

        if ($this->loadController()) {
            $this->loadAction();
        } else {
            Utils::error('Fatal error on load controller!', true);
        }
    }

    /**
     * Prepare a autoload of models
     */
    private function autoLoadModels()
    {
        # Load model file
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

    /**
     * Check application have auth to continue
     */
    private function checkAuth()
    {
        $controller = RequestURL::getController();
        $allowAuth = configs::get('not_auth');

        # Check auth config and auth allow
        if (configs::get('auth') && !in_array($controller, $allowAuth)) {
            $authClass = configs::get('auth_class');
            $auth = new $authClass;

            # Check is auth in the class
            return (method_exists($auth, 'isAuth') && $auth->isAuth());
        }

        return true;
    }

    /**
     * Run application
     */
    public function run()
    {
        # Load application configs
        configs::load();

        # Configure the request
        RequestURL::configure();

        $defaultUrlName = RequestURL::getDefaultUrlName();

        # Start autoload models
        $this->autoLoadModels();

        # Testing auth
        if ($this->checkAuth()) {

            # Run the controller
            if ($this->loadController()) {
                $this->loadAction();
            }

            # Check url map default controller
            elseif (RequestURL::getRouterUrl($defaultUrlName)) {
                RequestURL::setController($defaultUrlName);

                # Load url map default
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
            RequestURL::setController(configs::get('auth_controller'));
            RequestURL::setAction(configs::get('auth_action'));

            # Load auth controller and action
            if ($this->loadController()) {
                $this->loadAction();
            } else {
                $this->setNotLoaded();
            }
        }

        # Run the view or template
        if (configs::get('template')) {
            $this->loadTemplate();
        } else {
            $this->loadView();
        }

        # Run after action
        if (method_exists($this->controller, 'afterAction')) {
            $this->controller->afterAction();
        }
    }
}
