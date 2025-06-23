<?php
/**
 * TrackTraster Configuration
 * Configuración que usa variables de entorno (.env)
 */

// Cargar variables de entorno
require_once APPPATH . 'libraries/EnvLoader.php';
EnvLoader::load();

/**
 * Database Configuration
 */
$config['database'] = array (
  'hostname' => EnvLoader::get('DB_HOSTNAME', 'localhost'),
  'username' => EnvLoader::get('DB_USERNAME', 'root'),
  'password' => EnvLoader::get('DB_PASSWORD', ''),
  'database' => EnvLoader::get('DB_DATABASE', 'tracktraster_db'),
  'dbdriver' => EnvLoader::get('DB_DRIVER', 'mysqli'),
  'charset' => EnvLoader::get('DB_CHARSET', 'utf8mb4'),
  'collation' => EnvLoader::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
);

/**
 * Application Configuration
 */
$config['app'] = array (
  'base_url' => EnvLoader::get('APP_BASE_URL', 'https://tracktraster.3mas1r.com/'),
  'index_page' => EnvLoader::get('APP_INDEX_PAGE', ''),
  'session_expiration' => EnvLoader::get('APP_SESSION_EXPIRATION', 7200),
  'remember_me_expiration' => EnvLoader::get('APP_REMEMBER_ME_EXPIRATION', 2592000),
  'encryption_key' => EnvLoader::get('APP_ENCRYPTION_KEY', 'your-32-character-secret-key-here!'),
  'csrf_protection' => EnvLoader::get('APP_CSRF_PROTECTION', true),
  'csrf_token_name' => EnvLoader::get('APP_CSRF_TOKEN_NAME', 'csrf_token'),
  'csrf_cookie_name' => EnvLoader::get('APP_CSRF_COOKIE_NAME', 'csrf_cookie'),
);

/**
 * Spotify API Configuration
 */
$config['spotify'] = array (
  'client_id' => EnvLoader::get('SPOTIFY_CLIENT_ID', ''),
  'client_secret' => EnvLoader::get('SPOTIFY_CLIENT_SECRET', ''),
  'redirect_uri' => EnvLoader::get('SPOTIFY_REDIRECT_URI', 'https://tracktraster.3mas1r.com/auth/spotify/callback'),
  'enabled' => EnvLoader::get('SPOTIFY_ENABLED', false),
);

/**
 * Deezer API Configuration
 */
$config['deezer'] = array (
  'enabled' => EnvLoader::get('DEEZER_ENABLED', true),
);

/**
 * YouTube Music API Configuration
 */
$config['youtube_music'] = array (
  'api_key' => EnvLoader::get('YOUTUBE_MUSIC_API_KEY', ''),
  'enabled' => EnvLoader::get('YOUTUBE_MUSIC_ENABLED', false),
);

/**
 * Last.fm API Configuration
 */
$config['lastfm'] = array (
  'api_key' => EnvLoader::get('LASTFM_API_KEY', ''),
  'api_secret' => EnvLoader::get('LASTFM_API_SECRET', ''),
  'enabled' => EnvLoader::get('LASTFM_ENABLED', false),
);

/**
 * SoundCloud API Configuration
 */
$config['soundcloud'] = array (
  'client_id' => EnvLoader::get('SOUNDCLOUD_CLIENT_ID', ''),
  'enabled' => EnvLoader::get('SOUNDCLOUD_ENABLED', false),
);

/**
 * Music Platforms Unified Configuration
 */
$config['music_platforms'] = array (
  'spotify' => array (
    'client_id' => EnvLoader::get('SPOTIFY_CLIENT_ID', ''),
    'client_secret' => EnvLoader::get('SPOTIFY_CLIENT_SECRET', ''),
    'enabled' => EnvLoader::get('SPOTIFY_ENABLED', false),
  ),
  'deezer' => array (
    'enabled' => EnvLoader::get('DEEZER_ENABLED', true),
  ),
  'lastfm' => array (
    'api_key' => EnvLoader::get('LASTFM_API_KEY', ''),
    'api_secret' => EnvLoader::get('LASTFM_API_SECRET', ''),
    'enabled' => EnvLoader::get('LASTFM_ENABLED', false),
  ),
  'soundcloud' => array (
    'client_id' => EnvLoader::get('SOUNDCLOUD_CLIENT_ID', ''),
    'enabled' => EnvLoader::get('SOUNDCLOUD_ENABLED', false),
  ),
);

/**
 * LATAM Countries Configuration
 */
$config['countries'] = array (
  'AR' => 'Argentina',
  'BO' => 'Bolivia',
  'BR' => 'Brasil',
  'CL' => 'Chile',
  'CO' => 'Colombia',
  'CR' => 'Costa Rica',
  'CU' => 'Cuba',
  'DO' => 'República Dominicana',
  'EC' => 'Ecuador',
  'SV' => 'El Salvador',
  'GT' => 'Guatemala',
  'HN' => 'Honduras',
  'MX' => 'México',
  'NI' => 'Nicaragua',
  'PA' => 'Panamá',
  'PY' => 'Paraguay',
  'PE' => 'Perú',
  'PR' => 'Puerto Rico',
  'UY' => 'Uruguay',
  'VE' => 'Venezuela',
);

return $config;
