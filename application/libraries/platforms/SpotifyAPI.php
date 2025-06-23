<?php
/**
 * SpotifyAPI - Integración con Spotify Web API
 */

require_once APPPATH . 'libraries/platforms/BaseMusicPlatform.php';

class SpotifyAPI extends BaseMusicPlatform
{
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $tokenExpiration;

    protected function initialize()
    {
        $this->clientId = $this->config['client_id'];
        $this->clientSecret = $this->config['client_secret'];
        $this->baseUrl = 'https://api.spotify.com/v1';
        $this->rateLimitDelay = 0.1; // Spotify permite muchos requests por segundo
        
        // Obtener token de acceso
        $this->getAccessToken();
    }

    /**
     * Obtener token de acceso usando Client Credentials Flow
     */
    private function getAccessToken()
    {
        // Verificar si ya tenemos un token válido
        if ($this->accessToken && time() < $this->tokenExpiration) {
            return $this->accessToken;
        }

        $authUrl = 'https://accounts.spotify.com/api/token';
        $authData = 'grant_type=client_credentials';
        
        $authHeaders = [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $authUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $authData,
            CURLOPT_HTTPHEADER => $authHeaders,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Failed to get Spotify access token: $response");
        }

        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Invalid token response from Spotify");
        }

        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpiration = time() + ($tokenData['expires_in'] ?? 3600) - 60; // 60 segundos de margen

        return $this->accessToken;
    }

    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        $this->getAccessToken();
        
        $params = [
            'q' => $query,
            'type' => 'artist',
            'limit' => min($limit, 50), // Spotify max es 50
        ];

        if ($country !== 'all') {
            $params['market'] = $this->mapCountryCode($country);
        }

        $url = $this->baseUrl . '/search?' . http_build_query($params);
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken
        ];

        try {
            $response = $this->makeRequest($url, $headers);
            
            if (!isset($response['artists']['items'])) {
                return [];
            }

            $artists = [];
            foreach ($response['artists']['items'] as $item) {
                $artists[] = $this->normalizeSpotifyArtist($item);
            }

            return $artists;

        } catch (Exception $e) {
            $this->logError("Search artists failed", ['query' => $query, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistDetails($artistId)
    {
        $this->getAccessToken();
        
        $url = $this->baseUrl . '/artists/' . $artistId;
        $headers = ['Authorization: Bearer ' . $this->accessToken];

        try {
            $response = $this->makeRequest($url, $headers);
            return $this->normalizeSpotifyArtist($response);
        } catch (Exception $e) {
            $this->logError("Get artist details failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistMetrics($artistId, $country = 'all')
    {
        $this->getAccessToken();
        
        // Obtener datos básicos del artista
        $artistDetails = $this->getArtistDetails($artistId);
        
        // Obtener álbumes para calcular métricas adicionales
        $albumsUrl = $this->baseUrl . '/artists/' . $artistId . '/albums?include_groups=album,single&limit=50';
        if ($country !== 'all') {
            $albumsUrl .= '&market=' . $this->mapCountryCode($country);
        }

        $headers = ['Authorization: Bearer ' . $this->accessToken];

        try {
            $albumsResponse = $this->makeRequest($albumsUrl, $headers);
            
            $metrics = [
                'followers' => $artistDetails['followers'],
                'popularity' => $artistDetails['popularity'],
                'total_albums' => count($albumsResponse['items'] ?? []),
                'genres' => $artistDetails['genres'],
                'last_updated' => date('Y-m-d H:i:s')
            ];

            return $metrics;

        } catch (Exception $e) {
            $this->logError("Get artist metrics failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            
            // Retornar métricas básicas si falla la obtención de álbumes
            return [
                'followers' => $artistDetails['followers'],
                'popularity' => $artistDetails['popularity'],
                'total_albums' => 0,
                'genres' => $artistDetails['genres'],
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }

    public function getTopTracks($artistId, $country = 'all', $limit = 10)
    {
        $this->getAccessToken();
        
        // Spotify requiere un país específico para top tracks
        $market = $country === 'all' ? 'US' : $this->mapCountryCode($country);
        
        $url = $this->baseUrl . '/artists/' . $artistId . '/top-tracks?market=' . $market;
        $headers = ['Authorization: Bearer ' . $this->accessToken];

        try {
            $response = $this->makeRequest($url, $headers);
            
            if (!isset($response['tracks'])) {
                return [];
            }

            $tracks = [];
            $count = 0;
            foreach ($response['tracks'] as $track) {
                if ($count >= $limit) break;
                
                $tracks[] = $this->normalizeSpotifyTrack($track);
                $count++;
            }

            return $tracks;

        } catch (Exception $e) {
            $this->logError("Get top tracks failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Normalizar datos de artista de Spotify
     */
    private function normalizeSpotifyArtist($data)
    {
        return $this->normalizeArtistData([
            'id' => $data['id'],
            'name' => $data['name'],
            'image' => isset($data['images'][0]['url']) ? $data['images'][0]['url'] : null,
            'url' => $data['external_urls']['spotify'] ?? null,
            'followers' => $data['followers']['total'] ?? null,
            'popularity' => $data['popularity'] ?? null,
            'genres' => $data['genres'] ?? [],
            'verified' => true // Spotify siempre tiene artistas verificados
        ]);
    }

    /**
     * Normalizar datos de track de Spotify
     */
    private function normalizeSpotifyTrack($data)
    {
        return $this->normalizeTrackData([
            'id' => $data['id'],
            'name' => $data['name'],
            'artist' => $data['artists'][0]['name'] ?? '',
            'album' => $data['album']['name'] ?? '',
            'duration' => $data['duration_ms'] ?? 0,
            'popularity' => $data['popularity'] ?? null,
            'preview_url' => $data['preview_url'] ?? null,
            'image' => isset($data['album']['images'][0]['url']) ? $data['album']['images'][0]['url'] : null,
            'url' => $data['external_urls']['spotify'] ?? null
        ]);
    }

    /**
     * Obtener información de álbumes de un artista
     */
    public function getArtistAlbums($artistId, $country = 'all', $limit = 20)
    {
        $this->getAccessToken();
        
        $params = [
            'include_groups' => 'album,single',
            'limit' => min($limit, 50)
        ];

        if ($country !== 'all') {
            $params['market'] = $this->mapCountryCode($country);
        }

        $url = $this->baseUrl . '/artists/' . $artistId . '/albums?' . http_build_query($params);
        $headers = ['Authorization: Bearer ' . $this->accessToken];

        try {
            $response = $this->makeRequest($url, $headers);
            
            $albums = [];
            foreach ($response['items'] ?? [] as $album) {
                $albums[] = [
                    'id' => $album['id'],
                    'name' => $album['name'],
                    'release_date' => $album['release_date'] ?? null,
                    'type' => $album['album_type'] ?? 'album',
                    'total_tracks' => $album['total_tracks'] ?? 0,
                    'image' => isset($album['images'][0]['url']) ? $album['images'][0]['url'] : null,
                    'url' => $album['external_urls']['spotify'] ?? null
                ];
            }

            return $albums;

        } catch (Exception $e) {
            $this->logError("Get artist albums failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
?>
