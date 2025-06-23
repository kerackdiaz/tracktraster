<?php
/**
 * SoundCloud API Integration
 * Proporciona datos de artistas, tracks y seguidores
 */

class SoundCloudAPI
{
    private $clientId;
    private $baseUrl = 'https://api.soundcloud.com';
    private $enabled;

    public function __construct($config)
    {
        $this->clientId = $config['client_id'] ?? '';
        $this->enabled = $config['enabled'] ?? false;
        
        if (!$this->enabled) {
            throw new Exception('SoundCloud API no está habilitada');
        }
        
        if (empty($this->clientId)) {
            throw new Exception('SoundCloud Client ID no configurado');
        }
    }

    /**
     * Buscar artistas en SoundCloud
     */
    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        if (!$this->enabled) {
            throw new Exception('SoundCloud API no está habilitada');
        }

        $artists = [];
        
        try {
            $searchUrl = $this->baseUrl . '/users?' . http_build_query([
                'q' => $query,
                'client_id' => $this->clientId,
                'limit' => $limit
            ]);

            $response = $this->makeRequest($searchUrl);
            $data = json_decode($response, true);

            if (is_array($data)) {
                foreach ($data as $user) {
                    $artist = $this->formatArtistData($user);
                    if ($artist && $this->isLikelyArtist($user)) {
                        $artists[] = $artist;
                    }
                }
            }

        } catch (Exception $e) {
            throw new Exception('Error en SoundCloud API: ' . $e->getMessage());
        }

        return $artists;
    }

    /**
     * Obtener detalles de un artista específico
     */
    public function getArtistDetails($userId)
    {
        if (!$this->enabled) {
            throw new Exception('SoundCloud API no está habilitada');
        }

        try {
            // Obtener información del usuario
            $userUrl = $this->baseUrl . '/users/' . $userId . '?' . http_build_query([
                'client_id' => $this->clientId
            ]);

            $userResponse = $this->makeRequest($userUrl);
            $userData = json_decode($userResponse, true);

            if (!$userData) {
                throw new Exception('Usuario no encontrado en SoundCloud');
            }

            // Obtener tracks del usuario
            $tracksUrl = $this->baseUrl . '/users/' . $userId . '/tracks?' . http_build_query([
                'client_id' => $this->clientId,
                'limit' => 10
            ]);

            $tracksResponse = $this->makeRequest($tracksUrl);
            $tracksData = json_decode($tracksResponse, true);

            return $this->formatArtistDetails($userData, $tracksData);

        } catch (Exception $e) {
            throw new Exception('Error obteniendo detalles del artista: ' . $e->getMessage());
        }
    }

    /**
     * Formatear datos del artista desde la búsqueda
     */
    private function formatArtistData($user)
    {
        return [
            'id' => $user['id'],
            'name' => $user['username'] ?? $user['full_name'] ?? 'Unknown',
            'description' => $user['description'] ?? '',
            'image' => $user['avatar_url'] ?? '',
            'url' => $user['permalink_url'] ?? '',
            'followers' => $user['followers_count'] ?? 0,
            'popularity' => $this->calculatePopularity($user),
            'genres' => [],
            'country' => $user['country'] ?? null,
            'platform' => 'soundcloud'
        ];
    }

    /**
     * Formatear detalles completos del artista
     */
    private function formatArtistDetails($userData, $tracksData = [])
    {
        return [
            'id' => $userData['id'],
            'name' => $userData['username'] ?? $userData['full_name'] ?? 'Unknown',
            'description' => $userData['description'] ?? '',
            'image' => $userData['avatar_url'] ?? '',
            'url' => $userData['permalink_url'] ?? '',
            'followers' => $userData['followers_count'] ?? 0,
            'following' => $userData['followings_count'] ?? 0,
            'track_count' => $userData['track_count'] ?? 0,
            'playlist_count' => $userData['playlist_count'] ?? 0,
            'popularity' => $this->calculatePopularity($userData),
            'genres' => $this->extractGenresFromTracks($tracksData),
            'country' => $userData['country'] ?? null,
            'city' => $userData['city'] ?? null,
            'platform' => 'soundcloud',
            'verified' => $userData['verified'] ?? false,
            'created_at' => $userData['created_at'] ?? null,
            'top_tracks' => $this->formatTracks($tracksData)
        ];
    }

    /**
     * Determinar si un usuario parece ser un artista
     */
    private function isLikelyArtist($user)
    {
        $trackCount = $user['track_count'] ?? 0;
        $followers = $user['followers_count'] ?? 0;
        $following = $user['followings_count'] ?? 0;
        
        // Criterios para determinar si es probable que sea un artista:
        // 1. Tiene al menos algunas canciones
        // 2. Tiene más seguidores que seguidos (ratio de artista)
        // 3. Tiene una cantidad razonable de seguidores
        
        if ($trackCount === 0) return false;
        if ($followers < 10) return false;
        
        // Si tiene muchos seguidores, probablemente es artista
        if ($followers >= 1000) return true;
        
        // Si sigue a muy poca gente comparado con sus seguidores
        if ($following > 0 && ($followers / $following) > 2) return true;
        
        // Si tiene muchas canciones
        if ($trackCount >= 5) return true;
        
        return false;
    }

    /**
     * Calcular popularidad basada en métricas de SoundCloud
     */
    private function calculatePopularity($user)
    {
        $followers = $user['followers_count'] ?? 0;
        $tracks = $user['track_count'] ?? 0;
        $playlists = $user['playlist_count'] ?? 0;
        
        $score = 0;

        // Puntuación basada en seguidores
        if ($followers >= 1000000) $score += 40; // 1M+
        elseif ($followers >= 100000) $score += 30; // 100K+
        elseif ($followers >= 10000) $score += 20; // 10K+
        elseif ($followers >= 1000) $score += 10; // 1K+
        elseif ($followers >= 100) $score += 5; // 100+

        // Puntuación basada en contenido
        if ($tracks >= 100) $score += 20;
        elseif ($tracks >= 50) $score += 15;
        elseif ($tracks >= 20) $score += 10;
        elseif ($tracks >= 10) $score += 5;

        // Bonus por playlists (muestra actividad)
        if ($playlists >= 10) $score += 10;
        elseif ($playlists >= 5) $score += 5;

        // Bonus por verificación
        if ($user['verified'] ?? false) $score += 15;

        return min(100, $score);
    }

    /**
     * Extraer géneros de los tracks del artista
     */
    private function extractGenresFromTracks($tracks)
    {
        $genres = [];
        
        if (is_array($tracks)) {
            foreach ($tracks as $track) {
                if (isset($track['genre']) && !empty($track['genre'])) {
                    $genre = trim($track['genre']);
                    if (!in_array($genre, $genres)) {
                        $genres[] = $genre;
                    }
                }
                
                // Límite de géneros únicos
                if (count($genres) >= 5) break;
            }
        }
        
        return $genres;
    }

    /**
     * Formatear tracks para la respuesta
     */
    private function formatTracks($tracks)
    {
        $formatted = [];
        
        if (is_array($tracks)) {
            foreach (array_slice($tracks, 0, 10) as $track) {
                $formatted[] = [
                    'id' => $track['id'],
                    'name' => $track['title'] ?? 'Unknown',
                    'url' => $track['permalink_url'] ?? '',
                    'duration' => $track['duration'] ?? null,
                    'preview_url' => isset($track['stream_url']) ? $track['stream_url'] . '?client_id=' . $this->clientId : null,
                    'image' => $track['artwork_url'] ?? '',
                    'playcount' => $track['playback_count'] ?? 0,
                    'likes' => $track['likes_count'] ?? 0,
                    'created_at' => $track['created_at'] ?? null
                ];
            }
        }
        
        return $formatted;
    }

    /**
     * Realizar petición HTTP a la API
     */
    private function makeRequest($url)
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'TrackTraster SoundCloud API Client/1.0'
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('No se pudo conectar con SoundCloud API');
        }

        return $response;
    }

    /**
     * Obtener top tracks de un artista
     */
    public function getArtistTopTracks($userId, $country = 'all')
    {
        try {
            $tracksUrl = $this->baseUrl . '/users/' . $userId . '/tracks?' . http_build_query([
                'client_id' => $this->clientId,
                'limit' => 10
            ]);

            $response = $this->makeRequest($tracksUrl);
            $data = json_decode($response, true);

            return $this->formatTracks($data);

        } catch (Exception $e) {
            throw new Exception('Error obteniendo top tracks: ' . $e->getMessage());
        }
    }
}
