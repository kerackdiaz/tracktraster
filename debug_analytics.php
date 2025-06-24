<?php
/**
 * Debug Analytics - Endpoint para verificar el estado del sistema de analytics
 */

// Solo permitir acceso local o con clave
$allowedIPs = ['127.0.0.1', '::1'];
$debugKey = $_GET['debug_key'] ?? '';
$validKey = 'debug_analytics_2025';

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs) && $debugKey !== $validKey) {
    http_response_code(403);
    die('Access denied. Use ?debug_key=debug_analytics_2025');
}

// Configuración básica
define('BASEPATH', realpath(__DIR__) . '/');
define('APPPATH', BASEPATH . 'application/');

echo "<h1>TrackTraster Analytics Debug</h1>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// Verificar archivos necesarios
echo "<h2>1. Verificación de Archivos</h2>";
$requiredFiles = [
    'application/core/Database.php',
    'application/services/AnalyticsService.php',
    'application/libraries/MusicPlatformManager.php',
    'application/controllers/Analytics.php',
    '.env'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "<p>$status $file</p>";
}

// Verificar configuración
echo "<h2>2. Configuración .env</h2>";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $lines = explode("\n", $envContent);
    $config = [];
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    $dbKeys = ['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE'];
    foreach ($dbKeys as $key) {
        $value = $config[$key] ?? 'NO DEFINIDO';
        $masked = $key === 'DB_PASSWORD' ? str_repeat('*', strlen($value)) : $value;
        echo "<p><strong>$key:</strong> $masked</p>";
    }
} else {
    echo "<p>❌ Archivo .env no encontrado</p>";
}

// Probar conexión a base de datos
echo "<h2>3. Conexión a Base de Datos</h2>";
try {
    require_once 'application/core/Database.php';
    
    $dbConfig = [
        'hostname' => $config['DB_HOST'] ?? 'localhost',
        'username' => $config['DB_USERNAME'] ?? '',
        'password' => $config['DB_PASSWORD'] ?? '',
        'database' => $config['DB_DATABASE'] ?? '',
        'charset' => 'utf8mb4'
    ];
    
    $db = Database::getInstance($dbConfig);
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Verificar tablas necesarias
    $tables = ['users', 'artists', 'artist_trackings'];
    foreach ($tables as $table) {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
        echo "<p><strong>Tabla $table:</strong> $count registros</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// Probar AnalyticsService
echo "<h2>4. AnalyticsService</h2>";
try {
    require_once 'application/services/AnalyticsService.php';
    $analyticsService = new AnalyticsService($db, $config);
    echo "<p>✅ AnalyticsService instanciado correctamente</p>";
} catch (Exception $e) {
    echo "<p>❌ Error al instanciar AnalyticsService: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Probar MusicPlatformManager
echo "<h2>5. MusicPlatformManager</h2>";
try {
    require_once 'application/libraries/MusicPlatformManager.php';
    $platformManager = new MusicPlatformManager($config);
    echo "<p>✅ MusicPlatformManager instanciado correctamente</p>";
    
    $platforms = $platformManager->getAvailablePlatformNames();
    echo "<p><strong>Plataformas disponibles:</strong> " . implode(', ', $platforms) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error al instanciar MusicPlatformManager: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Mostrar logs recientes
echo "<h2>6. Logs de Error Recientes</h2>";
$logFiles = [
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    '../logs/error.log',
    'error.log'
];

$foundLog = false;
foreach ($logFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "<p><strong>Archivo de log:</strong> $logFile</p>";
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20); // Últimas 20 líneas
        echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
        foreach ($recentLines as $line) {
            if (strpos($line, 'AnalyticsService') !== false || strpos($line, 'TrackTraster') !== false) {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
        $foundLog = true;
        break;
    }
}

if (!$foundLog) {
    echo "<p>❌ No se encontraron archivos de log accesibles</p>";
}

echo "<h2>7. Test Básico</h2>";
if (isset($db) && isset($analyticsService)) {
    try {
        // Buscar un usuario y artista de prueba
        $user = $db->fetchOne("SELECT id FROM users LIMIT 1");
        $artist = $db->fetchOne("SELECT id FROM artists LIMIT 1");
        
        if ($user && $artist) {
            echo "<p>Probando con Usuario ID: {$user['id']}, Artista ID: {$artist['id']}</p>";
            
            // Crear tracking de prueba si no existe
            $tracking = $db->fetchOne(
                "SELECT id FROM artist_trackings WHERE user_id = ? AND artist_id = ?",
                [$user['id'], $artist['id']]
            );
            
            if (!$tracking) {
                $result = $db->execute(
                    "INSERT INTO artist_trackings (user_id, artist_id, status, tracking_start_date, country_code, created_at) VALUES (?, ?, 'active', CURDATE(), 'MX', NOW())",
                    [$user['id'], $artist['id']]
                );
                echo "<p>✅ Tracking de prueba creado</p>";
            }
            
            // Probar analytics
            $analytics = $analyticsService->getArtistAnalytics($artist['id'], $user['id']);
            echo "<p>✅ Analytics obtenidas correctamente</p>";
            echo "<p><strong>URL de prueba:</strong> <a href='/analytics?artist_id={$artist['id']}'>/analytics?artist_id={$artist['id']}</a></p>";
            
        } else {
            echo "<p>❌ No hay usuarios o artistas en la base de datos para hacer pruebas</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error en test básico: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<hr><p><em>Debug completado a las " . date('Y-m-d H:i:s') . "</em></p>";
?>
