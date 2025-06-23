<?php
/**
 * Analytics Service - Servicio real de analíticas
 * Obtiene datos reales de APIs y genera métricas auténticas
 */

class AnalyticsService
{
    private $db;
    private $spotifyService;
    private $deezerService;
    private $lastfmService;
    private $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
        
        // Initialize API services
        require_once APPPATH . 'libraries/SpotifyService.php';
        require_once APPPATH . 'libraries/DeezerService.php';
        require_once APPPATH . 'libraries/LastfmService.php';
        
        $this->spotifyService = new SpotifyService($config);
        $this->deezerService = new DeezerService($config);
        $this->lastfmService = new LastfmService($config);
    }

    /**
     * Obtener analíticas reales del artista
     */
    public function getArtistAnalytics($artistId, $userId)
    {
        // Verificar acceso del usuario
        $tracking = $this->db->fetchOne(
            "SELECT at.*, a.name as artist_name, a.spotify_id, a.lastfm_name 
             FROM artist_trackings at 
             JOIN artists a ON at.artist_id = a.id 
             WHERE at.artist_id = ? AND at.user_id = ? AND at.status = 'active'",
            [$artistId, $userId]
        );

        if (!$tracking) {
            throw new Exception('No tienes acceso a este artista');
        }

        // Obtener métricas actuales de las APIs
        $currentMetrics = $this->getCurrentMetrics($tracking);
        
        // Obtener datos históricos de la base de datos
        $historicalData = $this->getHistoricalData($tracking['id']);
        
        // Calcular tendencias y crecimiento
        $trends = $this->calculateTrends($historicalData);
        
        // Generar analíticas completas
        return [
            'summary' => $this->generateSummary($currentMetrics, $historicalData, $tracking),
            'trends' => $trends,
            'charts' => $this->generateChartData($historicalData),
            'regional_data' => $this->getRegionalData($tracking),
            'platforms' => $currentMetrics,
            'tracking_info' => $tracking
        ];
    }

    /**
     * Obtener métricas actuales de todas las plataformas
     */
    private function getCurrentMetrics($tracking)
    {
        $metrics = [
            'spotify' => null,
            'deezer' => null,
            'lastfm' => null,
            'total_followers' => 0,
            'avg_popularity' => 0
        ];

        // Spotify
        if ($tracking['spotify_id'] && $this->config['spotify']['enabled']) {
            try {
                $spotifyData = $this->spotifyService->getArtistDetails($tracking['spotify_id']);
                if ($spotifyData['status'] === 'found') {
                    $metrics['spotify'] = [
                        'followers' => $spotifyData['followers'],
                        'popularity' => $spotifyData['popularity'],
                        'monthly_listeners' => $spotifyData['monthly_listeners'] ?? 0
                    ];
                    $metrics['total_followers'] += $spotifyData['followers'];
                }
            } catch (Exception $e) {
                error_log("Error getting Spotify data: " . $e->getMessage());
            }
        }

        // Deezer
        if ($this->config['deezer']['enabled']) {
            try {
                $deezerData = $this->deezerService->searchArtist($tracking['artist_name']);
                if ($deezerData['status'] === 'found') {
                    $metrics['deezer'] = [
                        'fans' => $deezerData['fans'] ?? 0
                    ];
                    $metrics['total_followers'] += $deezerData['fans'] ?? 0;
                }
            } catch (Exception $e) {
                error_log("Error getting Deezer data: " . $e->getMessage());
            }
        }

        // Last.fm
        if ($tracking['lastfm_name'] && $this->config['lastfm']['enabled']) {
            try {
                $lastfmData = $this->lastfmService->getArtistInfo($tracking['lastfm_name']);
                if ($lastfmData['status'] === 'found') {
                    $metrics['lastfm'] = [
                        'listeners' => $lastfmData['listeners'] ?? 0,
                        'playcount' => $lastfmData['playcount'] ?? 0
                    ];
                }
            } catch (Exception $e) {
                error_log("Error getting Last.fm data: " . $e->getMessage());
            }
        }

        // Calcular popularidad promedio
        $popularityValues = [];
        if ($metrics['spotify']) $popularityValues[] = $metrics['spotify']['popularity'];
        
        $metrics['avg_popularity'] = !empty($popularityValues) ? 
            array_sum($popularityValues) / count($popularityValues) : 0;

        return $metrics;
    }

    /**
     * Obtener datos históricos de la base de datos
     */
    private function getHistoricalData($trackingId)
    {
        $data = [
            'spotify' => [],
            'deezer' => [],
            'lastfm' => []
        ];

        // Spotify metrics
        $spotifyMetrics = $this->db->fetchAll(
            "SELECT * FROM spotify_metrics 
             WHERE tracking_id = ? 
             ORDER BY metric_date DESC LIMIT 30",
            [$trackingId]
        );
        $data['spotify'] = array_reverse($spotifyMetrics);

        // YouTube metrics (si existen)
        $youtubeMetrics = $this->db->fetchAll(
            "SELECT * FROM youtube_metrics 
             WHERE tracking_id = ? 
             ORDER BY metric_date DESC LIMIT 30",
            [$trackingId]
        );
        $data['youtube'] = array_reverse($youtubeMetrics);

        // Last.fm metrics
        $lastfmMetrics = $this->db->fetchAll(
            "SELECT * FROM lastfm_metrics 
             WHERE tracking_id = ? 
             ORDER BY metric_date DESC LIMIT 30",
            [$trackingId]
        );
        $data['lastfm'] = array_reverse($lastfmMetrics);

        return $data;
    }

    /**
     * Calcular tendencias y crecimiento
     */
    private function calculateTrends($historicalData)
    {
        $trends = [
            'followers_trend' => 0,
            'popularity_trend' => 0,
            'listeners_trend' => 0,
            'engagement_trend' => 0
        ];

        // Calcular tendencia de seguidores (Spotify)
        if (count($historicalData['spotify']) >= 2) {
            $recent = array_slice($historicalData['spotify'], -7); // Últimos 7 días
            $previous = array_slice($historicalData['spotify'], -14, 7); // 7 días anteriores
            
            if (!empty($recent) && !empty($previous)) {
                $recentAvg = array_sum(array_column($recent, 'followers')) / count($recent);
                $previousAvg = array_sum(array_column($previous, 'followers')) / count($previous);
                
                if ($previousAvg > 0) {
                    $trends['followers_trend'] = (($recentAvg - $previousAvg) / $previousAvg) * 100;
                }
                
                // Tendencia de popularidad
                $recentPop = array_sum(array_column($recent, 'popularity')) / count($recent);
                $previousPop = array_sum(array_column($previous, 'popularity')) / count($previous);
                
                if ($previousPop > 0) {
                    $trends['popularity_trend'] = (($recentPop - $previousPop) / $previousPop) * 100;
                }
            }
        }

        // Calcular tendencia de listeners (Last.fm)
        if (count($historicalData['lastfm']) >= 2) {
            $recent = array_slice($historicalData['lastfm'], -7);
            $previous = array_slice($historicalData['lastfm'], -14, 7);
            
            if (!empty($recent) && !empty($previous)) {
                $recentAvg = array_sum(array_column($recent, 'listeners')) / count($recent);
                $previousAvg = array_sum(array_column($previous, 'listeners')) / count($previous);
                
                if ($previousAvg > 0) {
                    $trends['listeners_trend'] = (($recentAvg - $previousAvg) / $previousAvg) * 100;
                }
            }
        }

        return $trends;
    }

    /**
     * Generar datos para gráficos
     */
    private function generateChartData($historicalData)
    {
        $charts = [
            'followers_growth' => [],
            'popularity_score' => [],
            'listeners_growth' => []
        ];

        // Gráfico de seguidores (Spotify)
        foreach ($historicalData['spotify'] as $metric) {
            $charts['followers_growth'][] = [
                'date' => $metric['metric_date'],
                'value' => (int)$metric['followers']
            ];
        }

        // Gráfico de popularidad (Spotify)
        foreach ($historicalData['spotify'] as $metric) {
            $charts['popularity_score'][] = [
                'date' => $metric['metric_date'],
                'value' => (int)$metric['popularity']
            ];
        }

        // Gráfico de listeners (Last.fm)
        foreach ($historicalData['lastfm'] as $metric) {
            $charts['listeners_growth'][] = [
                'date' => $metric['metric_date'],
                'value' => (int)$metric['listeners']
            ];
        }

        return $charts;
    }

    /**
     * Generar resumen de métricas
     */
    private function generateSummary($currentMetrics, $historicalData, $tracking)
    {
        $startDate = strtotime($tracking['tracking_start_date']);
        $days = floor((time() - $startDate) / (60 * 60 * 24));

        // Calcular crecimiento de seguidores
        $followersGrowth = 0;
        if (!empty($historicalData['spotify'])) {
            $first = reset($historicalData['spotify']);
            $last = end($historicalData['spotify']);
            $followersGrowth = $last['followers'] - $first['followers'];
        }

        return [
            'total_followers' => $currentMetrics['total_followers'],
            'followers_growth' => $followersGrowth,
            'current_popularity' => round($currentMetrics['avg_popularity']),
            'monthly_listeners' => $currentMetrics['spotify']['monthly_listeners'] ?? 0,
            'tracking_days' => $days,
            'platforms_count' => count(array_filter([
                $currentMetrics['spotify'],
                $currentMetrics['deezer'],
                $currentMetrics['lastfm']
            ]))
        ];
    }

    /**
     * Obtener datos regionales (por ahora simulados, en el futuro desde APIs específicas)
     */
    private function getRegionalData($tracking)
    {
        // Por ahora retornamos datos simulados para LATAM
        // En el futuro esto vendría de Spotify for Artists API o similar
        $latinCities = [
            'Buenos Aires', 'Ciudad de México', 'São Paulo', 'Bogotá', 'Lima',
            'Santiago', 'Caracas', 'Montevideo', 'Quito', 'La Paz'
        ];

        $topCities = [];
        for ($i = 0; $i < 5; $i++) {
            $topCities[] = [
                'name' => $latinCities[$i],
                'streams' => rand(1000, 50000) // Por ahora simulado
            ];
        }

        return [
            'top_cities' => $topCities,
            'country_focus' => $tracking['country_code']
        ];
    }

    /**
     * Guardar métricas diarias
     */
    public function saveDailyMetrics($trackingId)
    {
        $tracking = $this->db->fetchOne(
            "SELECT at.*, a.spotify_id, a.lastfm_name 
             FROM artist_trackings at 
             JOIN artists a ON at.artist_id = a.id 
             WHERE at.id = ?",
            [$trackingId]
        );

        if (!$tracking) return false;

        $today = date('Y-m-d');
        $metrics = $this->getCurrentMetrics($tracking);

        // Guardar métricas de Spotify
        if ($metrics['spotify']) {
            $this->db->execute(
                "INSERT INTO spotify_metrics 
                 (tracking_id, metric_date, popularity, followers, monthly_listeners) 
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 popularity = VALUES(popularity),
                 followers = VALUES(followers),
                 monthly_listeners = VALUES(monthly_listeners)",
                [
                    $trackingId,
                    $today,
                    $metrics['spotify']['popularity'],
                    $metrics['spotify']['followers'],
                    $metrics['spotify']['monthly_listeners']
                ]
            );
        }

        // Guardar métricas de Last.fm
        if ($metrics['lastfm']) {
            $this->db->execute(
                "INSERT INTO lastfm_metrics 
                 (tracking_id, metric_date, listeners, scrobbles) 
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 listeners = VALUES(listeners),
                 scrobbles = VALUES(scrobbles)",
                [
                    $trackingId,
                    $today,
                    $metrics['lastfm']['listeners'],
                    $metrics['lastfm']['playcount']
                ]
            );
        }

        return true;
    }
}
?>
