<?php
/**
 * Environment Configuration Helper
 * Carga variables de entorno desde archivo .env
 */

class EnvLoader
{
    private static $variables = [];
    private static $loaded = false;
    
    /**
     * Carga las variables de entorno desde el archivo .env
     */    public static function load($envPath = null)
    {
        if (self::$loaded) {
            return;
        }
        
        if ($envPath === null) {
            $envPath = dirname(dirname(dirname(__FILE__))) . '/.env';
        }
        
        if (!file_exists($envPath)) {
            throw new Exception("Archivo .env no encontrado en: {$envPath}");
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Separar clave=valor
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Convertir valores booleanos
                if (strtolower($value) === 'true') {
                    $value = true;
                } elseif (strtolower($value) === 'false') {
                    $value = false;
                } elseif (is_numeric($value)) {
                    $value = is_float($value) ? (float)$value : (int)$value;
                }
                
                self::$variables[$key] = $value;
                
                // También establecer en $_ENV si no existe
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obtiene una variable de entorno
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        // Buscar primero en variables de entorno del sistema
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Luego en variables cargadas del .env
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        return $default;
    }
    
    /**
     * Establece una variable de entorno
     */
    public static function set($key, $value)
    {
        self::$variables[$key] = $value;
        $_ENV[$key] = $value;
    }
    
    /**
     * Verifica si una variable existe
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset($_ENV[$key]) || isset(self::$variables[$key]);
    }
    
    /**
     * Obtiene todas las variables cargadas
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return array_merge(self::$variables, $_ENV);
    }
    
    /**
     * Guarda cambios en el archivo .env
     */    public static function save($envPath = null)
    {
        if ($envPath === null) {
            $envPath = dirname(dirname(dirname(__FILE__))) . '/.env';
        }
        
        $content = [];
        $content[] = "# TrackTraster Environment Configuration";
        $content[] = "# Configuración de variables de entorno para TrackTraster";
        $content[] = "# IMPORTANTE: Este archivo NO debe subirse a repositorios públicos";
        $content[] = "";
        
        // Agrupar variables por sección
        $sections = [
            'DATABASE' => [
                'DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE',
                'DB_DRIVER', 'DB_CHARSET', 'DB_COLLATION'
            ],
            'APPLICATION' => [
                'APP_BASE_URL', 'APP_INDEX_PAGE', 'APP_SESSION_EXPIRATION',
                'APP_REMEMBER_ME_EXPIRATION', 'APP_ENCRYPTION_KEY',
                'APP_CSRF_PROTECTION', 'APP_CSRF_TOKEN_NAME', 'APP_CSRF_COOKIE_NAME'
            ],
            'SPOTIFY API' => [
                'SPOTIFY_CLIENT_ID', 'SPOTIFY_CLIENT_SECRET', 'SPOTIFY_REDIRECT_URI', 'SPOTIFY_ENABLED'
            ],
            'DEEZER API' => [
                'DEEZER_ENABLED'
            ],
            'LAST.FM API' => [
                'LASTFM_API_KEY', 'LASTFM_API_SECRET', 'LASTFM_ENABLED'
            ],
            'SOUNDCLOUD API' => [
                'SOUNDCLOUD_CLIENT_ID', 'SOUNDCLOUD_ENABLED'
            ],
            'YOUTUBE MUSIC API' => [
                'YOUTUBE_MUSIC_API_KEY', 'YOUTUBE_MUSIC_ENABLED'
            ]
        ];
        
        foreach ($sections as $sectionName => $keys) {
            $content[] = "# " . str_repeat("=", 43);
            $content[] = "# {$sectionName} CONFIGURATION";
            $content[] = "# " . str_repeat("=", 43);
            
            foreach ($keys as $key) {
                $value = self::get($key, '');
                
                // Formatear valor para el archivo
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (is_string($value) && (strpos($value, ' ') !== false || empty($value))) {
                    $value = '"' . $value . '"';
                }
                
                $content[] = "{$key}={$value}";
            }
            $content[] = "";
        }
        
        return file_put_contents($envPath, implode("\n", $content));
    }
}
