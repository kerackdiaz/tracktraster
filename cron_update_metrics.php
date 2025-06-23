<?php
/**
 * Cron Job - Actualización diaria de métricas
 * Ejecutar diariamente para recopilar datos de las APIs
 */

// Configuración
$cronKey = 'tracktraster_cron_2025';
$baseUrl = 'https://tracktraster.3mas1r.com'; // Cambiar por tu URL

// Verificar que se está ejecutando desde línea de comandos o con clave válida
if (php_sapi_name() !== 'cli' && ($_GET['key'] ?? '') !== $cronKey) {
    die('Access denied');
}

echo "=== TrackTraster - Actualización de Métricas ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Llamar al endpoint de actualización
$url = $baseUrl . "/analytics/updateDailyMetrics?key=" . urlencode($cronKey);

$context = stream_context_create([
    'http' => [
        'timeout' => 300, // 5 minutos
        'user_agent' => 'TrackTraster-Cron/1.0'
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response !== false) {
    echo "✅ Actualización exitosa:\n";
    echo $response . "\n";
} else {
    echo "❌ Error en la actualización\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

echo "\n=== Finalizado ===\n";
?>
