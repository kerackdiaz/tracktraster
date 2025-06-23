<?php
/**
 * Analytics Controller - Analíticas y métricas de artistas
 */

class Analytics extends BaseController
{    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
        
        // Initialize analytics service
        require_once APPPATH . 'services/AnalyticsService.php';
        $this->analyticsService = new AnalyticsService($this->db, $config);
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
                    // Get real analytics data
                    try {
                        $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
                    } catch (Exception $e) {
                        // Fallback to basic data if analytics fail
                        $analytics = $this->generateBasicAnalytics($selectedArtist);
                        error_log("Analytics error: " . $e->getMessage());
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
        
        return [
            'summary' => [
                'total_followers' => 0,
                'followers_growth' => 0,
                'current_popularity' => 0,
                'monthly_listeners' => 0,
                'tracking_days' => $days,
                'platforms_count' => 0
            ],
            'trends' => [
                'followers_trend' => 0,
                'popularity_trend' => 0,
                'listeners_trend' => 0,
                'engagement_trend' => 0
            ],
            'charts' => [
                'followers_growth' => [],
                'popularity_score' => [],
                'listeners_growth' => []
            ],
            'regional_data' => [
                'top_cities' => [],
                'country_focus' => $artist['country_code']
            ],
            'platforms' => [
                'spotify' => null,
                'deezer' => null,
                'lastfm' => null,
                'total_followers' => 0,
                'avg_popularity' => 0
            ],
            'tracking_info' => $artist,
            'message' => 'Recopilando datos iniciales. Las métricas aparecerán una vez que se recolecten datos de las APIs.'
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

        $trackings = $this->db->fetchAll(
            "SELECT id FROM artist_trackings WHERE status = 'active'"
        );

        $updated = 0;
        foreach ($trackings as $tracking) {
            try {
                if ($this->analyticsService->saveDailyMetrics($tracking['id'])) {
                    $updated++;
                }
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
            $analytics = $this->analyticsService->getArtistAnalytics($artistId, $user['id']);
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
}
