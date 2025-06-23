<?php
/**
 * Artists Controller - Gestión de artistas con integración multi-plataforma
 */

class Artists extends BaseController
{
    private $musicPlatforms;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
        
        // Inicializar gestor de plataformas musicales
        require_once APPPATH . 'libraries/MusicPlatformManager.php';
        $this->musicPlatforms = new MusicPlatformManager($config);
    }

    public function index()
    {
        $user = $this->session->getUser();
        
        // Get user's tracked artists
        $artists = [];
        try {
            $artists = $this->db->fetchAll(
                "SELECT a.*, at.country_code, at.event_name, at.event_date, at.status, at.created_at as tracking_started
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE at.user_id = ? 
                 ORDER BY at.created_at DESC",
                [$user['id']]
            );
        } catch (Exception $e) {
            $artists = [];
        }

        $data = [
            'title' => 'Mis Artistas - TrackTraster',
            'page_title' => 'Mis Artistas',
            'active_menu' => 'artists',
            'user' => $user,
            'artists' => $artists,
            'countries' => $this->config['countries']
        ];

        $this->loadView('artists/index', $data);
    }

    public function search()
    {
        $user = $this->session->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processSearch();
        } else {
            $this->showSearchForm();
        }
    }    private function showSearchForm()
    {
        $user = $this->session->getUser();
        
        $data = [
            'title' => 'Buscar Artista - TrackTraster',
            'page_title' => 'Buscar Artista',
            'active_menu' => 'artists',
            'user' => $user,
            'csrf_token' => $this->generateCSRFToken(),
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success'),
            'countries' => $this->config['countries'],
            'available_platforms' => $this->musicPlatforms->getAvailablePlatforms()
        ];

        $this->loadView('artists/search', $data);
    }private function processSearch()
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('artists/search');
        }        $artistName = trim($_POST['artist_name'] ?? '');
        $selectedPlatforms = $_POST['platforms'] ?? ['all'];
        
        if (empty($artistName)) {
            $this->session->setFlash('error', 'El nombre del artista es requerido');
            $this->redirect('artists/search');
        }

        try {
            // Buscar en plataformas seleccionadas o todas si se eligió "all"
            $searchResults = $this->musicPlatforms->searchArtistsInPlatforms($artistName, $selectedPlatforms, 20);
            
            $data = [
                'title' => 'Resultados de Búsqueda - TrackTraster',
                'page_title' => 'Resultados de Búsqueda',
                'active_menu' => 'artists',
                'user' => $this->session->getUser(),
                'search_term' => $artistName,
                'selected_platforms' => $selectedPlatforms,
                'results' => $searchResults,
                'available_platforms' => $this->musicPlatforms->getAvailablePlatforms(),
                'csrf_token' => $this->generateCSRFToken()
            ];

            $this->loadView('artists/search-results', $data);
            
        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error en la búsqueda: ' . $e->getMessage());
            $this->redirect('artists/search');
        }
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('artists/search');
        }

        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('artists/search');
        }        $artistData = [
            'name' => trim($_POST['artist_name'] ?? ''),
            'platforms_data' => $_POST['platforms_data'] ?? '[]', // JSON con datos de todas las plataformas
            'image_url' => $_POST['image_url'] ?? null,
            'primary_platform' => $_POST['primary_platform'] ?? 'spotify',
            'popularity' => intval($_POST['popularity'] ?? 0),
            'followers_total' => intval($_POST['followers'] ?? 0),
            'genres' => json_encode($_POST['genres'] ?? [])
        ];

        if (empty($artistData['name'])) {
            $this->session->setFlash('error', 'El nombre del artista es requerido');
            $this->redirect('artists/search');
        }        try {
            // Decodificar datos de plataformas
            $platformsData = json_decode($artistData['platforms_data'], true) ?? [];
            
            // Check if artist already exists por nombre (método simple)
            $existingArtist = $this->db->fetchOne(
                "SELECT id FROM artists WHERE LOWER(name) = LOWER(?)",
                [$artistData['name']]
            );

            $artistId = null;
            if ($existingArtist) {
                $artistId = $existingArtist['id'];
                
                // Actualizar datos del artista existente
                $this->db->execute(
                    "UPDATE artists SET 
                     platforms_data = ?, image_url = ?, popularity = ?, 
                     followers_total = ?, genres = ?, updated_at = NOW()
                     WHERE id = ?",
                    [$artistData['platforms_data'], $artistData['image_url'], 
                     $artistData['popularity'], $artistData['followers_total'], 
                     $artistData['genres'], $artistId]
                );
                
            } else {
                // Create new artist
                $result = $this->db->execute(
                    "INSERT INTO artists (name, platforms_data, image_url, popularity, followers_total, genres, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$artistData['name'], $artistData['platforms_data'], $artistData['image_url'], 
                     $artistData['popularity'], $artistData['followers_total'], $artistData['genres']]
                );
                $artistId = $result['insert_id'];
            }

            // Redirect to create tracking
            $this->redirect('trackings/create?artist_id=' . $artistId);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al agregar el artista: ' . $e->getMessage());
            $this->redirect('artists/search');
        }
    }    public function view($artistId = null)
    {
        if (!$artistId) {
            $this->redirect('artists');
        }

        $user = $this->session->getUser();
        
        try {
            // Get artist with tracking info for this user
            $artist = $this->db->fetchOne(
                "SELECT a.*, at.country_code, at.event_name, at.event_date, at.status, at.notes,
                        at.tracking_start_date, at.created_at as tracking_started
                 FROM artists a 
                 LEFT JOIN artist_trackings at ON a.id = at.artist_id AND at.user_id = ?
                 WHERE a.id = ?",
                [$user['id'], $artistId]
            );

            if (!$artist) {
                $this->session->setFlash('error', 'Artista no encontrado');
                $this->redirect('artists');
            }

            // Enriquecer con datos multi-plataforma
            $platformData = $this->enrichArtistWithPlatformData($artist);

            $data = [
                'title' => $artist['name'] . ' - TrackTraster',
                'page_title' => $artist['name'],
                'active_menu' => 'artists',
                'user' => $user,
                'artist' => $artist,
                'platform_data' => $platformData,
                'countries' => $this->config['countries']
            ];

            $this->loadView('artists/view', $data);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al cargar el artista');
            $this->redirect('artists');
        }
    }

    /**
     * Enriquecer artista con datos de múltiples plataformas
     */
    private function enrichArtistWithPlatformData($artist)
    {
        try {
            // Obtener métricas combinadas de todas las plataformas
            $metrics = $this->musicPlatforms->getArtistCombinedMetrics(
                $artist['name'], 
                $artist['country_code'] ?? null
            );

            return $metrics;

        } catch (Exception $e) {
            // Si falla la obtención de métricas, retornar estructura vacía
            error_log('Error enriching artist with platform data: ' . $e->getMessage());
            return [
                'total_followers' => 0,
                'avg_popularity' => 0,
                'platforms_data' => [],
                'platforms_count' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }
}