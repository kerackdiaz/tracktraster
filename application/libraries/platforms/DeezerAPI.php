<?php
/**
 * DeezerAPI - Integración con Deezer API
 */

require_once APPPATH . 'libraries/platforms/BaseMusicPlatform.php';

class DeezerAPI extends BaseMusicPlatform
{
    protected function initialize()
    {
        $this->baseUrl = 'https://api.deezer.com';
        $this->rateLimitDelay = 0.2; // Deezer tiene rate limiting
    }

    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        $params = [
            'q' => $query,
            'limit' => min($limit, 100), // Deezer max es 100
            'index' => 0
        ];

        $url = $this->baseUrl . '/search/artist?' . http_build_query($params);

        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['data'])) {
                return [];
            }

            $artists = [];
            foreach ($response['data'] as $item) {
                $artists[] = $this->normalizeDeezerArtist($item);
            }

            return $artists;

        } catch (Exception $e) {
            $this->logError("Search artists failed", ['query' => $query, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistDetails($artistId)
    {
        $url = $this->baseUrl . '/artist/' . $artistId;

        try {
            $response = $this->makeRequest($url);
            return $this->normalizeDeezerArtist($response);
        } catch (Exception $e) {
            $this->logError("Get artist details failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getArtistMetrics($artistId, $country = 'all')
    {
        // Obtener datos básicos del artista
        $artistDetails = $this->getArtistDetails($artistId);
        
        // Obtener álbumes para métricas adicionales
        $albumsUrl = $this->baseUrl . '/artist/' . $artistId . '/albums?limit=100';

        try {
            $albumsResponse = $this->makeRequest($albumsUrl);
            
            $metrics = [
                'followers' => $artistDetails['followers'],
                'popularity' => $artistDetails['popularity'],
                'total_albums' => count($albumsResponse['data'] ?? []),
                'total_fans' => $artistDetails['followers'], // En Deezer son "fans"
                'last_updated' => date('Y-m-d H:i:s')
            ];

            return $metrics;

        } catch (Exception $e) {
            $this->logError("Get artist metrics failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            
            return [
                'followers' => $artistDetails['followers'],
                'popularity' => $artistDetails['popularity'],
                'total_albums' => 0,
                'total_fans' => $artistDetails['followers'],
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }

    public function getTopTracks($artistId, $country = 'all', $limit = 10)
    {
        $url = $this->baseUrl . '/artist/' . $artistId . '/top?limit=' . min($limit, 50);

        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['data'])) {
                return [];
            }

            $tracks = [];
            foreach ($response['data'] as $track) {
                $tracks[] = $this->normalizeDeezerTrack($track);
            }

            return $tracks;

        } catch (Exception $e) {
            $this->logError("Get top tracks failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Normalizar datos de artista de Deezer
     */
    private function normalizeDeezerArtist($data)
    {
        return $this->normalizeArtistData([
            'id' => $data['id'],
            'name' => $data['name'],
            'image' => $data['picture_medium'] ?? $data['picture'] ?? null,
            'url' => $data['link'] ?? null,
            'followers' => $data['nb_fan'] ?? null,
            'popularity' => $this->calculateDeezerPopularity($data),
            'genres' => [], // Deezer no provee géneros en búsqueda básica
            'verified' => true
        ]);
    }

    /**
     * Normalizar datos de track de Deezer
     */
    private function normalizeDeezerTrack($data)
    {
        return $this->normalizeTrackData([
            'id' => $data['id'],
            'name' => $data['title'],
            'artist' => $data['artist']['name'] ?? '',
            'album' => $data['album']['title'] ?? '',
            'duration' => ($data['duration'] ?? 0) * 1000, // Deezer da segundos, convertir a ms
            'popularity' => $data['rank'] ?? null,
            'preview_url' => $data['preview'] ?? null,
            'image' => $data['album']['cover_medium'] ?? null,
            'url' => $data['link'] ?? null
        ]);
    }

    /**
     * Calcular popularidad estimada basada en datos de Deezer
     */
    private function calculateDeezerPopularity($data)
    {
        $fans = $data['nb_fan'] ?? 0;
        
        // Convertir número de fans a escala de popularidad 0-100 similar a Spotify
        if ($fans === 0) return 0;
        if ($fans < 1000) return 10;
        if ($fans < 10000) return 20;
        if ($fans < 50000) return 30;
        if ($fans < 100000) return 40;
        if ($fans < 500000) return 50;
        if ($fans < 1000000) return 60;
        if ($fans < 5000000) return 70;
        if ($fans < 10000000) return 80;
        if ($fans < 50000000) return 90;
        
        return 100;
    }

    /**
     * Obtener álbumes de un artista
     */
    public function getArtistAlbums($artistId, $limit = 25)
    {
        $url = $this->baseUrl . '/artist/' . $artistId . '/albums?limit=' . min($limit, 100);

        try {
            $response = $this->makeRequest($url);
            
            $albums = [];
            foreach ($response['data'] ?? [] as $album) {
                $albums[] = [
                    'id' => $album['id'],
                    'name' => $album['title'],
                    'release_date' => $album['release_date'] ?? null,
                    'type' => $album['record_type'] ?? 'album',
                    'total_tracks' => $album['nb_tracks'] ?? 0,
                    'image' => $album['cover_medium'] ?? null,
                    'url' => $album['link'] ?? null
                ];
            }

            return $albums;

        } catch (Exception $e) {
            $this->logError("Get artist albums failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Obtener géneros de un artista (requiere llamada adicional)
     */
    public function getArtistGenres($artistId)
    {
        // Deezer no provee géneros directamente, intentar obtener de álbumes
        try {
            $albums = $this->getArtistAlbums($artistId, 5);
            $genres = [];
            
            foreach ($albums as $album) {
                // Obtener detalles del álbum para géneros
                $albumUrl = $this->baseUrl . '/album/' . $album['id'];
                $albumDetails = $this->makeRequest($albumUrl);
                
                if (isset($albumDetails['genres']['data'])) {
                    foreach ($albumDetails['genres']['data'] as $genre) {
                        if (!in_array($genre['name'], $genres)) {
                            $genres[] = $genre['name'];
                        }
                    }
                }
            }

            return $genres;

        } catch (Exception $e) {
            $this->logError("Get artist genres failed", ['artist_id' => $artistId, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
?>
