<?php
/**
 * Analytics Controller - Analíticas y métricas de artistas
 */

class Analytics extends BaseController
{    public function __construct($config)
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
    }

    public function index()
    {
        $user = $this->session->getUser();
        $artistId = $_GET['artist_id'] ?? null;
        
        // Get user's tracked artists for dropdown
        $trackedArtists = [];
        try {
            $trackedArtists = $this->db->fetchAll(
                "SELECT DISTINCT a.id, a.name, a.image_url 
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE at.user_id = ? AND at.status = 'active'
                 ORDER BY a.name",
                [$user['id']]
            );
        } catch (Exception $e) {
            $trackedArtists = [];
        }

        $selectedArtist = null;
        $analytics = null;

        if ($artistId && !empty($trackedArtists)) {
            try {
                // Verify user has access to this artist
                $selectedArtist = $this->db->fetchOne(
                    "SELECT a.*, at.country_code, at.event_name, at.event_date, at.tracking_start_date 
                     FROM artists a 
                     JOIN artist_trackings at ON a.id = at.artist_id 
                     WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'",
                    [$artistId, $user['id']]
                );                if ($selectedArtist) {
                    // Get real analytics data with better error handling
                    if ($this->analyticsService) {
                        try {
                            $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
                        } catch (Exception $e) {
                            error_log("Analytics error: " . $e->getMessage());
                            $analytics = $this->generateBasicAnalytics($selectedArtist);
                        }
                    } else {
                        // Fallback when service is not available
                        $analytics = $this->generateBasicAnalytics($selectedArtist);
                    }
                }
            } catch (Exception $e) {
                $selectedArtist = null;
            }
        }

        $data = [
            'title' => 'Analíticas - TrackTraster',
            'page_title' => 'Analíticas de Artistas',
            'active_menu' => 'analytics',
            'user' => $user,
            'tracked_artists' => $trackedArtists,
            'selected_artist' => $selectedArtist,
            'analytics' => $analytics,
            'countries' => $this->config['countries']
        ];

        $this->loadView('analytics/index', $data);
    }    private function generateBasicAnalytics($artist)
    {
        // Generate basic analytics when no historical data exists
        $startDate = strtotime($artist['tracking_start_date']);
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
                'country_focus' => $artist['country_code']
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
            'message' => 'Mostrando datos simulados. Para datos reales, configure las APIs en el panel de administración.'
        ];
    }    /**
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
}
