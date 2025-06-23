<?php
/**
 * Last.fm API Integration
 * Proporciona datos de scrobbles, oyentes y estadísticas de artistas
 */

class LastfmAPI
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://ws.audioscrobbler.com/2.0/';
    private $enabled;

    public function __construct($config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->enabled = $config['enabled'] ?? false;
        
        if (!$this->enabled) {
            throw new Exception('Last.fm API no está habilitada');
        }
        
        if (empty($this->apiKey)) {
            throw new Exception('Last.fm API Key no configurada');
        }
    }

    /**
     * Buscar artistas en Last.fm
     */
    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        if (!$this->enabled) {
            throw new Exception('Last.fm API no está habilitada');
        }

        $artists = [];
        
        try {
            $params = [
                'method' => 'artist.search',
                'artist' => $query,
                'api_key' => $this->apiKey,
                'format' => 'json',
                'limit' => $limit
            ];

            $response = $this->makeRequest($params);
            $data = json_decode($response, true);

            if (isset($data['results']['artistmatches']['artist'])) {
                $artistList = $data['results']['artistmatches']['artist'];
                
                // Si solo hay un artista, Last.fm lo devuelve como objeto, no array
                if (isset($artistList['name'])) {
                    $artistList = [$artistList];
                }

                foreach ($artistList as $item) {
                    $artist = $this->formatArtistData($item);
                    if ($artist) {
                        $artists[] = $artist;
                    }
                }
            }

        } catch (Exception $e) {
            throw new Exception('Error en Last.fm API: ' . $e->getMessage());
        }

        return $artists;
    }

    /**
     * Obtener detalles de un artista específico
     */
    public function getArtistDetails($artistName)
    {
        if (!$this->enabled) {
            throw new Exception('Last.fm API no está habilitada');
        }

        try {
            // Obtener información general del artista
            $infoParams = [
                'method' => 'artist.getinfo',
                'artist' => $artistName,
                'api_key' => $this->apiKey,
                'format' => 'json'
            ];

            $infoResponse = $this->makeRequest($infoParams);
            $infoData = json_decode($infoResponse, true);

            if (!isset($infoData['artist'])) {
                throw new Exception('Artista no encontrado en Last.fm');
            }

            // Obtener top tracks
            $tracksParams = [
                'method' => 'artist.gettoptracks',
                'artist' => $artistName,
                'api_key' => $this->apiKey,
                'format' => 'json',
                'limit' => 10
            ];

            $tracksResponse = $this->makeRequest($tracksParams);
            $tracksData = json_decode($tracksResponse, true);

            return $this->formatArtistDetails($infoData['artist'], $tracksData);

        } catch (Exception $e) {
            throw new Exception('Error obteniendo detalles del artista: ' . $e->getMessage());
        }
    }

    /**
     * Formatear datos del artista desde la búsqueda
     */
    private function formatArtistData($item)
    {
        return [
            'id' => $item['mbid'] ?? $item['name'], // Usar MusicBrainz ID o nombre como ID
            'name' => $item['name'],
            'description' => '',
            'image' => $this->getBestImage($item['image'] ?? []),
            'url' => $item['url'] ?? '',
            'followers' => isset($item['listeners']) ? (int)$item['listeners'] : null,
            'popularity' => null, // Se calculará en getArtistDetails
            'genres' => [],
            'country' => null,
            'platform' => 'lastfm'
        ];
    }

    /**
     * Formatear detalles completos del artista
     */
    private function formatArtistDetails($artistInfo, $tracksData = null)
    {
        $stats = $artistInfo['stats'] ?? [];
        $tags = $artistInfo['tags']['tag'] ?? [];
        
        // Extraer géneros de los tags
        $genres = [];
        if (is_array($tags)) {
            foreach (array_slice($tags, 0, 5) as $tag) {
                if (is_array($tag) && isset($tag['name'])) {
                    $genres[] = $tag['name'];
                } elseif (is_string($tag)) {
                    $genres[] = $tag;
                }
            }
        }

        $listeners = isset($stats['listeners']) ? (int)$stats['listeners'] : 0;
        $playcount = isset($stats['playcount']) ? (int)$stats['playcount'] : 0;

        return [
            'id' => $artistInfo['mbid'] ?? $artistInfo['name'],
            'name' => $artistInfo['name'],
            'description' => isset($artistInfo['bio']['summary']) ? strip_tags($artistInfo['bio']['summary']) : '',
            'image' => $this->getBestImage($artistInfo['image'] ?? []),
            'url' => $artistInfo['url'] ?? '',
            'followers' => $listeners, // En Last.fm son "listeners"
            'playcount' => $playcount,
            'popularity' => $this->calculatePopularity($listeners, $playcount),
            'genres' => $genres,
            'country' => null,
            'platform' => 'lastfm',
            'similar_artists' => $this->extractSimilarArtists($artistInfo['similar']['artist'] ?? []),
            'top_tracks' => $this->extractTopTracks($tracksData['toptracks']['track'] ?? [])
        ];
    }

    /**
     * Obtener la mejor imagen disponible
     */
    private function getBestImage($images)
    {
        if (empty($images) || !is_array($images)) {
            return '';
        }

        // Buscar por tamaño preferido
        $sizes = ['extralarge', 'large', 'medium', 'small'];
        
        foreach ($sizes as $size) {
            foreach ($images as $img) {
                if (isset($img['size']) && $img['size'] === $size && !empty($img['#text'])) {
                    return $img['#text'];
                }
            }
        }

        // Si no encuentra por tamaño, devolver la primera disponible
        foreach ($images as $img) {
            if (!empty($img['#text'])) {
                return $img['#text'];
            }
        }

        return '';
    }

    /**
     * Calcular popularidad basada en listeners y playcount
     */
    private function calculatePopularity($listeners, $playcount)
    {
        if ($listeners === 0 && $playcount === 0) {
            return 0;
        }

        $score = 0;

        // Puntuación basada en listeners
        if ($listeners >= 1000000) $score += 40; // 1M+ listeners
        elseif ($listeners >= 500000) $score += 30; // 500K+ listeners
        elseif ($listeners >= 100000) $score += 20; // 100K+ listeners
        elseif ($listeners >= 50000) $score += 15; // 50K+ listeners
        elseif ($listeners >= 10000) $score += 10; // 10K+ listeners
        elseif ($listeners >= 1000) $score += 5; // 1K+ listeners

        // Puntuación basada en playcount
        if ($playcount >= 50000000) $score += 35; // 50M+ plays
        elseif ($playcount >= 10000000) $score += 25; // 10M+ plays
        elseif ($playcount >= 1000000) $score += 15; // 1M+ plays
        elseif ($playcount >= 100000) $score += 10; // 100K+ plays
        elseif ($playcount >= 10000) $score += 5; // 10K+ plays

        // Bonus por ratio playcount/listeners (engagement)
        if ($listeners > 0) {
            $ratio = $playcount / $listeners;
            if ($ratio >= 100) $score += 15; // Muy alto engagement
            elseif ($ratio >= 50) $score += 10; // Alto engagement
            elseif ($ratio >= 20) $score += 5; // Buen engagement
        }

        return min(100, $score);
    }

    /**
     * Extraer artistas similares
     */
    private function extractSimilarArtists($similarData)
    {
        $similar = [];
        
        if (is_array($similarData)) {
            foreach (array_slice($similarData, 0, 5) as $artist) {
                if (isset($artist['name'])) {
                    $similar[] = [
                        'name' => $artist['name'],
                        'url' => $artist['url'] ?? '',
                        'image' => $this->getBestImage($artist['image'] ?? [])
                    ];
                }
            }
        }
        
        return $similar;
    }

    /**
     * Extraer top tracks
     */
    private function extractTopTracks($tracksData)
    {
        $tracks = [];
        
        if (is_array($tracksData)) {
            foreach (array_slice($tracksData, 0, 10) as $track) {
                if (isset($track['name'])) {
                    $tracks[] = [
                        'name' => $track['name'],
                        'playcount' => isset($track['playcount']) ? (int)$track['playcount'] : 0,
                        'listeners' => isset($track['listeners']) ? (int)$track['listeners'] : 0,
                        'url' => $track['url'] ?? '',
                        'rank' => isset($track['@attr']['rank']) ? (int)$track['@attr']['rank'] : 0
                    ];
                }
            }
        }
        
        return $tracks;
    }

    /**
     * Realizar petición HTTP a la API
     */
    private function makeRequest($params)
    {
        $url = $this->baseUrl . '?' . http_build_query($params);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'TrackTraster Last.fm API Client/1.0'
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('No se pudo conectar con Last.fm API');
        }

        // Verificar si hay errores en la respuesta
        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new Exception('Last.fm API Error: ' . $data['message']);
        }

        return $response;
    }

    /**
     * Obtener top tracks de un artista
     */
    public function getArtistTopTracks($artistName, $country = 'all')
    {
        try {
            $params = [
                'method' => 'artist.gettoptracks',
                'artist' => $artistName,
                'api_key' => $this->apiKey,
                'format' => 'json',
                'limit' => 10
            ];

            $response = $this->makeRequest($params);
            $data = json_decode($response, true);

            $tracks = [];
            if (isset($data['toptracks']['track'])) {
                $trackList = $data['toptracks']['track'];
                
                // Si solo hay una canción, Last.fm la devuelve como objeto
                if (isset($trackList['name'])) {
                    $trackList = [$trackList];
                }

                foreach ($trackList as $track) {
                    $tracks[] = [
                        'id' => $track['mbid'] ?? $track['name'],
                        'name' => $track['name'],
                        'url' => $track['url'] ?? '',
                        'duration' => null,
                        'preview_url' => null,
                        'image' => $this->getBestImage($track['image'] ?? []),
                        'playcount' => isset($track['playcount']) ? (int)$track['playcount'] : 0,
                        'listeners' => isset($track['listeners']) ? (int)$track['listeners'] : 0
                    ];
                }
            }

            return $tracks;

        } catch (Exception $e) {
            throw new Exception('Error obteniendo top tracks: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de país específico (limitado en Last.fm)
     */
    public function getCountryStats($artistName, $country)
    {
        // Last.fm tiene datos limitados por país en la API gratuita
        // Retornamos datos básicos
        try {
            $details = $this->getArtistDetails($artistName);
            return [
                'artist' => $artistName,
                'country' => $country,
                'listeners' => $details['followers'] ?? 0,
                'playcount' => $details['playcount'] ?? 0,
                'note' => 'Datos globales de Last.fm (no específicos por país)'
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
