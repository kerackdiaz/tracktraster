<?php
/**
 * Session Management Class
 */

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    public function logout()
    {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        // Remove remember me cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUserEmail()
    {
        return $_SESSION['user_email'] ?? null;
    }

    public function getUserName()
    {
        return $_SESSION['user_name'] ?? null;
    }

    public function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $this->getUserId(),
                'email' => $this->getUserEmail(),
                'full_name' => $this->getUserName()
            ];
        }
        return null;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function setFlash($key, $message)
    {
        $_SESSION['flash_' . $key] = $message;
    }

    public function getFlash($key)
    {
        $message = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);
        return $message;
    }

    public function hasFlash($key)
    {
        return isset($_SESSION['flash_' . $key]);
    }
}
