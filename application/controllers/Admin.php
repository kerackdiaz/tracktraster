<?php
/**
 * Admin Controller - Panel de administración simplificado
 */

class Admin extends BaseController
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
        
        // Verificar si el usuario es administrador
        $user = $this->session->getUser();
        if (!$this->isAdmin($user)) {
            $this->session->setFlash('error', 'Acceso denegado. Se requieren permisos de administrador.');
            $this->redirect('dashboard');
        }
    }

    private function isAdmin($user)
    {
        // Por simplicidad, verificar por email o agregar campo 'role' a la BD
        $adminEmails = ['admin@tracktraster.com', 'admin@localhost'];
        return in_array($user['email'] ?? '', $adminEmails) || ($user['role'] ?? '') === 'admin';
    }

    public function index()
    {
        $user = $this->session->getUser();
        
        $data = [
            'title' => 'Panel de Administración - TrackTraster',
            'page_title' => 'Panel de Administración',
            'active_menu' => 'admin',
            'user' => $user,
            'csrf_token' => $this->generateCSRFToken()
        ];

        $this->loadView('admin/index', $data);
    }

    public function api_status()
    {
        $user = $this->session->getUser();
        
        // Obtener estado actual de las APIs
        $apiStatus = $this->getApiStatus();
        $testResults = $this->runAutomaticTests();
        
        $data = [
            'title' => 'Estado de APIs - TrackTraster',
            'page_title' => 'Estado del Sistema y APIs',
            'active_menu' => 'admin',
            'user' => $user,
            'api_status' => $apiStatus,
            'test_results' => $testResults,
            'last_check' => date('d/m/Y H:i:s'),
            'csrf_token' => $this->generateCSRFToken()
        ];

        $this->loadView('admin/api_status', $data);
    }

    private function getApiStatus()
    {
        // Cargar variables de entorno
        require_once APPPATH . 'libraries/EnvLoader.php';
        EnvLoader::load();
        
        return [
            'spotify' => [
                'name' => 'Spotify',
                'enabled' => EnvLoader::get('SPOTIFY_ENABLED', false),
                'configured' => !empty(EnvLoader::get('SPOTIFY_CLIENT_ID', '')) && !empty(EnvLoader::get('SPOTIFY_CLIENT_SECRET', '')),
                'icon' => 'fab fa-spotify',
                'color' => 'success'
            ],
            'deezer' => [
                'name' => 'Deezer',
                'enabled' => EnvLoader::get('DEEZER_ENABLED', true),
                'configured' => true, // No requiere configuración
                'icon' => 'fas fa-music',
                'color' => 'warning'
            ],
            'lastfm' => [
                'name' => 'Last.fm',
                'enabled' => EnvLoader::get('LASTFM_ENABLED', false),
                'configured' => !empty(EnvLoader::get('LASTFM_API_KEY', '')) && !empty(EnvLoader::get('LASTFM_API_SECRET', '')),
                'icon' => 'fab fa-lastfm',
                'color' => 'danger'
            ],
            'soundcloud' => [
                'name' => 'SoundCloud',
                'enabled' => EnvLoader::get('SOUNDCLOUD_ENABLED', false),
                'configured' => !empty(EnvLoader::get('SOUNDCLOUD_CLIENT_ID', '')),
                'icon' => 'fab fa-soundcloud',
                'color' => 'primary'
            ],
            'youtube_music' => [
                'name' => 'YouTube Music',
                'enabled' => EnvLoader::get('YOUTUBE_MUSIC_ENABLED', false),
                'configured' => !empty(EnvLoader::get('YOUTUBE_MUSIC_API_KEY', '')),
                'icon' => 'fab fa-youtube',
                'color' => 'danger'
            ]
        ];
    }    private function runAutomaticTests()
    {
        $results = [];
        
        try {
            require_once APPPATH . 'libraries/MusicPlatformManager.php';
            
            // Preparar configuración específica para las APIs
            $apiConfig = $this->buildApiConfig();
            $manager = new MusicPlatformManager($apiConfig);
            
            // Probar búsqueda de un artista conocido
            $testQuery = 'Bad Bunny';
            $searchResults = $manager->searchArtists($testQuery, 'all', 3);
            
            foreach ($searchResults['platforms'] as $platform => $result) {
                $results[$platform] = [
                    'name' => $this->getPlatformDisplayName($platform),
                    'status' => $result['status'],
                    'count' => $result['count'] ?? 0,
                    'error' => $result['error'] ?? null,
                    'response_time' => $result['response_time'] ?? null,
                    'test_query' => $testQuery
                ];
            }
            
        } catch (Exception $e) {
            $results['error'] = 'Error en las pruebas automáticas: ' . $e->getMessage();
        }
        
        return $results;
    }

    private function getPlatformDisplayName($platform)
    {
        $names = [
            'spotify' => 'Spotify',
            'deezer' => 'Deezer',
            'lastfm' => 'Last.fm',
            'soundcloud' => 'SoundCloud',
            'youtube_music' => 'YouTube Music'
        ];
        
        return $names[$platform] ?? ucfirst($platform);
    }

    public function refresh_tests()
    {
        // AJAX endpoint para refrescar pruebas automáticamente
        header('Content-Type: application/json');
        
        $testResults = $this->runAutomaticTests();
        $apiStatus = $this->getApiStatus();
        
        echo json_encode([
            'success' => true,
            'test_results' => $testResults,
            'api_status' => $apiStatus,
            'last_check' => date('d/m/Y H:i:s')
        ]);
        exit;
    }

    public function system_info()
    {
        $user = $this->session->getUser();
        
        // Información del sistema
        $systemInfo = [
            'php_version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'env_file_exists' => file_exists(dirname(dirname(dirname(__FILE__))) . '/.env'),
            'env_writable' => is_writable(dirname(dirname(dirname(__FILE__))) . '/.env'),
            'config_writable' => is_writable(APPPATH . 'config/'),
            'libraries_loaded' => [
                'EnvLoader' => class_exists('EnvLoader', false),
                'MusicPlatformManager' => class_exists('MusicPlatformManager', false)
            ]
        ];
        
        $data = [
            'title' => 'Información del Sistema - TrackTraster',
            'page_title' => 'Información del Sistema',
            'active_menu' => 'admin',
            'user' => $user,
            'system_info' => $systemInfo,
            'csrf_token' => $this->generateCSRFToken()
        ];        $this->loadView('admin/system_info', $data);
    }

    private function buildApiConfig()
    {
        // Cargar variables de entorno
        require_once APPPATH . 'libraries/EnvLoader.php';
        EnvLoader::load();
        
        return [
            'spotify' => [
                'client_id' => EnvLoader::get('SPOTIFY_CLIENT_ID', ''),
                'client_secret' => EnvLoader::get('SPOTIFY_CLIENT_SECRET', ''),
                'redirect_uri' => EnvLoader::get('SPOTIFY_REDIRECT_URI', ''),
                'enabled' => EnvLoader::get('SPOTIFY_ENABLED', false)
            ],
            'deezer' => [
                'enabled' => EnvLoader::get('DEEZER_ENABLED', true)
            ],
            'lastfm' => [
                'api_key' => EnvLoader::get('LASTFM_API_KEY', ''),
                'api_secret' => EnvLoader::get('LASTFM_API_SECRET', ''),
                'enabled' => EnvLoader::get('LASTFM_ENABLED', false)
            ],
            'soundcloud' => [
                'client_id' => EnvLoader::get('SOUNDCLOUD_CLIENT_ID', ''),
                'enabled' => EnvLoader::get('SOUNDCLOUD_ENABLED', false)
            ],
            'youtube_music' => [
                'api_key' => EnvLoader::get('YOUTUBE_MUSIC_API_KEY', ''),
                'enabled' => EnvLoader::get('YOUTUBE_MUSIC_ENABLED', false)
            ]
        ];
    }
}
