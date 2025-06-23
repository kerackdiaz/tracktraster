<?php
/**
 * Analytics Controller - Analíticas y métricas de artistas
 */

class Analytics extends BaseController
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
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
                );

                if ($selectedArtist) {
                    $analytics = $this->generateMockAnalytics($selectedArtist);
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
    }

    private function generateMockAnalytics($artist)
    {
        // Generate mock analytics data for demonstration
        // In a real implementation, this would fetch actual data from APIs
        
        $startDate = strtotime($artist['tracking_start_date']);
        $days = floor((time() - $startDate) / (60 * 60 * 24));
        
        return [
            'summary' => [
                'total_streams' => rand(100000, 5000000),
                'followers_growth' => rand(-5, 50),
                'chart_position' => rand(1, 100),
                'social_mentions' => rand(50, 1000),
                'tracking_days' => $days
            ],
            'trends' => [
                'streams_trend' => rand(-10, 30),
                'followers_trend' => rand(-5, 25),
                'popularity_trend' => rand(-3, 15),
                'engagement_trend' => rand(-8, 20)
            ],
            'charts' => [
                'daily_streams' => $this->generateMockChartData($days, 10000, 50000),
                'followers_growth' => $this->generateMockChartData($days, -100, 500),
                'popularity_score' => $this->generateMockChartData($days, 30, 90)
            ],
            'regional_data' => [
                'top_cities' => [
                    ['name' => 'Buenos Aires', 'streams' => rand(10000, 100000)],
                    ['name' => 'Ciudad de México', 'streams' => rand(8000, 80000)],
                    ['name' => 'São Paulo', 'streams' => rand(12000, 120000)],
                    ['name' => 'Bogotá', 'streams' => rand(6000, 60000)],
                    ['name' => 'Lima', 'streams' => rand(4000, 40000)]
                ]
            ]
        ];
    }

    private function generateMockChartData($days, $min, $max)
    {
        $data = [];
        $baseValue = rand($min, $max);
        
        for ($i = max(0, $days - 30); $i <= $days; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $variance = rand(-($max - $min) * 0.1, ($max - $min) * 0.1);
            $value = max($min, min($max, $baseValue + $variance));
            
            $data[] = [
                'date' => $date,
                'value' => $value
            ];
            
            $baseValue = $value;
        }
        
        return array_reverse($data);
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
            }

            // Generate CSV export
            $analytics = $this->generateMockAnalytics($artist);
            $filename = 'analytics_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $artist['name']) . '_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, ['Fecha', 'Streams Diarios', 'Crecimiento Seguidores', 'Score Popularidad']);
            
            // Data
            $streamData = $analytics['charts']['daily_streams'];
            $followerData = $analytics['charts']['followers_growth'];
            $popularityData = $analytics['charts']['popularity_score'];
            
            for ($i = 0; $i < count($streamData); $i++) {
                fputcsv($output, [
                    $streamData[$i]['date'],
                    $streamData[$i]['value'],
                    $followerData[$i]['value'] ?? 0,
                    $popularityData[$i]['value'] ?? 0
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
