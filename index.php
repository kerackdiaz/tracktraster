<?php
/**
 * TrackTraster - Music Artist Analytics Platform
 * Entry point for the application
 */

// Enable error reporting for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Define path constants
define('BASEPATH', __DIR__ . '/');
define('APPPATH', BASEPATH . 'application/');
define('SYSTEM', BASEPATH . 'system/');

// Debug: Check if URL parameter is being received
if (!isset($_GET['url']) || empty($_GET['url'])) {
    // If no URL is provided, redirect to auth/login
    $_GET['url'] = 'auth/login';
}

try {
    // Load the bootstrap file
    require_once APPPATH . 'core/Bootstrap.php';

    // Initialize the application
    $app = new Bootstrap();
    $app->run();
    
} catch (Exception $e) {
    // Show error for debugging
    echo '<h1>Error en TrackTraster</h1>';
    echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
    echo '<p><strong>Archivo:</strong> ' . $e->getFile() . ' (línea ' . $e->getLine() . ')</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
    echo '<hr>';
    echo '<p><a href="/debug.php">Ver Debug completo</a></p>';
}
