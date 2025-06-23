<?php
/**
 * Base Controller Class
 */

class BaseController
{
    protected $config;
    protected $db;
    protected $session;

    public function __construct($config)
    {
        $this->config = $config;
        $this->db = Database::getInstance($config['database']);
        $this->initializeSession();
        $this->checkRememberMe();
    }

    private function initializeSession()
    {
        $this->session = new Session();
    }

    private function checkRememberMe()
    {
        if (!$this->session->isLoggedIn() && isset($_COOKIE['remember_token'])) {
            $this->validateRememberToken($_COOKIE['remember_token']);
        }
    }

    private function validateRememberToken($token)
    {
        $user = $this->db->fetchOne(
            "SELECT u.*, rt.token FROM users u 
             JOIN remember_tokens rt ON u.id = rt.user_id 
             WHERE rt.token = ? AND rt.expires_at > NOW()",
            [$token]
        );

        if ($user) {
            $this->session->login($user);
            // Extend remember token
            $this->extendRememberToken($user['id'], $token);
        } else {
            // Invalid token, remove cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    private function extendRememberToken($userId, $token)
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->config['app']['remember_me_expiration']);
        $this->db->execute(
            "UPDATE remember_tokens SET expires_at = ? WHERE user_id = ? AND token = ?",
            [$expiresAt, $userId, $token]
        );
    }    protected function loadView($view, $data = [])
    {
        extract($data);
        $viewPath = APPPATH . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Vista no encontrada: $viewPath");
        }
        
        require_once $viewPath;
    }

    protected function redirect($url)
    {
        header('Location: ' . $this->config['app']['base_url'] . $url);
        exit;
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth()
    {
        if (!$this->session->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    protected function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
