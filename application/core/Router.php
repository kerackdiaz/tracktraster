<?php
/**
 * Router Class - Handle URL routing and controller loading
 */

class Router
{
    private $config;
    private $controller = 'Auth';
    private $method = 'login';
    private $params = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->parseUrl();
    }

    public function dispatch()
    {
        // Load the controller
        $controllerFile = APPPATH . 'controllers/' . $this->controller . '.php';
        
        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($this->controller)) {
            $this->show404();
            return;
        }

        $controllerInstance = new $this->controller($this->config);

        if (!method_exists($controllerInstance, $this->method)) {
            $this->show404();
            return;
        }

        // Call the method with parameters
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);

            // Set controller
            if (isset($url[0]) && !empty($url[0])) {
                $this->controller = ucfirst(strtolower($url[0]));
                // Set default method to 'index' for most controllers
                $this->method = 'index';
            }

            // Set method
            if (isset($url[1]) && !empty($url[1])) {
                $this->method = strtolower($url[1]);
            }

            // Set parameters
            if (isset($url[2])) {
                $this->params = array_slice($url, 2);
            }
        }
    }

    private function show404()
    {
        http_response_code(404);
        require_once APPPATH . 'views/errors/404.php';
        exit;
    }
}
