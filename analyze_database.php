<?php
/**
 * Análisis completo de la base de datos TrackTraster
 * Para identificar qué tablas tienen datos y cuáles están vacías
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

echo "=== ANÁLISIS COMPLETO DE BASE DE DATOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance($dbConfig);
    echo "✅ Conexión a base de datos exitosa\n\n";

    // Lista de tablas principales
    $tables = [
        'users',
        'artists', 
        'artist_trackings',
        'spotify_metrics',
        'deezer_metrics',
        'lastfm_metrics',
        'youtube_metrics',
        'platform_metrics',
        'metric_snapshots',
        'growth_analytics'
    ];

    echo "=== ESTADO DE TABLAS ===\n";
    foreach ($tables as $table) {
        try {
            $count = $db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
            $status = $count > 0 ? "✅ $count registros" : "❌ VACÍA";
            echo sprintf("%-20s %s\n", $table . ':', $status);
        } catch (Exception $e) {
            echo sprintf("%-20s ❌ NO EXISTE\n", $table . ':');
        }
    }

    echo "\n=== ANÁLISIS DETALLADO ===\n\n";

    // 1. Usuarios
    echo "1. USUARIOS:\n";
    $users = $db->fetchAll("SELECT id, full_name, email, country, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    foreach ($users as $user) {
        echo "   - ID: {$user['id']}, {$user['full_name']} ({$user['email']}) - {$user['country']}\n";
    }
    echo "\n";

    // 2. Artistas
    echo "2. ARTISTAS:\n";
    $artists = $db->fetchAll("SELECT id, name, spotify_id, deezer_id, lastfm_name, followers_total, popularity, created_at FROM artists ORDER BY created_at DESC LIMIT 10");
    foreach ($artists as $artist) {
        echo "   - ID: {$artist['id']}, '{$artist['name']}'\n";
        echo "     Spotify: " . ($artist['spotify_id'] ?: 'No') . "\n";
        echo "     Deezer: " . ($artist['deezer_id'] ?: 'No') . "\n";
        echo "     Last.fm: " . ($artist['lastfm_name'] ?: 'No') . "\n";
        echo "     Seguidores: {$artist['followers_total']}, Popularidad: {$artist['popularity']}\n";
        echo "     Creado: {$artist['created_at']}\n\n";
    }

    // 3. Trackings
    echo "3. TRACKINGS ACTIVOS:\n";
    $trackings = $db->fetchAll("
        SELECT at.*, a.name as artist_name, u.full_name as user_name
        FROM artist_trackings at 
        JOIN artists a ON at.artist_id = a.id 
        JOIN users u ON at.user_id = u.id 
        WHERE at.status = 'active'
        ORDER BY at.created_at DESC 
        LIMIT 10
    ");
    
    if (empty($trackings)) {
        echo "   ❌ NO HAY TRACKINGS ACTIVOS\n\n";
    } else {
        foreach ($trackings as $tracking) {
            echo "   - Tracking ID: {$tracking['id']}\n";
            echo "     Usuario: {$tracking['user_name']} (ID: {$tracking['user_id']})\n";
            echo "     Artista: {$tracking['artist_name']} (ID: {$tracking['artist_id']})\n";
            echo "     País: {$tracking['country_code']}\n";
            echo "     Inicio: {$tracking['tracking_start_date']}\n";
            echo "     Status: {$tracking['status']}\n";
            if ($tracking['event_name']) {
                echo "     Evento: {$tracking['event_name']} - {$tracking['event_date']}\n";
            }
            echo "\n";
        }
    }

    // 4. Métricas por plataforma
    echo "4. MÉTRICAS POR PLATAFORMA:\n";
    
    $platforms = ['spotify_metrics', 'deezer_metrics', 'lastfm_metrics', 'youtube_metrics'];
    foreach ($platforms as $platform) {
        try {
            $count = $db->fetchOne("SELECT COUNT(*) as count FROM $platform")['count'];
            echo "   $platform: $count registros\n";
            
            if ($count > 0) {
                $latest = $db->fetchAll("SELECT * FROM $platform ORDER BY metric_date DESC LIMIT 3");
                foreach ($latest as $metric) {
                    echo "     - Tracking {$metric['tracking_id']}: {$metric['metric_date']}\n";
                    if (isset($metric['followers'])) echo "       Followers: {$metric['followers']}\n";
                    if (isset($metric['fans'])) echo "       Fans: {$metric['fans']}\n";
                    if (isset($metric['listeners'])) echo "       Listeners: {$metric['listeners']}\n";
                    if (isset($metric['subscribers'])) echo "       Subscribers: {$metric['subscribers']}\n";
                    if (isset($metric['popularity'])) echo "       Popularity: {$metric['popularity']}\n";
                }
            }
        } catch (Exception $e) {
            echo "   $platform: ❌ NO EXISTE\n";
        }
    }
    echo "\n";

    // 5. Verificar datos iniciales faltantes
    echo "5. DATOS INICIALES FALTANTES:\n";
    
    foreach ($trackings as $tracking) {
        $trackingId = $tracking['id'];
        $artistName = $tracking['artist_name'];
        
        echo "   Tracking ID $trackingId ($artistName):\n";
        
        // Verificar métricas iniciales
        $hasSpotify = $db->fetchOne("SELECT COUNT(*) as count FROM spotify_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        $hasDeezer = $db->fetchOne("SELECT COUNT(*) as count FROM deezer_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        $hasLastfm = $db->fetchOne("SELECT COUNT(*) as count FROM lastfm_metrics WHERE tracking_id = ?", [$trackingId])['count'];
        
        echo "     - Spotify metrics: " . ($hasSpotify > 0 ? "✅ $hasSpotify" : "❌ NINGUNA") . "\n";
        echo "     - Deezer metrics: " . ($hasDeezer > 0 ? "✅ $hasDeezer" : "❌ NINGUNA") . "\n";
        echo "     - Last.fm metrics: " . ($hasLastfm > 0 ? "✅ $hasLastfm" : "❌ NINGUNA") . "\n";
        
        // Verificar snapshot inicial
        try {
            $hasSnapshot = $db->fetchOne("SELECT COUNT(*) as count FROM metric_snapshots WHERE tracking_id = ? AND snapshot_type = 'initial'", [$trackingId])['count'];
            echo "     - Snapshot inicial: " . ($hasSnapshot > 0 ? "✅" : "❌ FALTA") . "\n";
        } catch (Exception $e) {
            echo "     - Snapshot inicial: ❌ TABLA NO EXISTE\n";
        }
        
        echo "\n";
    }

    // 6. Problema identificado
    echo "6. PROBLEMAS IDENTIFICADOS:\n";
    
    $problems = [];
    
    if (empty($trackings)) {
        $problems[] = "❌ No hay trackings activos - necesario crear al menos uno para testing";
    }
    
    foreach ($trackings as $tracking) {
        $trackingId = $tracking['id'];
        $hasAnyMetrics = false;
        
        foreach ($platforms as $platform) {
            try {
                $count = $db->fetchOne("SELECT COUNT(*) as count FROM $platform WHERE tracking_id = ?", [$trackingId])['count'];
                if ($count > 0) $hasAnyMetrics = true;
            } catch (Exception $e) {
                // Tabla no existe
            }
        }
        
        if (!$hasAnyMetrics) {
            $problems[] = "❌ Tracking ID $trackingId no tiene datos iniciales - se necesita crear métricas base";
        }
    }
    
    if (empty($problems)) {
        echo "   ✅ No se detectaron problemas críticos\n";
    } else {
        foreach ($problems as $problem) {
            echo "   $problem\n";
        }
    }

    echo "\n=== RECOMENDACIONES ===\n";
    echo "1. Crear datos iniciales para trackings existentes\n";
    echo "2. Implementar proceso de captura de métricas iniciales al crear tracking\n";
    echo "3. Verificar que AnalyticsService esté poblando datos correctamente\n";
    echo "4. Considerar crear datos de prueba realistas\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";
?>
