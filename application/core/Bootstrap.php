<?php
/**
 * Bootstrap Class - Application Initializer
 */

class Bootstrap
{
    private $config;
    private $router;
    private $request;

    public function __construct()
    {
        $this->loadConfig();
        $this->initializeSession();
        $this->initializeDatabase();
        $this->initializeRouter();
    }

    public function run()
    {
        $this->router->dispatch();
    }

    private function loadConfig()
    {
        $this->config = include APPPATH . 'config/config.php';
    }

    private function initializeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => $this->config['app']['session_expiration'],
                'path' => '/',
                'domain' => '',
                'secure' => false, // Set to true in production with HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }    private function initializeDatabase()
    {
        require_once APPPATH . 'core/Database.php';
        require_once APPPATH . 'core/Session.php';
        require_once APPPATH . 'core/BaseController.php';
        $db = Database::getInstance($this->config['database']);
    }

    private function initializeRouter()
    {
        require_once APPPATH . 'core/Router.php';
        $this->router = new Router($this->config);
    }
}
