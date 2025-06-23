<?php
/**
 * YouTubeMusicAPI - Integración con YouTube Music API
 * Nota: YouTube Music no tiene API pública oficial, usando métodos alternativos
 */

require_once APPPATH . 'libraries/platforms/BaseMusicPlatform.php';

class YouTubeMusicAPI extends BaseMusicPlatform
{
    protected $apiKey;

    protected function initialize()
    {
        $this->apiKey = $this->config['api_key'] ?? null;
        $this->baseUrl = 'https://www.googleapis.com/youtube/v3';
        $this->rateLimitDelay = 0.1;
    }

    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        if (!$this->apiKey) {
            throw new Exception("YouTube API key is required");
        }

        $params = [
            'key' => $this->apiKey,
            'q' => $query . ' artist',
            'type' => 'channel',
            'part' => 'snippet,statistics',
            'maxResults' => min($limit, 50),
            'order' => 'relevance'
        ];

        if ($country !== 'all') {
            $params['regionCode'] = $this->mapCountryCode($country);
        }

        $url = $this->baseUrl . '/search?' . http_build_query($params);

        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['items'])) {
                return [];
            }

            $artists = [];
            foreach ($response['items'] as $item) {
                // Solo incluir canales que parecen ser de música
                if ($this->isLikelyMusicChannel($item)) {
                    $artists[] = $this->normalizeYouTubeArtist($item);
                }
            }

            return $artists;

        } catch (Exception $e) {
            $this->logError("Search artists failed", ['query' => $query, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistDetails($artistId)
    {
        if (!$this->apiKey) {
            throw new Exception("YouTube API key is required");
        }

        $params = [
            'key' => $this->apiKey,
            'id' => $artistId,
            'part' => 'snippet,statistics,brandingSettings'
        ];

        $url = $this->baseUrl . '/channels?' . http_build_query($params);

        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['items'][0])) {
                throw new Exception("Artist not found");
            }

            return $this->normalizeYouTubeArtist($response['items'][0]);

        } catch (Exception $e) {
            $this->logError("Get artist details failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistMetrics($artistId, $country = 'all')
    {
        $artistDetails = $this->getArtistDetails($artistId);
        
        return [
            'subscribers' => $artistDetails['followers'],
            'total_views' => $artistDetails['total_views'] ?? 0,
            'video_count' => $artistDetails['video_count'] ?? 0,
            'popularity' => $artistDetails['popularity'],
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }

    public function getTopTracks($artistId, $country = 'all', $limit = 10)
    {
        if (!$this->apiKey) {
            throw new Exception("YouTube API key is required");
        }

        $params = [
            'key' => $this->apiKey,
            'channelId' => $artistId,
            'part' => 'snippet,statistics',
            'order' => 'viewCount',
            'type' => 'video',
            'maxResults' => min($limit, 50)
        ];

        $url = $this->baseUrl . '/search?' . http_build_query($params);

        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['items'])) {
                return [];
            }

            $tracks = [];
            foreach ($response['items'] as $item) {
                if ($item['id']['kind'] === 'youtube#video') {
                    $tracks[] = $this->normalizeYouTubeTrack($item);
                }
            }

            return $tracks;

        } catch (Exception $e) {
            $this->logError("Get top tracks failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Normalizar datos de artista de YouTube
     */
    private function normalizeYouTubeArtist($data)
    {
        $statistics = $data['statistics'] ?? [];
        
        return $this->normalizeArtistData([
            'id' => $data['id'],
            'name' => $data['snippet']['title'],
            'image' => $data['snippet']['thumbnails']['high']['url'] ?? $data['snippet']['thumbnails']['default']['url'] ?? null,
            'url' => 'https://www.youtube.com/channel/' . $data['id'],
            'followers' => intval($statistics['subscriberCount'] ?? 0),
            'popularity' => $this->calculateYouTubePopularity($statistics),
            'genres' => $this->extractGenresFromDescription($data['snippet']['description'] ?? ''),
            'verified' => $this->isVerifiedChannel($data),
            'total_views' => intval($statistics['viewCount'] ?? 0),
            'video_count' => intval($statistics['videoCount'] ?? 0)
        ]);
    }

    /**
     * Normalizar datos de track de YouTube
     */
    private function normalizeYouTubeTrack($data)
    {
        return $this->normalizeTrackData([
            'id' => $data['id']['videoId'],
            'name' => $data['snippet']['title'],
            'artist' => $data['snippet']['channelTitle'],
            'album' => '',
            'duration' => 0, // Requiere llamada adicional a videos endpoint
            'popularity' => null,
            'preview_url' => null,
            'image' => $data['snippet']['thumbnails']['high']['url'] ?? null,
            'url' => 'https://www.youtube.com/watch?v=' . $data['id']['videoId']
        ]);
    }

    /**
     * Verificar si un canal parece ser de música
     */
    private function isLikelyMusicChannel($item)
    {
        $title = strtolower($item['snippet']['title']);
        $description = strtolower($item['snippet']['description'] ?? '');
        
        $musicKeywords = ['music', 'official', 'artist', 'song', 'album', 'vevo', 'records'];
        
        foreach ($musicKeywords as $keyword) {
            if (strpos($title, $keyword) !== false || strpos($description, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Calcular popularidad basada en estadísticas de YouTube
     */
    private function calculateYouTubePopularity($statistics)
    {
        $subscribers = intval($statistics['subscriberCount'] ?? 0);
        $views = intval($statistics['viewCount'] ?? 0);
        $videos = intval($statistics['videoCount'] ?? 1);
        
        $avgViewsPerVideo = $videos > 0 ? $views / $videos : 0;
        
        // Algoritmo simple para calcular popularidad 0-100
        if ($subscribers === 0) return 0;
        if ($subscribers < 1000) return 5;
        if ($subscribers < 10000) return 15;
        if ($subscribers < 100000) return 25;
        if ($subscribers < 500000) return 40;
        if ($subscribers < 1000000) return 55;
        if ($subscribers < 5000000) return 70;
        if ($subscribers < 10000000) return 85;
        
        return 100;
    }

    /**
     * Extraer géneros de la descripción del canal
     */
    private function extractGenresFromDescription($description)
    {
        $genres = [];
        $commonGenres = [
            'pop', 'rock', 'hip hop', 'rap', 'reggaeton', 'latin', 'folk', 'country',
            'electronic', 'dance', 'edm', 'jazz', 'blues', 'classical', 'metal',
            'indie', 'alternative', 'r&b', 'soul', 'funk', 'punk', 'ska'
        ];
        
        $description = strtolower($description);
        
        foreach ($commonGenres as $genre) {
            if (strpos($description, $genre) !== false) {
                $genres[] = ucfirst($genre);
            }
        }
        
        return array_unique($genres);
    }

    /**
     * Verificar si el canal está verificado
     */
    private function isVerifiedChannel($data)
    {
        // YouTube no expone estado de verificación en API pública
        // Inferir basado en número de suscriptores
        $subscribers = intval($data['statistics']['subscriberCount'] ?? 0);
        return $subscribers > 100000;
    }
}
?>
