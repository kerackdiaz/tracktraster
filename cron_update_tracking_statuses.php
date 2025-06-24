<?php
/**
 * Cron Job - Actualizar estados de tracking
 * 
 * Ejecutar diariamente para mantener los estados de tracking actualizados:
 * 0 6 * * * /usr/bin/php /path/to/tracktraster/cron_update_tracking_statuses.php
 */

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/cron_errors.log');

// Log de inicio
$logMessage = "[" . date('Y-m-d H:i:s') . "] Iniciando actualización de estados de tracking\n";
file_put_contents(__DIR__ . '/logs/cron.log', $logMessage, FILE_APPEND | LOCK_EX);

try {
    // Incluir configuración y clases necesarias
    require_once __DIR__ . '/application/core/Database.php';
    require_once __DIR__ . '/application/services/TrackingLifecycleService.php';
    
    // Cargar configuración
    $config = [];
    if (file_exists(__DIR__ . '/.env')) {
        $envContent = file_get_contents(__DIR__ . '/.env');
        $lines = explode("\n", $envContent);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            if ($key === 'DB_HOST') $config['host'] = $value;
            elseif ($key === 'DB_NAME') $config['name'] = $value;
            elseif ($key === 'DB_USER') $config['user'] = $value;
            elseif ($key === 'DB_PASS') $config['password'] = $value;
        }
    }
    
    // Configuración por defecto si no hay .env
    if (empty($config)) {
        $config = [
            'host' => 'localhost',
            'name' => 'masrcom1_tracktraster',
            'user' => 'masrcom1_tracktraster',
            'password' => 'your_password_here'
        ];
    }
    
    // Conectar a base de datos
    $database = new Database($config);
    $db = $database->getConnection();
    
    // Inicializar servicio de lifecycle
    $lifecycleService = new TrackingLifecycleService($db);
    
    // Actualizar estados
    $lifecycleService->updateTrackingStatuses();
    
    // Estadísticas de la actualización
    $stats = $db->fetchAll(
        "SELECT tracking_status, COUNT(*) as count 
         FROM artist_trackings 
         WHERE status = 'active' 
         GROUP BY tracking_status"
    );
    
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Estados actualizados exitosamente:\n";
    foreach ($stats as $stat) {
        $logMessage .= "  - {$stat['tracking_status']}: {$stat['count']} trackings\n";
    }
    
    file_put_contents(__DIR__ . '/logs/cron.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    echo "Estados de tracking actualizados exitosamente\n";
    foreach ($stats as $stat) {
        echo "- {$stat['tracking_status']}: {$stat['count']} trackings\n";
    }
    
} catch (Exception $e) {
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/logs/cron.log', $errorMessage, FILE_APPEND | LOCK_EX);
    
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Proceso completado\n";
?>
