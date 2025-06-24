<?php
/**
 * Script de prueba para verificar analytics
 */

// Definir constantes necesarias
define('BASEPATH', realpath(__DIR__) . '/');
define('APPPATH', BASEPATH . 'application/');

// Configuración básica
require_once 'application/core/Database.php';

// Cargar configuración de .env
$config = [];
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            $config[trim($key)] = trim($value);
        }
    }
}

// Configuración de base de datos
$dbConfig = [
    'hostname' => $_ENV['DB_HOST'] ?? 'localhost',
    'username' => $_ENV['DB_USERNAME'] ?? 'masrcom1_trackuser',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'database' => $_ENV['DB_DATABASE'] ?? 'masrcom1_tracktraster',
    'charset' => 'utf8mb4'
];

echo "=== CONFIGURACIÓN ===\n";
echo "Host: " . $dbConfig['hostname'] . "\n";
echo "Usuario: " . $dbConfig['username'] . "\n";
echo "Base de datos: " . $dbConfig['database'] . "\n\n";

// Crear conexión a base de datos
try {
    $db = Database::getInstance($dbConfig);
    echo "Conexión a base de datos establecida correctamente.\n\n";
} catch (Exception $e) {
    echo "ERROR conectando a base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== VERIFICACIÓN DE DATOS DE TESTING ===\n\n";

// Verificar usuarios
try {
    $users = $db->fetchAll("SELECT id, full_name, email FROM users LIMIT 3");
    echo "Usuarios en BD: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Nombre: {$user['full_name']}, Email: {$user['email']}\n";
    }
} catch (Exception $e) {
    echo "Error consultando usuarios: " . $e->getMessage() . "\n";
    $users = [];
}

echo "\n";

// Verificar artistas
try {
    $artists = $db->fetchAll("SELECT id, name, spotify_id FROM artists LIMIT 5");
    echo "Artistas en BD: " . count($artists) . "\n";
    foreach ($artists as $artist) {
        echo "- ID: {$artist['id']}, Nombre: {$artist['name']}, Spotify ID: " . ($artist['spotify_id'] ?: 'No definido') . "\n";
    }
} catch (Exception $e) {
    echo "Error consultando artistas: " . $e->getMessage() . "\n";
    $artists = [];
}

echo "\n";

// Verificar trackings
try {
    $trackings = $db->fetchAll("
        SELECT at.id, at.user_id, at.artist_id, a.name as artist_name, at.status 
        FROM artist_trackings at 
        JOIN artists a ON at.artist_id = a.id 
        LIMIT 5
    ");
    echo "Trackings en BD: " . count($trackings) . "\n";
    foreach ($trackings as $tracking) {
        echo "- Tracking ID: {$tracking['id']}, Usuario: {$tracking['user_id']}, Artista: {$tracking['artist_name']}, Status: {$tracking['status']}\n";
    }
} catch (Exception $e) {
    echo "Error consultando trackings: " . $e->getMessage() . "\n";
    $trackings = [];
}

echo "\n";

// Si hay datos suficientes, probar el AnalyticsService
if (!empty($users) && !empty($artists) && !empty($trackings)) {
    $userId = $users[0]['id'];
    $artistId = $trackings[0]['artist_id'];
    
    echo "=== TESTING ANALYTICS SERVICE ===\n";
    echo "Usuario ID: $userId\n";
    echo "Artista ID: $artistId\n\n";
    
    // Ahora probar el Analytics Service
    try {
        require_once 'application/services/AnalyticsService.php';
        
        $analyticsService = new AnalyticsService($db, $config);
        echo "AnalyticsService instanciado correctamente.\n";
        
        // Probar obtener analytics del artista
        echo "Probando obtener analytics para artista ID: $artistId, usuario: $userId\n";
        
        $analytics = $analyticsService->getArtistAnalytics($artistId, $userId);
        
        echo "Analytics obtenidas exitosamente!\n";
        echo "- Total followers: " . $analytics['summary']['total_followers'] . "\n";
        echo "- Popularidad promedio: " . $analytics['summary']['current_popularity'] . "\n";
        echo "- Plataformas encontradas: " . $analytics['summary']['platforms_count'] . "\n";
        echo "- Días de tracking: " . $analytics['summary']['tracking_days'] . "\n";
        
        if (!empty($analytics['platforms']['spotify'])) {
            echo "- Spotify followers: " . $analytics['platforms']['spotify']['followers'] . "\n";
            echo "- Spotify popularity: " . $analytics['platforms']['spotify']['popularity'] . "\n";
        }
        
        echo "\nURL de prueba: https://tracktraster.3mas1r.com/analytics?artist_id=$artistId\n";
        
    } catch (Exception $e) {
        echo "ERROR al probar AnalyticsService: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No hay suficientes datos para probar. Se necesitan usuarios, artistas y trackings.\n";
    echo "Consulte la base de datos manualmente o ejecute los scripts de instalación.\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";
?>
