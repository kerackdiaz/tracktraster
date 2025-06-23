<?php
/**
 * Verificación de archivos estáticos
 * Script temporal para verificar que los assets se cargan correctamente
 */

echo '<h1>Verificación de archivos estáticos - TrackTraster</h1>';

$assets_to_check = [
    'assets/css/style.css',
    'assets/css/dashboard.css',
    'assets/js/app.js',
    'assets/js/dashboard.js',
    'assets/img/favicon.ico'
];

echo '<ul>';
foreach ($assets_to_check as $asset) {
    $file_path = __DIR__ . '/' . $asset;
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        echo '<li><span style="color: green;">✓</span> <strong>' . $asset . '</strong> - ' . $size . ' bytes</li>';
    } else {
        echo '<li><span style="color: red;">✗</span> <strong>' . $asset . '</strong> - NO ENCONTRADO</li>';
    }
}
echo '</ul>';

echo '<h2>URLs de prueba:</h2>';
echo '<ul>';
foreach ($assets_to_check as $asset) {
    $url = '/' . $asset;
    echo '<li><a href="' . $url . '" target="_blank">' . $url . '</a></li>';
}
echo '</ul>';

echo '<h2>Información del servidor:</h2>';
echo '<ul>';
echo '<li><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</li>';
echo '<li><strong>Script Name:</strong> ' . $_SERVER['SCRIPT_NAME'] . '</li>';
echo '<li><strong>Request URI:</strong> ' . $_SERVER['REQUEST_URI'] . '</li>';
echo '<li><strong>Server Name:</strong> ' . $_SERVER['SERVER_NAME'] . '</li>';
echo '</ul>';
?>
