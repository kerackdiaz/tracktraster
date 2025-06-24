<?php
/**
 * Script para poblar métricas iniciales en las tablas vacías
 */

// Configuración básica
define('BASEPATH', realpath(__DIR__) . '/');
define('APPPATH', BASEPATH . 'application/');

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

require_once 'application/core/Database.php';
$db = Database::getInstance($dbConfig);

echo "=== POBLANDO MÉTRICAS INICIALES ===\n\n";

// Obtener todos los trackings activos
$trackings = $db->fetchAll(
    "SELECT at.id, at.artist_id, at.tracking_start_date, a.name as artist_name 
     FROM artist_trackings at 
     JOIN artists a ON at.artist_id = a.id 
     WHERE at.status = 'active'"
);

echo "Trackings encontrados: " . count($trackings) . "\n\n";

foreach ($trackings as $tracking) {
    echo "Procesando tracking ID: {$tracking['id']} - Artista: {$tracking['artist_name']}\n";
    
    $trackingId = $tracking['id'];
    $startDate = $tracking['tracking_start_date'];
    $artistName = $tracking['artist_name'];
    
    // Calcular días desde el inicio del tracking
    $daysToGenerate = min(30, max(1, floor((time() - strtotime($startDate)) / (60 * 60 * 24))));
    
    echo "  → Generando $daysToGenerate días de datos históricos\n";
    
    // Generar datos para Spotify
    $baseFollowers = rand(1000, 50000);
    $basePopularity = rand(20, 80);
    $baseListeners = rand(500, 25000);
    
    for ($i = $daysToGenerate; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("$startDate +$i days"));
        
        // Variación gradual día a día
        $dailyVariation = ($daysToGenerate - $i) * 0.5; // Crecimiento gradual
        $randomVariation = (sin($i * 0.2) * 100); // Variación natural
        
        $followers = round($baseFollowers + $dailyVariation * 10 + $randomVariation);
        $popularity = max(0, min(100, round($basePopularity + $dailyVariation * 0.3 + $randomVariation * 0.1)));
        $monthlyListeners = round($baseListeners + $dailyVariation * 5 + $randomVariation * 0.5);
        
        // Insertar en spotify_metrics
        try {
            $db->execute(
                "INSERT IGNORE INTO spotify_metrics 
                 (tracking_id, metric_date, popularity, followers, monthly_listeners, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$trackingId, $date, $popularity, $followers, $monthlyListeners]
            );
        } catch (Exception $e) {
            echo "    Error insertando Spotify: " . $e->getMessage() . "\n";
        }
        
        // Insertar en lastfm_metrics
        $lastfmListeners = round($followers * 0.3);
        $scrobbles = round($lastfmListeners * rand(50, 200));
        
        try {
            $db->execute(
                "INSERT IGNORE INTO lastfm_metrics 
                 (tracking_id, metric_date, listeners, scrobbles, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$trackingId, $date, $lastfmListeners, $scrobbles]
            );
        } catch (Exception $e) {
            echo "    Error insertando Last.fm: " . $e->getMessage() . "\n";
        }
        
        // Insertar en deezer_metrics
        $deezerFans = round($followers * 0.2);
        
        try {
            $db->execute(
                "INSERT IGNORE INTO deezer_metrics 
                 (tracking_id, metric_date, fans, created_at) 
                 VALUES (?, ?, ?, NOW())",
                [$trackingId, $date, $deezerFans]
            );
        } catch (Exception $e) {
            echo "    Error insertando Deezer: " . $e->getMessage() . "\n";
        }
        
        // Insertar en youtube_metrics
        $youtubeSubscribers = round($followers * 0.8);
        $youtubeViews = round($youtubeSubscribers * rand(20, 100));
        
        try {
            $db->execute(
                "INSERT IGNORE INTO youtube_metrics 
                 (tracking_id, metric_date, subscribers, total_views, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$trackingId, $date, $youtubeSubscribers, $youtubeViews]
            );
        } catch (Exception $e) {
            echo "    Error insertando YouTube: " . $e->getMessage() . "\n";
        }
    }
    
    echo "  ✅ Completado para {$tracking['artist_name']}\n\n";
}

// Verificar resultados
echo "=== VERIFICACIÓN DE RESULTADOS ===\n";

$tables = ['spotify_metrics', 'lastfm_metrics', 'deezer_metrics', 'youtube_metrics'];
foreach ($tables as $table) {
    $count = $db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
    echo "Tabla $table: $count registros\n";
}

echo "\n=== POBLADO COMPLETADO ===\n";
echo "Ahora puedes probar las analytics en: /analytics?artist_id=1\n";
?>
