<?php
/**
 * Environment Configuration Loader
 * Helper para cargar configuración en scripts de diagnóstico
 */

class EnvConfigLoader
{
    public static function loadConfig()
    {
        // Cargar variables de entorno desde .env
        $envFile = __DIR__ . '/.env';
        $config = [];
        
        if (file_exists($envFile)) {
            $envVars = parse_ini_file($envFile);
            foreach ($envVars as $key => $value) {
                $_ENV[$key] = $value;
            }
            
            // Configuración de base de datos
            $config['db'] = [
                'hostname' => $_ENV['DB_HOSTNAME'] ?? 'localhost',
                'database' => $_ENV['DB_DATABASE'] ?? 'tracktraster_db',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'driver' => $_ENV['DB_DRIVER'] ?? 'mysqli',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ];
            
            // Configuración de aplicación
            $config['app'] = [
                'base_url' => $_ENV['APP_BASE_URL'] ?? 'http://localhost/',
                'index_page' => $_ENV['APP_INDEX_PAGE'] ?? '',
                'encryption_key' => $_ENV['APP_ENCRYPTION_KEY'] ?? '',
                'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC'
            ];
            
            // Configuración de APIs
            $config['spotify'] = [
                'client_id' => $_ENV['SPOTIFY_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['SPOTIFY_CLIENT_SECRET'] ?? '',
                'redirect_uri' => $_ENV['SPOTIFY_REDIRECT_URI'] ?? ''
            ];
            
            $config['lastfm'] = [
                'api_key' => $_ENV['LASTFM_API_KEY'] ?? '',
                'api_secret' => $_ENV['LASTFM_API_SECRET'] ?? ''
            ];
            
            $config['deezer'] = [
                'app_id' => $_ENV['DEEZER_APP_ID'] ?? '',
                'secret' => $_ENV['DEEZER_SECRET'] ?? ''
            ];
            
        } else {
            throw new Exception('.env file not found');
        }
        
        return $config;
    }
    
    public static function getDatabaseConnection()
    {
        $config = self::loadConfig();
        $db = $config['db'];
        
        $dsn = "mysql:host={$db['hostname']};dbname={$db['database']};charset={$db['charset']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
}
?>
