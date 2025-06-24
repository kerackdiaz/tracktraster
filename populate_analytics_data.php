<?php
/**
 * Poblar datos iniciales realistas para Analytics
 * Crear métricas históricas para todos los trackings activos
 */

// Configuración básica
define('BASEPATH', realpath(__DIR__) . '/');
define('APPPATH', BASEPATH . 'application/');

require_once 'application/core/Database.php';

// Cargar configuración de .env
$config = [];
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $config[trim($key)] = trim($value);
        }
    }
}

// Configuración de base de datos
$dbConfig = [
    'hostname' => $config['DB_HOST'] ?? 'localhost',
    'username' => $config['DB_USERNAME'] ?? '',
    'password' => $config['DB_PASSWORD'] ?? '',
    'database' => $config['DB_DATABASE'] ?? '',
    'charset' => 'utf8mb4'
];

echo "=== POBLANDO DATOS INICIALES PARA ANALYTICS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance($dbConfig);
    echo "✅ Conexión a base de datos exitosa\n\n";

    // Obtener todos los trackings activos
    $trackings = $db->fetchAll("
        SELECT at.*, a.name as artist_name, a.popularity as artist_popularity, a.followers_total
        FROM artist_trackings at 
        JOIN artists a ON at.artist_id = a.id 
        WHERE at.status = 'active'
        ORDER BY at.id
    ");

    if (empty($trackings)) {
        echo "❌ No hay trackings activos para poblar\n";
        exit;
    }

    echo "Encontrados " . count($trackings) . " trackings activos\n\n";

    foreach ($trackings as $tracking) {
        $trackingId = $tracking['id'];
        $artistName = $tracking['artist_name'];
        $trackingStart = $tracking['tracking_start_date'];
        $basePop = max(20, $tracking['artist_popularity'] ?: 30);
        $baseFollowers = max(1000, $tracking['followers_total'] ?: 5000);
        
        echo "📊 Procesando: $artistName (Tracking ID: $trackingId)\n";
        echo "   Inicio tracking: $trackingStart\n";
        echo "   Popularidad base: $basePop\n";
        echo "   Seguidores base: $baseFollowers\n";

        // Calcular días desde el inicio del tracking
        $startDate = new DateTime($trackingStart);
        $currentDate = new DateTime();
        $daysDiff = $startDate->diff($currentDate)->days;
        
        echo "   Días de tracking: $daysDiff\n";

        // Generar datos históricos desde la fecha de inicio hasta hoy
        $metricsToCreate = min(30, $daysDiff + 1); // Máximo 30 días de historia
        
        echo "   Generando $metricsToCreate días de métricas...\n";

        for ($i = 0; $i < $metricsToCreate; $i++) {
            $metricDate = (clone $startDate)->modify("+$i days")->format('Y-m-d');
            
            // Simular crecimiento orgánico con variaciones
            $growthFactor = 1 + ($i * 0.02); // 2% crecimiento por día
            $randomVariation = 1 + (mt_rand(-10, 15) / 100); // ±10% a +15% variación aleatoria
            
            // SPOTIFY METRICS
            $spotifyFollowers = round($baseFollowers * $growthFactor * $randomVariation);
            $spotifyPop = min(100, round($basePop + ($i * 0.1) + mt_rand(-2, 3)));
            $monthlyListeners = round($spotifyFollowers * (mt_rand(120, 300) / 100)); // 1.2x a 3x los seguidores
            
            $result = $db->execute(
                "INSERT IGNORE INTO spotify_metrics 
                 (tracking_id, metric_date, popularity, followers, monthly_listeners, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$trackingId, $metricDate, $spotifyPop, $spotifyFollowers, $monthlyListeners]
            );

            // DEEZER METRICS  
            $deezerFans = round($spotifyFollowers * (mt_rand(20, 40) / 100)); // 20-40% de Spotify
            
            $db->execute(
                "INSERT IGNORE INTO deezer_metrics 
                 (tracking_id, metric_date, fans, rank, albums_count, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$trackingId, $metricDate, $deezerFans, mt_rand(1000, 10000), mt_rand(3, 15)]
            );

            // LASTFM METRICS
            $lastfmListeners = round($spotifyFollowers * (mt_rand(10, 25) / 100)); // 10-25% de Spotify
            $scrobbles = round($lastfmListeners * mt_rand(50, 200)); // 50-200 scrobbles por listener
            
            $db->execute(
                "INSERT IGNORE INTO lastfm_metrics 
                 (tracking_id, metric_date, listeners, scrobbles, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$trackingId, $metricDate, $lastfmListeners, $scrobbles]
            );

            // YOUTUBE METRICS (si la tabla existe)
            try {
                $youtubeSubscribers = round($spotifyFollowers * (mt_rand(5, 15) / 100)); // 5-15% de Spotify
                $totalViews = $youtubeSubscribers * mt_rand(100, 500); // 100-500 views por subscriber
                $recentViews = round($totalViews * (mt_rand(5, 15) / 100)); // 5-15% views recientes
                
                $db->execute(
                    "INSERT IGNORE INTO youtube_metrics 
                     (tracking_id, metric_date, subscribers, total_views, recent_views, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())",
                    [$trackingId, $metricDate, $youtubeSubscribers, $totalViews, $recentViews]
                );
            } catch (Exception $e) {
                // Tabla youtube_metrics puede no existir
            }
        }

        // Crear snapshot inicial
        try {
            $initialSnapshot = [
                'spotify' => [
                    'followers' => $baseFollowers,
                    'popularity' => $basePop,
                    'monthly_listeners' => round($baseFollowers * 1.5)
                ],
                'deezer' => [
                    'fans' => round($baseFollowers * 0.3)
                ],
                'lastfm' => [
                    'listeners' => round($baseFollowers * 0.2),
                    'scrobbles' => round($baseFollowers * 10)
                ]
            ];

            $db->execute(
                "INSERT IGNORE INTO metric_snapshots 
                 (tracking_id, snapshot_type, snapshot_date, spotify_data, deezer_data, lastfm_data, combined_metrics, created_at) 
                 VALUES (?, 'initial', ?, ?, ?, ?, ?, NOW())",
                [
                    $trackingId, 
                    $trackingStart . ' 00:00:00',
                    json_encode($initialSnapshot['spotify']),
                    json_encode($initialSnapshot['deezer']),
                    json_encode($initialSnapshot['lastfm']),
                    json_encode($initialSnapshot)
                ]
            );
        } catch (Exception $e) {
            echo "   ⚠️  Error creando snapshot: " . $e->getMessage() . "\n";
        }

        // Actualizar datos del artista con métricas más recientes
        $latestSpotify = $db->fetchOne(
            "SELECT * FROM spotify_metrics WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 1",
            [$trackingId]
        );

        if ($latestSpotify) {
            $db->execute(
                "UPDATE artists SET 
                 popularity = ?, 
                 followers_total = ?,
                 total_followers_all_platforms = ?,
                 avg_popularity_all_platforms = ?,
                 updated_at = NOW()
                 WHERE id = ?",
                [
                    $latestSpotify['popularity'],
                    $latestSpotify['followers'],
                    $latestSpotify['followers'] + round($latestSpotify['followers'] * 0.3), // +30% otras plataformas
                    $latestSpotify['popularity'],
                    $tracking['artist_id']
                ]
            );
        }

        echo "   ✅ Métricas creadas exitosamente\n\n";
    }

    echo "=== VERIFICACIÓN DE DATOS CREADOS ===\n";
    
    // Verificar que se crearon los datos
    foreach ($trackings as $tracking) {
        $trackingId = $tracking['id'];
        $artistName = $tracking['artist_name'];
        
        $spotifyCount = $db->fetchOne("SELECT COUNT(*) as count FROM spotify_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        $deezerCount = $db->fetchOne("SELECT COUNT(*) as count FROM deezer_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        $lastfmCount = $db->fetchOne("SELECT COUNT(*) as count FROM lastfm_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        
        echo "$artistName (ID: $trackingId):\n";
        echo "  - Spotify: $spotifyCount métricas\n";
        echo "  - Deezer: $deezerCount métricas\n";
        echo "  - Last.fm: $lastfmCount métricas\n";
        
        // Mostrar métricas más recientes
        $latest = $db->fetchOne(
            "SELECT s.followers as spotify_followers, s.popularity, s.monthly_listeners,
                    d.fans as deezer_fans, l.listeners as lastfm_listeners
             FROM spotify_metrics s
             LEFT JOIN deezer_metrics d ON s.tracking_id = d.tracking_id AND s.metric_date = d.metric_date
             LEFT JOIN lastfm_metrics l ON s.tracking_id = l.tracking_id AND s.metric_date = l.metric_date
             WHERE s.tracking_id = ? 
             ORDER BY s.metric_date DESC LIMIT 1",
            [$trackingId]
        );
        
        if ($latest) {
            echo "  - Últimas métricas:\n";
            echo "    Spotify: {$latest['spotify_followers']} seguidores, {$latest['popularity']} popularidad, {$latest['monthly_listeners']} oyentes mensuales\n";
            echo "    Deezer: {$latest['deezer_fans']} fans\n";
            echo "    Last.fm: {$latest['lastfm_listeners']} oyentes\n";
        }
        echo "\n";
    }

    echo "✅ DATOS INICIALES POBLADOS EXITOSAMENTE\n";
    echo "📈 Los analytics ahora mostrarán datos realistas con tendencias de crecimiento\n";
    echo "🔗 Prueba en: /analytics?artist_id=1\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
?>
