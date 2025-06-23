<?php
/**
 * TrackTraster Database Setup/Update Script
 * Actualiza la base de datos manteniendo los usuarios existentes
 * 
 * URL: http://tu-dominio.com/database/setup.php?key=tracktraster_update_2025
 */

// Clave de seguridad para ejecutar el script
$setup_key = 'tracktraster_update_2025';

// Verificar clave de seguridad
if (!isset($_GET['key']) || $_GET['key'] !== $setup_key) {
    die('❌ Acceso denegado. Clave de seguridad requerida.');
}

// Cargar configuración
require_once '../application/config/config.php';
require_once '../application/libraries/EnvLoader.php';
EnvLoader::load();

echo '<h1>🔧 TrackTraster Database Setup/Update</h1>';
echo '<pre>';

// Configuración de la base de datos
$db_config = [
    'host' => EnvLoader::get('DB_HOSTNAME', 'localhost'),
    'username' => EnvLoader::get('DB_USERNAME', 'root'),
    'password' => EnvLoader::get('DB_PASSWORD', ''),
    'database' => EnvLoader::get('DB_DATABASE', 'tracktraster_db'),
];

try {
    // Conectar a MySQL
    echo "📡 Conectando a MySQL...\n";
    $pdo = new PDO(
        "mysql:host={$db_config['host']}",
        $db_config['username'],
        $db_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Crear base de datos si no existe
    echo "🗄️  Verificando base de datos '{$db_config['database']}'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$db_config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$db_config['database']}");
    
    // Verificar si existen usuarios
    echo "👥 Verificando usuarios existentes...\n";
    $existing_users = false;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $user_count = $stmt->fetchColumn();
        $existing_users = $user_count > 0;
        echo "   ✅ Encontrados {$user_count} usuarios existentes\n";
    } catch (PDOException $e) {
        echo "   ℹ️  Tabla de usuarios no existe aún\n";
    }

    // Ejecutar schema
    echo "🔄 Ejecutando schema actualizado...\n";
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    
    // Dividir en statements individuales
    $statements = array_filter(
        preg_split('/;\s*$/m', $schema),
        function($stmt) {
            return trim($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );

    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || preg_match('/^\s*--/', $statement)) continue;
        
        try {
            $pdo->exec($statement);
            $success_count++;
            
            // Mostrar progreso para operaciones importantes
            if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                echo "   ✅ Tabla '{$matches[1]}' creada/actualizada\n";
            } elseif (preg_match('/CREATE VIEW\s+(\w+)/i', $statement, $matches)) {
                echo "   👁️  Vista '{$matches[1]}' creada\n";
            } elseif (preg_match('/CREATE INDEX\s+(\w+)/i', $statement, $matches)) {
                echo "   📊 Índice '{$matches[1]}' creado\n";
            }
        } catch (PDOException $e) {
            $error_count++;
            
            // Ignorar errores esperados
            if (
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate key name') !== false ||
                strpos($e->getMessage(), 'Unknown table') !== false
            ) {
                // Error esperado, continuar
                continue;
            }
            
            echo "   ⚠️  Error (ignorado): " . substr($e->getMessage(), 0, 100) . "...\n";
        }
    }

    // Verificar estructura final
    echo "\n📋 Verificando estructura final...\n";
    
    $required_tables = [
        'users', 'artists', 'artist_trackings', 'platform_metrics',
        'spotify_metrics', 'deezer_metrics', 'lastfm_metrics',
        'youtube_metrics', 'metric_snapshots', 'growth_analytics',
        'alerts', 'reports', 'system_logs', 'remember_tokens'
    ];
    
    $existing_tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existing_tables[] = $row[0];
    }
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    $extra_tables = array_diff($existing_tables, $required_tables);
    
    echo "   ✅ Tablas existentes: " . count($existing_tables) . "\n";
    echo "   📊 Tablas requeridas: " . count($required_tables) . "\n";
    
    if (empty($missing_tables)) {
        echo "   🎉 Todas las tablas requeridas están presentes\n";
    } else {
        echo "   ❌ Tablas faltantes: " . implode(', ', $missing_tables) . "\n";
    }

    // Verificar columnas críticas
    echo "\n🔍 Verificando columnas críticas...\n";
    
    try {
        $stmt = $pdo->query("DESCRIBE artists");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $critical_columns = ['platforms_data', 'total_followers_all_platforms', 'avg_popularity_all_platforms'];
        $missing_columns = array_diff($critical_columns, $columns);
        
        if (empty($missing_columns)) {
            echo "   ✅ Todas las columnas críticas están presentes en 'artists'\n";
        } else {
            echo "   ❌ Columnas faltantes en 'artists': " . implode(', ', $missing_columns) . "\n";
        }
    } catch (PDOException $e) {
        echo "   ⚠️  Error verificando columnas: {$e->getMessage()}\n";
    }

    // Resumen final
    echo "\n🎯 RESUMEN FINAL:\n";
    echo "================\n";
    echo "✅ Statements ejecutados: {$success_count}\n";
    echo "⚠️  Errores (ignorados): {$error_count}\n";
    echo "👥 Usuarios conservados: " . ($existing_users ? 'SÍ' : 'NO') . "\n";
    echo "🗄️  Base de datos: {$db_config['database']}\n";
    echo "📅 Actualización: " . date('Y-m-d H:i:s') . "\n";
    
    echo "\n🚀 ¡Base de datos actualizada correctamente!\n";
    echo "\n📝 PRÓXIMOS PASOS:\n";
    echo "1. Verificar que la aplicación funcione correctamente\n";
    echo "2. Probar la creación de nuevos seguimientos\n";
    echo "3. Verificar que los datos existentes se mantengan\n";
    echo "4. Eliminar este archivo por seguridad\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR CRÍTICO:\n";
    echo "================\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
    
    echo "\n🔧 SOLUCIONES POSIBLES:\n";
    echo "1. Verificar credenciales de base de datos en .env\n";
    echo "2. Asegurar que MySQL esté ejecutándose\n";
    echo "3. Verificar permisos de usuario de base de datos\n";
    echo "4. Revisar configuración de red/firewall\n";
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Error: {$e->getMessage()}\n";
}

echo '</pre>';

// Información de seguridad
echo '<hr>';
echo '<p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de ejecutarlo por seguridad.</p>';
echo '<p>Para volver a ejecutar, usa: <code>?key=' . $setup_key . '</code></p>';
?>
