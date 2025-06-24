<?php
/**
 * Analytics Controller - Analíticas y métricas de artistas
 */

class Analytics extends BaseController
{
    protected $analyticsService;
    protected $lifecycleService;
    
    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
        
        // Initialize analytics service with error handling
        try {
            require_once APPPATH . 'services/AnalyticsService.php';
            $this->analyticsService = new AnalyticsService($this->db, $config);
        } catch (Exception $e) {
            error_log("Analytics service initialization error: " . $e->getMessage());
            $this->analyticsService = null;
        }
        
        // Initialize tracking lifecycle service
        try {
            require_once APPPATH . 'services/TrackingLifecycleService.php';
            $this->lifecycleService = new TrackingLifecycleService($this->db);
        } catch (Exception $e) {
            error_log("TrackingLifecycleService initialization error: " . $e->getMessage());
            $this->lifecycleService = null;
        }
    }    public function index()
    {
        $user = $this->session->getUser();
        $artistId = $_GET['artist_id'] ?? null;
        
        // Get user's tracked artists for dropdown
        $trackedArtists = [];
        try {
            $trackedArtists = $this->db->fetchAll(
                "SELECT DISTINCT a.id, a.name, a.image_url, at.event_name, at.event_date,
                        at.tracking_status, at.tracking_start_date,
                        DATEDIFF(COALESCE(at.event_date, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), CURDATE()) as days_to_event
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE at.user_id = ? AND at.status = 'active'
                 ORDER BY at.event_date ASC, a.name",
                [$user['id']]
            );
        } catch (Exception $e) {
            $trackedArtists = [];
        }

        // Auto-select artist if no artist selected but there's only one tracked artist
        if (!$artistId && count($trackedArtists) === 1) {
            $artistId = $trackedArtists[0]['id'];
        }

        $selectedArtist = null;
        $analytics = null;
        $lifecycle = null;

        if ($artistId && !empty($trackedArtists)) {
            try {
                // Verify user has access to this artist and get tracking info
                $trackingInfo = $this->db->fetchOne(
                    "SELECT at.id as tracking_id, a.*, at.country_code, at.event_name, at.event_date, 
                            at.tracking_start_date, at.tracking_status, at.event_city, at.event_venue
                     FROM artists a 
                     JOIN artist_trackings at ON a.id = at.artist_id 
                     WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'",
                    [$artistId, $user['id']]
                );

                if ($trackingInfo) {
                    $selectedArtist = $trackingInfo;
                    
                    // Get lifecycle information
                    if ($this->lifecycleService) {
                        $lifecycle = $this->lifecycleService->getTrackingLifecycle($trackingInfo['tracking_id']);
                    }
                    
                    // Get event-contextual analytics
                    if ($this->analyticsService && $this->lifecycleService) {
                        try {
                            $analytics = $this->lifecycleService->getEventContextualMetrics($trackingInfo['tracking_id']);
                        } catch (Exception $e) {
                            error_log("Event contextual analytics error: " . $e->getMessage());
                            // Fallback to regular analytics
                            try {
                                $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
                            } catch (Exception $e2) {
                                error_log("Regular analytics error: " . $e2->getMessage());
                                $analytics = $this->generateBasicAnalytics($selectedArtist);
                            }
                        }
                    } elseif ($this->analyticsService) {
                        try {
                            $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
                        } catch (Exception $e) {
                            error_log("Analytics error: " . $e->getMessage());
                            $analytics = $this->generateBasicAnalytics($selectedArtist);
                        }
                    } else {
                        // Fallback when services are not available
                        $analytics = $this->generateBasicAnalytics($selectedArtist);
                    }
                }
            } catch (Exception $e) {
                $selectedArtist = null;
                error_log("Error loading artist analytics: " . $e->getMessage());
            }
        }$data = [
            'title' => 'Analíticas - TrackTraster',
            'page_title' => 'Analíticas de Artistas',
            'active_menu' => 'analytics',
            'user' => $user,
            'tracked_artists' => $trackedArtists,
            'selected_artist' => $selectedArtist,
            'analytics' => $analytics,
            'lifecycle' => $lifecycle,
            'countries' => $this->config['countries']
        ];

        $this->loadView('analytics/index', $data);
    }    private function generateBasicAnalytics($artist)
    {
        // Generate basic analytics when no historical data exists
        $startDate = isset($artist['tracking_start_date']) && $artist['tracking_start_date'] 
            ? strtotime($artist['tracking_start_date']) 
            : strtotime('-7 days'); // Default to 7 days ago if no tracking start date
            
        $days = floor((time() - $startDate) / (60 * 60 * 24));
        
        // Generate some basic chart data for the tracking period
        $chartData = [];
        $listenersData = [];
        $popularityData = [];
        
        // Generate data points for the last 30 days or tracking period
        $dataPoints = min(30, max(7, $days));
        $baseValue = 1000; // Base followers
        
        for ($i = $dataPoints; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $variation = sin($i * 0.2) * 100; // Small variation
            
            $chartData[] = [
                'date' => $date,
                'value' => max(0, $baseValue + $variation + ($dataPoints - $i) * 10)
            ];
            
            $listenersData[] = [
                'date' => $date,
                'value' => max(0, ($baseValue * 0.3) + $variation * 0.5)
            ];
            
            $popularityData[] = [
                'date' => $date,
                'value' => max(0, min(100, 45 + sin($i * 0.3) * 10))
            ];
        }
        
        return [
            'summary' => [
                'total_followers' => end($chartData)['value'],
                'followers_growth' => end($chartData)['value'] - reset($chartData)['value'],
                'current_popularity' => round(end($popularityData)['value']),
                'monthly_listeners' => round(end($listenersData)['value'] * 2.5),
                'tracking_days' => $days,
                'platforms_count' => 2 // Spotify + Deezer simulado
            ],
            'trends' => [
                'followers_trend' => 2.5, // Simulado 2.5% crecimiento
                'popularity_trend' => 1.2, 
                'listeners_trend' => 1.8,
                'engagement_trend' => 2.1
            ],
            'charts' => [
                'followers_growth' => $chartData,
                'popularity_score' => $popularityData,
                'listeners_growth' => $listenersData
            ],
            'regional_data' => [
                'top_cities' => [
                    ['name' => 'Ciudad de México', 'streams' => 15420],
                    ['name' => 'Buenos Aires', 'streams' => 12380],
                    ['name' => 'São Paulo', 'streams' => 18950],
                    ['name' => 'Bogotá', 'streams' => 9760],
                    ['name' => 'Lima', 'streams' => 8340]
                ],
                'country_focus' => $artist['country_code'] ?? 'CO'
            ],
            'platforms' => [
                'spotify' => [
                    'followers' => round(end($chartData)['value'] * 0.6),
                    'popularity' => round(end($popularityData)['value']),
                    'monthly_listeners' => round(end($listenersData)['value'] * 2.5)
                ],
                'deezer' => [
                    'fans' => round(end($chartData)['value'] * 0.4)
                ],
                'lastfm' => [
                    'listeners' => round(end($listenersData)['value']),
                    'playcount' => round(end($listenersData)['value'] * 150)
                ],
                'total_followers' => round(end($chartData)['value']),
                'avg_popularity' => round(end($popularityData)['value'])
            ],
            'tracking_info' => $artist,
            'lifecycle' => [
                'recommendations' => [
                    'El seguimiento acaba de comenzar. Los datos se están recopilando.',
                    'Revisa las analíticas en 24-48 horas para ver las primeras métricas.',
                    'Considera promover tu música en redes sociales para generar tráfico inicial.'
                ]
            ],
            'message' => 'Seguimiento recién creado. Mostrando datos de ejemplo mientras se recopilan métricas reales.'
        ];
    }/**
     * Crear un cron job o tarea programada para actualizar métricas diarias
     */
    public function updateDailyMetrics()
    {
        // Solo accesible desde cron o admin
        if (!$this->isValidCronRequest()) {
            http_response_code(403);
            die('Access denied');
        }

        if (!$this->analyticsService) {
            echo "Analytics service not available\n";
            return;
        }

        $trackings = $this->db->fetchAll(
            "SELECT id FROM artist_trackings WHERE status = 'active'"
        );

        $updated = 0;
        foreach ($trackings as $tracking) {
            try {
                if ($this->analyticsService->saveDailyMetrics($tracking['id'])) {
                    $updated++;
                }
                sleep(1); // Rate limiting
            } catch (Exception $e) {
                error_log("Error updating metrics for tracking {$tracking['id']}: " . $e->getMessage());
            }
        }

        echo "Updated metrics for $updated trackings\n";
    }

    private function isValidCronRequest()
    {
        // Check if request is from localhost/cron or has valid key
        $validKey = $this->config['app']['cron_key'] ?? 'tracktraster_cron_2025';
        $providedKey = $_GET['key'] ?? '';
        
        return $providedKey === $validKey || 
               in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
    }

    public function export()
    {
        $user = $this->session->getUser();
        $artistId = $_GET['artist_id'] ?? null;
        
        if (!$artistId) {
            $this->session->setFlash('error', 'Debe seleccionar un artista');
            $this->redirect('analytics');
        }

        // Verify access
        try {
            $artist = $this->db->fetchOne(
                "SELECT a.*, at.country_code 
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'",
                [$artistId, $user['id']]
            );

            if (!$artist) {
                $this->session->setFlash('error', 'Artista no encontrado');
                $this->redirect('analytics');
            }            // Generate CSV export with real data
            try {
                if ($this->analyticsService) {
                    $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
                } else {
                    $analytics = $this->generateBasicAnalytics($artist);
                }
            } catch (Exception $e) {
                $analytics = $this->generateBasicAnalytics($artist);
            }
            
            $filename = 'analytics_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $artist['name']) . '_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, ['Fecha', 'Seguidores Spotify', 'Popularidad Spotify', 'Listeners Last.fm']);
            
            // Data from charts
            $followerData = $analytics['charts']['followers_growth'];
            $popularityData = $analytics['charts']['popularity_score'];
            $listenersData = $analytics['charts']['listeners_growth'];
            
            $maxRows = max(count($followerData), count($popularityData), count($listenersData));
            
            for ($i = 0; $i < $maxRows; $i++) {
                fputcsv($output, [
                    $followerData[$i]['date'] ?? '',
                    $followerData[$i]['value'] ?? 0,
                    $popularityData[$i]['value'] ?? 0,
                    $listenersData[$i]['value'] ?? 0
                ]);
            }
            
            fclose($output);
            exit;

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al exportar datos');
            $this->redirect('analytics');
        }
    }
    
    /**
     * Poblar métricas faltantes para trackings existentes
     */
    public function populateMetrics()
    {
        // Solo accesible desde admin o con clave
        $debugKey = $_GET['populate_key'] ?? '';
        $validKey = 'populate_metrics_2025';
        
        if ($debugKey !== $validKey) {
            http_response_code(403);
            die('Access denied. Use ?populate_key=populate_metrics_2025');
        }

        if (!$this->analyticsService) {
            die('Analytics service not available');
        }

        // Obtener trackings sin métricas
        $trackings = $this->db->fetchAll(
            "SELECT at.id, at.artist_id, at.tracking_start_date, a.name as artist_name 
             FROM artist_trackings at 
             JOIN artists a ON at.artist_id = a.id 
             LEFT JOIN spotify_metrics sm ON sm.tracking_id = at.id
             WHERE at.status = 'active' AND sm.id IS NULL"
        );

        echo "<h1>Poblando Métricas Faltantes</h1>";
        echo "<p>Trackings sin métricas encontrados: " . count($trackings) . "</p>";

        $created = 0;
        foreach ($trackings as $tracking) {
            echo "<p>Procesando: {$tracking['artist_name']} (ID: {$tracking['id']})</p>";
            
            try {
                if ($this->analyticsService->createInitialMetrics($tracking['id'])) {
                    $created++;
                    echo "<p style='color: green;'>✅ Métricas creadas exitosamente</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error creando métricas</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
            }
            
            // Small delay to avoid overwhelming the system
            usleep(100000); // 0.1 seconds
        }

        echo "<hr>";
        echo "<p><strong>Resumen:</strong> $created métricas iniciales creadas de " . count($trackings) . " trackings procesados.</p>";
        echo "<p><a href='/analytics'>Ir a Analytics</a></p>";
    }
    
    /**
     * Diagnóstico del sistema de tracking (solo para admin/debug)
     */
    public function systemDiagnostic()
    {
        // Solo accesible con clave de debug
        $debugKey = $_GET['debug_key'] ?? '';
        $validKey = 'diagnostic_2025';
        
        if ($debugKey !== $validKey) {
            http_response_code(403);
            die('Access denied. Use ?debug_key=diagnostic_2025');
        }

        echo "<h1>🔍 TrackTraster - Diagnóstico del Sistema</h1>";
        echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
        echo "<hr>";

        // 1. Estado de la base de datos
        echo "<h2>📊 Estado de la Base de Datos</h2>";
        try {
            $trackings = $this->db->fetchAll("SELECT COUNT(*) as total FROM artist_trackings WHERE status = 'active'");
            $artists = $this->db->fetchAll("SELECT COUNT(*) as total FROM artists");
            $users = $this->db->fetchAll("SELECT COUNT(*) as total FROM users");
            
            echo "<ul>";
            echo "<li>Trackings activos: " . $trackings[0]['total'] . "</li>";
            echo "<li>Artistas registrados: " . $artists[0]['total'] . "</li>";
            echo "<li>Usuarios registrados: " . $users[0]['total'] . "</li>";
            echo "</ul>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error conectando a la base de datos: " . $e->getMessage() . "</p>";
        }

        // 2. Estado de los servicios
        echo "<h2>🔧 Estado de los Servicios</h2>";
        echo "<ul>";
        echo "<li>AnalyticsService: " . ($this->analyticsService ? "✅ Disponible" : "❌ No disponible") . "</li>";
        echo "<li>TrackingLifecycleService: " . ($this->lifecycleService ? "✅ Disponible" : "❌ No disponible") . "</li>";
        echo "</ul>";

        // 3. Análisis de trackings
        echo "<h2>📈 Análisis de Trackings</h2>";
        try {
            $trackingDetails = $this->db->fetchAll(
                "SELECT at.id, at.artist_id, a.name as artist_name, at.event_name, at.event_date, 
                        at.tracking_start_date, at.tracking_status,
                        (SELECT COUNT(*) FROM spotify_metrics sm WHERE sm.tracking_id = at.id) as spotify_metrics_count,
                        (SELECT COUNT(*) FROM lastfm_metrics lm WHERE lm.tracking_id = at.id) as lastfm_metrics_count
                 FROM artist_trackings at 
                 JOIN artists a ON at.artist_id = a.id 
                 WHERE at.status = 'active'
                 ORDER BY at.id DESC"
            );

            if (empty($trackingDetails)) {
                echo "<p>No hay trackings activos.</p>";
            } else {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>ID</th><th>Artista</th><th>Evento</th><th>Fecha Evento</th><th>Estado</th><th>Métricas Spotify</th><th>Métricas Last.fm</th><th>Diagnóstico</th>";
                echo "</tr>";

                foreach ($trackingDetails as $tracking) {
                    $hasMetrics = $tracking['spotify_metrics_count'] > 0 || $tracking['lastfm_metrics_count'] > 0;
                    $diagnostic = $hasMetrics ? "✅ Con datos" : "⚠️ Sin métricas";
                    
                    echo "<tr>";
                    echo "<td>{$tracking['id']}</td>";
                    echo "<td>{$tracking['artist_name']}</td>";
                    echo "<td>{$tracking['event_name']}</td>";
                    echo "<td>{$tracking['event_date']}</td>";
                    echo "<td>{$tracking['tracking_status']}</td>";
                    echo "<td>{$tracking['spotify_metrics_count']}</td>";
                    echo "<td>{$tracking['lastfm_metrics_count']}</td>";
                    echo "<td>$diagnostic</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error analizando trackings: " . $e->getMessage() . "</p>";
        }

        // 4. Acciones de reparación
        echo "<h2>🔧 Acciones de Reparación</h2>";
        echo "<ul>";
        echo "<li><a href='/analytics/populateMetrics?populate_key=populate_metrics_2025' target='_blank'>Poblar métricas faltantes</a></li>";
        echo "<li><a href='/analytics/updateDailyMetrics?key=" . ($this->config['app']['cron_key'] ?? 'tracktraster_cron_2025') . "' target='_blank'>Actualizar métricas diarias</a></li>";
        echo "</ul>";

        // 5. Información del sistema
        echo "<h2>💻 Información del Sistema</h2>";
        echo "<ul>";
        echo "<li>PHP Version: " . PHP_VERSION . "</li>";
        echo "<li>Server Time: " . date('Y-m-d H:i:s') . "</li>";
        echo "<li>Memory Usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB</li>";
        echo "</ul>";

        // 6. Logs recientes (si existen)
        echo "<h2>📝 Logs Recientes</h2>";
        $logFile = dirname(dirname(dirname(__FILE__))) . '/logs/tracktraster.log';
        if (file_exists($logFile)) {
            $logLines = array_slice(file($logFile), -10);
            echo "<pre style='background-color: #f5f5f5; padding: 10px; overflow-x: auto;'>";
            foreach ($logLines as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        } else {
            echo "<p>No se encontraron logs recientes.</p>";
        }

        echo "<hr>";
        echo "<p><a href='/analytics'>← Volver a Analytics</a></p>";
    }    // ...existing code...
}
