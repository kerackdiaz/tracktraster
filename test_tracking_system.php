<?php
/**
 * Test Script - Verificar creación de seguimientos y métricas
 */

// Incluir configuración básica
require_once __DIR__ . '/application/core/Database.php';
require_once __DIR__ . '/application/services/AnalyticsService.php';
require_once __DIR__ . '/application/services/TrackingLifecycleService.php';

echo "<h1>Test: Verificación de Seguimientos y Métricas</h1>";

try {
    // Cargar configuración desde .env
    $config = ['database' => []];
    if (file_exists(__DIR__ . '/.env')) {
        $envContent = file_get_contents(__DIR__ . '/.env');
        $lines = explode("\n", $envContent);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            if ($key === 'DB_HOST') $config['database']['host'] = $value;
            elseif ($key === 'DB_NAME') $config['database']['name'] = $value;
            elseif ($key === 'DB_USER') $config['database']['user'] = $value;
            elseif ($key === 'DB_PASS') $config['database']['password'] = $value;
        }
    }
    
    // Conectar a base de datos
    $database = new Database($config['database']);
    $db = $database->getConnection();
    
    echo "<h2>✅ Conexión a base de datos exitosa</h2>";
    
    // Verificar trackings activos
    $trackings = $db->fetchAll(
        "SELECT at.id, at.artist_id, a.name, at.event_name, at.event_date, at.tracking_start_date, at.tracking_status
         FROM artist_trackings at 
         JOIN artists a ON at.artist_id = a.id 
         WHERE at.status = 'active'
         ORDER BY at.created_at DESC
         LIMIT 5"
    );
    
    echo "<h2>📊 Seguimientos Activos (últimos 5):</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Artista</th><th>Evento</th><th>Fecha Evento</th><th>Estado</th><th>Métricas</th></tr>";
    
    foreach ($trackings as $tracking) {
        // Verificar si tiene métricas
        $hasMetrics = $db->fetchOne(
            "SELECT COUNT(*) as count FROM spotify_metrics WHERE tracking_id = ?",
            [$tracking['id']]
        );
        
        echo "<tr>";
        echo "<td>{$tracking['id']}</td>";
        echo "<td>{$tracking['name']}</td>";
        echo "<td>" . ($tracking['event_name'] ?: 'Sin evento') . "</td>";
        echo "<td>" . ($tracking['event_date'] ? date('d/m/Y', strtotime($tracking['event_date'])) : 'N/A') . "</td>";
        echo "<td>{$tracking['tracking_status']}</td>";
        echo "<td>" . ($hasMetrics['count'] > 0 ? "✅ {$hasMetrics['count']} registros" : "❌ Sin métricas") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test de servicios
    echo "<h2>🧪 Test de Servicios:</h2>";
    
    // Test AnalyticsService
    try {
        $analyticsService = new AnalyticsService($db, $config);
        echo "<p>✅ AnalyticsService: Inicializado correctamente</p>";
        
        if (!empty($trackings)) {
            $firstTracking = $trackings[0];
            echo "<p>🔬 Probando con tracking ID: {$firstTracking['id']} ({$firstTracking['name']})</p>";
            
            // Test createInitialMetrics
            try {
                $result = $analyticsService->createInitialMetrics($firstTracking['id']);
                echo "<p>✅ createInitialMetrics: " . ($result ? "Exitoso" : "Sin cambios (ya existe)") . "</p>";
            } catch (Exception $e) {
                echo "<p>⚠️ createInitialMetrics: " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ AnalyticsService: Error - " . $e->getMessage() . "</p>";
    }
    
    // Test TrackingLifecycleService
    try {
        $lifecycleService = new TrackingLifecycleService($db);
        echo "<p>✅ TrackingLifecycleService: Inicializado correctamente</p>";
        
        if (!empty($trackings)) {
            $firstTracking = $trackings[0];
            
            // Test updateTrackingStatuses
            try {
                $lifecycleService->updateTrackingStatuses();
                echo "<p>✅ updateTrackingStatuses: Exitoso</p>";
            } catch (Exception $e) {
                echo "<p>⚠️ updateTrackingStatuses: " . $e->getMessage() . "</p>";
            }
            
            // Test getTrackingLifecycle
            try {
                $lifecycle = $lifecycleService->getTrackingLifecycle($firstTracking['id']);
                if ($lifecycle) {
                    echo "<p>✅ getTrackingLifecycle: Fase actual - {$lifecycle['phase']}</p>";
                    echo "<p>📅 Días para evento: {$lifecycle['days_to_event']}</p>";
                    echo "<p>📈 Progreso: {$lifecycle['progress_percentage']}%</p>";
                } else {
                    echo "<p>⚠️ getTrackingLifecycle: Sin datos de lifecycle</p>";
                }
            } catch (Exception $e) {
                echo "<p>❌ getTrackingLifecycle: " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ TrackingLifecycleService: Error - " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>💡 Recomendaciones:</h2>";
    $noMetricsCount = 0;
    foreach ($trackings as $tracking) {
        $hasMetrics = $db->fetchOne(
            "SELECT COUNT(*) as count FROM spotify_metrics WHERE tracking_id = ?",
            [$tracking['id']]
        );
        if ($hasMetrics['count'] == 0) {
            $noMetricsCount++;
        }
    }
    
    if ($noMetricsCount > 0) {
        echo "<p>⚠️ Hay {$noMetricsCount} seguimientos sin métricas iniciales.</p>";
        echo "<p>🔧 <a href='/analytics/populateMetrics?populate_key=populate_metrics_2025'>Ejecutar populate metrics</a></p>";
    } else {
        echo "<p>✅ Todos los seguimientos tienen métricas iniciales.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Fecha del test:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><a href='/'>← Volver al Dashboard</a> | <a href='/analytics'>Ver Analytics</a></p>";
?>
