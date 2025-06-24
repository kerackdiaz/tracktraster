<?php
/**
 * Trackings Controller - Gestión de seguimientos de artistas
 */

class Trackings extends BaseController
{
    protected $lifecycleService;
    
    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
        
        // Initialize tracking lifecycle service
        try {
            require_once APPPATH . 'services/TrackingLifecycleService.php';
            $this->lifecycleService = new TrackingLifecycleService($this->db);
            
            // Update tracking statuses on each request
            $this->lifecycleService->updateTrackingStatuses();
        } catch (Exception $e) {
            error_log("TrackingLifecycleService initialization error: " . $e->getMessage());
            $this->lifecycleService = null;
        }
    }public function index()
    {
        $user = $this->session->getUser();
          // Get all trackings for the user
        $trackings = [];
        try {
            $trackings = $this->db->fetchAll(
                "SELECT at.*, a.name as artist_name, a.image_url, a.popularity, 
                        DATEDIFF(COALESCE(at.event_date, NOW()), at.tracking_start_date) as tracking_days,
                        DATEDIFF(COALESCE(at.event_date, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), CURDATE()) as days_to_event,
                        at.tracking_status
                 FROM artist_trackings at 
                 JOIN artists a ON at.artist_id = a.id 
                 WHERE at.user_id = ? 
                 ORDER BY at.event_date ASC, at.created_at DESC",
                [$user['id']]
            );
            
            // Enriquecer con datos de múltiples plataformas y lifecycle
            $trackings = $this->enrichTrackingsWithPlatformData($trackings);
            
        } catch (Exception $e) {
            $trackings = [];
        }

        $data = [
            'title' => 'Seguimientos - TrackTraster',
            'page_title' => 'Seguimientos de Artistas',
            'active_menu' => 'trackings',
            'user' => $user,
            'trackings' => $trackings,
            'countries' => $this->config['countries'],
            'csrf_token' => $this->generateCSRFToken()
        ];

        $this->loadView('trackings/index', $data);
    }    /**
     * Enriquecer seguimientos con datos de múltiples plataformas y lifecycle info
     */
    private function enrichTrackingsWithPlatformData($trackings)
    {
        if (empty($trackings)) {
            return $trackings;
        }

        try {
            require_once APPPATH . 'libraries/MusicPlatformManager.php';
            $platformManager = new MusicPlatformManager($this->config);

            foreach ($trackings as &$tracking) {
                try {
                    // Obtener métricas combinadas de todas las plataformas
                    $metrics = $platformManager->getArtistCombinedMetrics(
                        $tracking['artist_name'], 
                        $tracking['country_code']
                    );

                    // Agregar métricas combinadas al tracking
                    $tracking['platform_metrics'] = $metrics;
                    $tracking['total_followers_all_platforms'] = $metrics['total_followers'];
                    $tracking['avg_popularity_all_platforms'] = $metrics['avg_popularity'];
                    $tracking['platforms_available'] = $metrics['platforms_count'];

                    // Obtener información del lifecycle si está disponible
                    if ($this->lifecycleService) {
                        $lifecycle = $this->lifecycleService->getTrackingLifecycle($tracking['id']);
                        if ($lifecycle) {
                            $tracking['lifecycle'] = $lifecycle;
                            $tracking['phase'] = $lifecycle['phase'];
                            $tracking['progress_percentage'] = $lifecycle['progress_percentage'];
                        }
                    }

                } catch (Exception $e) {
                    // Si falla la obtención de métricas, continuar sin ellas
                    $tracking['platform_metrics'] = null;
                    $tracking['total_followers_all_platforms'] = 0;
                    $tracking['avg_popularity_all_platforms'] = 0;
                    $tracking['platforms_available'] = 0;
                    $tracking['lifecycle'] = null;
                }
            }

        } catch (Exception $e) {
            // Si falla completamente, retornar los trackings sin enriquecer
            error_log('Error enriching trackings with platform data: ' . $e->getMessage());
        }

        return $trackings;
    }

    public function create()
    {
        $user = $this->session->getUser();
        $artistId = $_GET['artist_id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            $this->showCreateForm($artistId);
        }
    }

    private function showCreateForm($artistId = null)
    {
        $user = $this->session->getUser();
        $artist = null;
        
        if ($artistId) {
            try {
                $artist = $this->db->fetchOne(
                    "SELECT * FROM artists WHERE id = ?",
                    [$artistId]
                );
                
                if (!$artist) {
                    $this->session->setFlash('error', 'Artista no encontrado');
                    $this->redirect('artists');
                }
                
                // Check if tracking already exists
                $existingTracking = $this->db->fetchOne(
                    "SELECT id FROM artist_trackings WHERE artist_id = ? AND user_id = ?",
                    [$artistId, $user['id']]
                );
                
                if ($existingTracking) {
                    $this->session->setFlash('error', 'Ya tienes un seguimiento activo para este artista');
                    $this->redirect('artists/view/' . $artistId);
                }
                
            } catch (Exception $e) {
                $this->session->setFlash('error', 'Error al cargar el artista');
                $this->redirect('artists');
            }
        }
        
        $data = [
            'title' => 'Crear Seguimiento - TrackTraster',
            'page_title' => 'Crear Nuevo Seguimiento',
            'active_menu' => 'trackings',
            'user' => $user,
            'artist' => $artist,
            'csrf_token' => $this->generateCSRFToken(),
            'countries' => $this->config['countries'],
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success')
        ];

        $this->loadView('trackings/create', $data);
    }

    private function processCreate()
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('trackings/create');
        }

        $user = $this->session->getUser();
        
        $trackingData = [
            'artist_id' => intval($_POST['artist_id'] ?? 0),
            'country_code' => $_POST['country_code'] ?? '',
            'event_name' => trim($_POST['event_name'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'tracking_start_date' => $_POST['tracking_start_date'] ?? date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? ''),
            'status' => 'active'
        ];

        // Validation
        if (empty($trackingData['artist_id'])) {
            $this->session->setFlash('error', 'Debe seleccionar un artista');
            $this->redirect('trackings/create');
        }

        if (empty($trackingData['country_code'])) {
            $this->session->setFlash('error', 'Debe seleccionar un país');
            $this->redirect('trackings/create');
        }

        try {
            // Verify artist exists
            $artist = $this->db->fetchOne(
                "SELECT id, name FROM artists WHERE id = ?",
                [$trackingData['artist_id']]
            );
            
            if (!$artist) {
                $this->session->setFlash('error', 'Artista no encontrado');
                $this->redirect('artists');
            }

            // Check for existing tracking
            $existingTracking = $this->db->fetchOne(
                "SELECT id FROM artist_trackings WHERE artist_id = ? AND user_id = ?",
                [$trackingData['artist_id'], $user['id']]
            );

            if ($existingTracking) {
                $this->session->setFlash('error', 'Ya tienes un seguimiento activo para este artista');
                $this->redirect('artists/view/' . $trackingData['artist_id']);
            }            // Create tracking
            $result = $this->db->execute(
                "INSERT INTO artist_trackings (user_id, artist_id, country_code, event_name, event_date, tracking_start_date, notes, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$user['id'], $trackingData['artist_id'], $trackingData['country_code'], 
                 $trackingData['event_name'], $trackingData['event_date'], 
                 $trackingData['tracking_start_date'], $trackingData['notes'], $trackingData['status']]
            );

            // Get the tracking ID and create initial metrics
            $trackingId = $result['insert_id'];
            if ($trackingId && $trackingData['status'] === 'active') {
                try {
                    require_once APPPATH . 'services/AnalyticsService.php';
                    $analyticsService = new AnalyticsService($this->db, $this->config);
                    $analyticsService->createInitialMetrics($trackingId);
                    error_log("Initial metrics created for tracking ID: $trackingId");
                } catch (Exception $e) {
                    error_log("Error creating initial metrics for tracking $trackingId: " . $e->getMessage());
                    // No interrumpir el flujo si falla la creación de métricas
                }
            }            $this->session->setFlash('success', 'Seguimiento creado exitosamente para ' . $artist['name']);
            
            // Redirigir a Analytics con el artista seleccionado para ver los datos inmediatamente
            $this->redirect('analytics?artist_id=' . $trackingData['artist_id']);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al crear el seguimiento: ' . $e->getMessage());
            $this->redirect('trackings/create' . ($trackingData['artist_id'] ? '?artist_id=' . $trackingData['artist_id'] : ''));
        }
    }

    public function edit($trackingId = null)
    {
        if (!$trackingId) {
            $this->redirect('trackings');
        }

        $user = $this->session->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($trackingId);
        } else {
            $this->showEditForm($trackingId);
        }
    }

    private function showEditForm($trackingId)
    {
        $user = $this->session->getUser();
        
        try {
            // Get tracking with artist info
            $tracking = $this->db->fetchOne(
                "SELECT at.*, a.name as artist_name, a.image_url 
                 FROM artist_trackings at 
                 JOIN artists a ON at.artist_id = a.id 
                 WHERE at.id = ? AND at.user_id = ?",
                [$trackingId, $user['id']]
            );

            if (!$tracking) {
                $this->session->setFlash('error', 'Seguimiento no encontrado');
                $this->redirect('trackings');
            }

            $data = [
                'title' => 'Editar Seguimiento - TrackTraster',
                'page_title' => 'Editar Seguimiento: ' . $tracking['artist_name'],
                'active_menu' => 'trackings',
                'user' => $user,
                'tracking' => $tracking,
                'csrf_token' => $this->generateCSRFToken(),
                'countries' => $this->config['countries'],
                'error' => $this->session->getFlash('error'),
                'success' => $this->session->getFlash('success')
            ];

            $this->loadView('trackings/edit', $data);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al cargar el seguimiento');
            $this->redirect('trackings');
        }
    }

    private function processEdit($trackingId)
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('trackings/edit/' . $trackingId);
        }

        $user = $this->session->getUser();
        
        $updateData = [
            'country_code' => $_POST['country_code'] ?? '',
            'event_name' => trim($_POST['event_name'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'tracking_start_date' => $_POST['tracking_start_date'] ?? null,
            'notes' => trim($_POST['notes'] ?? ''),
            'status' => $_POST['status'] ?? 'active'
        ];

        // Validation
        if (empty($updateData['country_code'])) {
            $this->session->setFlash('error', 'Debe seleccionar un país');
            $this->redirect('trackings/edit/' . $trackingId);
        }

        try {
            // Verify tracking belongs to user
            $tracking = $this->db->fetchOne(
                "SELECT at.*, a.name as artist_name 
                 FROM artist_trackings at 
                 JOIN artists a ON at.artist_id = a.id 
                 WHERE at.id = ? AND at.user_id = ?",
                [$trackingId, $user['id']]
            );

            if (!$tracking) {
                $this->session->setFlash('error', 'Seguimiento no encontrado');
                $this->redirect('trackings');
            }

            // Update tracking
            $this->db->execute(
                "UPDATE artist_trackings 
                 SET country_code = ?, event_name = ?, event_date = ?, tracking_start_date = ?, 
                     notes = ?, status = ?, updated_at = NOW() 
                 WHERE id = ? AND user_id = ?",
                [$updateData['country_code'], $updateData['event_name'], $updateData['event_date'],
                 $updateData['tracking_start_date'], $updateData['notes'], $updateData['status'],
                 $trackingId, $user['id']]
            );

            $this->session->setFlash('success', 'Seguimiento actualizado exitosamente');
            $this->redirect('artists/view/' . $tracking['artist_id']);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al actualizar el seguimiento: ' . $e->getMessage());
            $this->redirect('trackings/edit/' . $trackingId);
        }
    }

    public function delete($trackingId = null)
    {
        if (!$trackingId) {
            $this->redirect('trackings');
        }

        $user = $this->session->getUser();

        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('trackings');
        }

        try {
            // Verify tracking belongs to user
            $tracking = $this->db->fetchOne(
                "SELECT at.*, a.name as artist_name 
                 FROM artist_trackings at 
                 JOIN artists a ON at.artist_id = a.id 
                 WHERE at.id = ? AND at.user_id = ?",
                [$trackingId, $user['id']]
            );

            if (!$tracking) {
                $this->session->setFlash('error', 'Seguimiento no encontrado');
                $this->redirect('trackings');
            }

            // Delete tracking
            $this->db->execute(
                "DELETE FROM artist_trackings WHERE id = ? AND user_id = ?",
                [$trackingId, $user['id']]
            );

            $this->session->setFlash('success', 'Seguimiento eliminado exitosamente');
            $this->redirect('trackings');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al eliminar el seguimiento: ' . $e->getMessage());
            $this->redirect('trackings');
        }
    }

    /**
     * Endpoint para actualizar estados de tracking (cron job)
     */
    public function updateStatuses()
    {
        // Solo accesible desde cron o admin
        if (!$this->isValidCronRequest()) {
            http_response_code(403);
            die('Access denied');
        }

        if (!$this->lifecycleService) {
            echo "TrackingLifecycleService not available\n";
            return;
        }

        try {
            $this->lifecycleService->updateTrackingStatuses();
            echo "Tracking statuses updated successfully\n";
        } catch (Exception $e) {
            echo "Error updating tracking statuses: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Verificar si la solicitud es válida para cron
     */
    private function isValidCronRequest()
    {
        // Permitir acceso desde localhost
        if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
            return true;
        }
        
        // Permitir acceso con token válido
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $validToken = $this->config['cron_token'] ?? 'default_cron_token';
        
        return $token === $validToken;
    }

    // ...existing code...
}
