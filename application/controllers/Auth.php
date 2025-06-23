<?php
/**
 * Authentication Controller
 */

class Auth extends BaseController
{
    public function __construct($config)
    {
        parent::__construct($config);
    }    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->session->isLoggedIn()) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        } else {
            $this->showLoginForm();
        }
    }    public function register()
    {
        // If already logged in, redirect to dashboard
        if ($this->session->isLoggedIn()) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegistration();
        } else {
            $this->showRegistrationForm();
        }
    }

    public function logout()
    {
        // Clean up remember tokens from database
        if ($this->session->isLoggedIn()) {
            $userId = $this->session->getUserId();
            $this->db->execute("DELETE FROM remember_tokens WHERE user_id = ?", [$userId]);
        }

        $this->session->logout();
        $this->redirect('auth/login');
    }

    private function showLoginForm()
    {
        $data = [
            'title' => 'Iniciar Sesión - TrackTraster',
            'csrf_token' => $this->generateCSRFToken(),
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success')
        ];
        
        $this->loadView('auth/login', $data);
    }

    private function showRegistrationForm()
    {
        $data = [
            'title' => 'Registro - TrackTraster',
            'csrf_token' => $this->generateCSRFToken(),
            'error' => $this->session->getFlash('error'),
            'countries' => $this->config['countries']
        ];
        
        $this->loadView('auth/register', $data);
    }

    private function processLogin()
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('auth/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        // Validate input
        if (empty($email) || empty($password)) {
            $this->session->setFlash('error', 'Email y contraseña son requeridos');
            $this->redirect('auth/login');
        }

        // Find user
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ? AND active = 1",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $this->session->setFlash('error', 'Credenciales inválidas');
            $this->redirect('auth/login');
        }

        // Update last login
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );        // Login user
        $this->session->login($user);

        // Handle remember me
        if ($rememberMe) {
            $this->setRememberToken($user['id']);
        }

        $this->redirect('dashboard/index');
    }

    private function processRegistration()
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('auth/register');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $country = $_POST['country'] ?? '';
        $company = trim($_POST['company'] ?? '');

        // Validate input
        $errors = $this->validateRegistrationData($fullName, $email, $password, $confirmPassword, $country);

        if (!empty($errors)) {
            $this->session->setFlash('error', implode('<br>', $errors));
            $this->redirect('auth/register');
        }

        // Check if email already exists
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );

        if ($existingUser) {
            $this->session->setFlash('error', 'El email ya está registrado');
            $this->redirect('auth/register');
        }

        // Create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $result = $this->db->execute(
            "INSERT INTO users (full_name, email, password, country, company, created_at, active) 
             VALUES (?, ?, ?, ?, ?, NOW(), 1)",
            [$fullName, $email, $hashedPassword, $country, $company]
        );

        if ($result['affected_rows'] > 0) {
            $this->session->setFlash('success', 'Cuenta creada exitosamente. Ya puedes iniciar sesión.');
            $this->redirect('auth/login');
        } else {
            $this->session->setFlash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            $this->redirect('auth/register');
        }
    }

    private function validateRegistrationData($fullName, $email, $password, $confirmPassword, $country)
    {
        $errors = [];

        if (empty($fullName) || strlen($fullName) < 2) {
            $errors[] = 'El nombre completo es requerido (mínimo 2 caracteres)';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido es requerido';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        if (empty($country) || !array_key_exists($country, $this->config['countries'])) {
            $errors[] = 'Selecciona un país válido';
        }

        return $errors;
    }

    private function setRememberToken($userId)
    {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->config['app']['remember_me_expiration']);

        // Delete existing tokens for this user
        $this->db->execute("DELETE FROM remember_tokens WHERE user_id = ?", [$userId]);

        // Insert new token
        $this->db->execute(
            "INSERT INTO remember_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
            [$userId, $token, $expiresAt]
        );

        // Set cookie
        setcookie(
            'remember_token',
            $token,
            time() + $this->config['app']['remember_me_expiration'],
            '/',
            '',
            false, // Set to true in production with HTTPS
            true
        );
    }
}
