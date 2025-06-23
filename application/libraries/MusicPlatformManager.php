<?php
/**
 * MusicPlatformManager - Gestor de plataformas musicales
 * Centraliza las búsquedas y datos de múltiples plataformas
 */

class MusicPlatformManager
{
    private $platforms = [];
    private $config;
    private $cache = [];
    private $cacheExpiration = 3600; // 1 hora
    private $lastSearchQuery = ''; // Para algoritmo de relevancia

    public function __construct($config)
    {
        $this->config = $config;
        $this->initializePlatforms();
    }    private function initializePlatforms()
    {
        // Inicializar plataformas disponibles solo si están habilitadas
        if (isset($this->config['spotify']) && ($this->config['spotify']['enabled'] ?? true)) {
            try {
                require_once APPPATH . 'libraries/platforms/SpotifyAPI.php';
                $this->platforms['spotify'] = new SpotifyAPI($this->config['spotify']);
            } catch (Exception $e) {
                // Si falla Spotify, continuar sin él
                error_log("Failed to initialize Spotify API: " . $e->getMessage());
            }
        }

        if (isset($this->config['deezer']) && ($this->config['deezer']['enabled'] ?? true)) {
            try {
                require_once APPPATH . 'libraries/platforms/DeezerAPI.php';
                $this->platforms['deezer'] = new DeezerAPI($this->config['deezer']);
            } catch (Exception $e) {
                error_log("Failed to initialize Deezer API: " . $e->getMessage());
            }
        }

        if (isset($this->config['lastfm']) && ($this->config['lastfm']['enabled'] ?? false)) {
            try {
                require_once APPPATH . 'libraries/platforms/LastfmAPI.php';
                $this->platforms['lastfm'] = new LastfmAPI($this->config['lastfm']);
            } catch (Exception $e) {
                error_log("Failed to initialize Last.fm API: " . $e->getMessage());
            }
        }

        if (isset($this->config['soundcloud']) && ($this->config['soundcloud']['enabled'] ?? false)) {
            try {
                require_once APPPATH . 'libraries/platforms/SoundCloudAPI.php';
                $this->platforms['soundcloud'] = new SoundCloudAPI($this->config['soundcloud']);
            } catch (Exception $e) {
                error_log("Failed to initialize SoundCloud API: " . $e->getMessage());
            }
        }        // YouTube Music - habilitado para búsquedas de artistas
        if (isset($this->config['youtube_music']) && ($this->config['youtube_music']['enabled'] ?? false)) {
            try {
                require_once APPPATH . 'libraries/platforms/YouTubeMusicAPI.php';
                $this->platforms['youtube_music'] = new YouTubeMusicAPI($this->config['youtube_music']);
            } catch (Exception $e) {
                error_log("Failed to initialize YouTube Music API: " . $e->getMessage());
            }
        }
    }/**
     * Buscar artistas en todas las plataformas
     */
    public function searchArtists($query, $country = 'all', $limit = 20)
    {
        $this->lastSearchQuery = $query; // Guardar para algoritmo de relevancia
        
        $cacheKey = "search_artists_" . md5($query . $country . $limit);
        
        // Verificar caché
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $results = [
            'query' => $query,
            'country' => $country,
            'total_results' => 0,
            'platforms' => [],
            'combined_results' => []
        ];

        $allArtists = [];

        foreach ($this->platforms as $platformName => $platform) {
            try {
                $platformResults = $platform->searchArtists($query, $country, $limit);
                
                $results['platforms'][$platformName] = [
                    'status' => 'success',
                    'count' => count($platformResults),
                    'results' => $platformResults
                ];

                // Agregar al conjunto combinado
                foreach ($platformResults as $artist) {
                    $artist['source_platform'] = $platformName;
                    $allArtists[] = $artist;
                }

            } catch (Exception $e) {
                $results['platforms'][$platformName] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'count' => 0,
                    'results' => []
                ];
            }
        }

        // Combinar y eliminar duplicados por nombre
        $results['combined_results'] = $this->mergeDuplicateArtists($allArtists);
        $results['total_results'] = count($results['combined_results']);

        // Guardar en caché
        $this->cache[$cacheKey] = $results;

        return $results;
    }

    /**
     * Obtener información detallada de un artista desde todas las plataformas
     */
    public function getArtistDetails($artistId, $platform = null)
    {
        if ($platform && isset($this->platforms[$platform])) {
            return $this->platforms[$platform]->getArtistDetails($artistId);
        }

        $details = [
            'artist_id' => $artistId,
            'platforms' => []
        ];

        foreach ($this->platforms as $platformName => $platformInstance) {
            try {
                $platformDetails = $platformInstance->getArtistDetails($artistId);
                $details['platforms'][$platformName] = $platformDetails;
            } catch (Exception $e) {
                $details['platforms'][$platformName] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return $details;
    }

    /**
     * Obtener métricas de un artista (streams, seguidores, etc.)
     */
    public function getArtistMetrics($artistId, $platform = null, $country = 'all')
    {
        $metrics = [
            'artist_id' => $artistId,
            'country' => $country,
            'timestamp' => date('Y-m-d H:i:s'),
            'platforms' => []
        ];

        $platforms = $platform ? [$platform => $this->platforms[$platform]] : $this->platforms;

        foreach ($platforms as $platformName => $platformInstance) {
            try {
                $platformMetrics = $platformInstance->getArtistMetrics($artistId, $country);
                $metrics['platforms'][$platformName] = $platformMetrics;
            } catch (Exception $e) {
                $metrics['platforms'][$platformName] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return $metrics;
    }

    /**
     * Obtener top tracks de un artista
     */
    public function getTopTracks($artistId, $platform = null, $country = 'all', $limit = 10)
    {
        $tracks = [
            'artist_id' => $artistId,
            'country' => $country,
            'platforms' => []
        ];

        $platforms = $platform ? [$platform => $this->platforms[$platform]] : $this->platforms;

        foreach ($platforms as $platformName => $platformInstance) {
            try {
                $platformTracks = $platformInstance->getTopTracks($artistId, $country, $limit);
                $tracks['platforms'][$platformName] = $platformTracks;
            } catch (Exception $e) {
                $tracks['platforms'][$platformName] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return $tracks;
    }    /**
     * Combinar artistas duplicados basado en similitud de nombres
     */
    private function mergeDuplicateArtists($artists)
    {
        $merged = [];
        $processed = [];

        foreach ($artists as $artist) {
            $artistName = strtolower(trim($artist['name']));
            $found = false;

            foreach ($merged as &$mergedArtist) {
                $mergedName = strtolower(trim($mergedArtist['name']));
                
                // Verificar similitud (nombre exacto o muy similar)
                if ($artistName === $mergedName || $this->calculateSimilarity($artistName, $mergedName) > 0.8) {
                    // Fusionar información de plataformas
                    if (!isset($mergedArtist['platforms'])) {
                        $mergedArtist['platforms'] = [];
                    }
                    
                    $mergedArtist['platforms'][$artist['source_platform']] = [
                        'id' => $artist['id'],
                        'url' => $artist['url'] ?? null,
                        'followers' => $artist['followers'] ?? null,
                        'popularity' => $artist['popularity'] ?? null
                    ];
                    
                    // Actualizar datos principales si la nueva fuente tiene mejor información
                    if (!empty($artist['image']) && empty($mergedArtist['image'])) {
                        $mergedArtist['image'] = $artist['image'];
                    }
                    
                    if (!empty($artist['genres']) && empty($mergedArtist['genres'])) {
                        $mergedArtist['genres'] = $artist['genres'];
                    }
                    
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $artist['platforms'] = [
                    $artist['source_platform'] => [
                        'id' => $artist['id'],
                        'url' => $artist['url'] ?? null,
                        'followers' => $artist['followers'] ?? null,
                        'popularity' => $artist['popularity'] ?? null
                    ]
                ];
                unset($artist['source_platform']);
                $merged[] = $artist;
            }
        }

        // NUEVA LÓGICA DE ORDENAMIENTO: Relevancia + Popularidad
        return $this->sortByRelevanceAndPopularity($merged, $this->lastSearchQuery ?? '');
    }

    /**
     * Ordenar resultados por relevancia del nombre y popularidad
     */
    private function sortByRelevanceAndPopularity($artists, $searchQuery)
    {
        $searchQuery = strtolower(trim($searchQuery));
        
        usort($artists, function($a, $b) use ($searchQuery) {
            $nameA = strtolower(trim($a['name']));
            $nameB = strtolower(trim($b['name']));
            
            // 1. Prioridad: Coincidencia exacta
            $exactMatchA = ($nameA === $searchQuery) ? 1 : 0;
            $exactMatchB = ($nameB === $searchQuery) ? 1 : 0;
            
            if ($exactMatchA !== $exactMatchB) {
                return $exactMatchB - $exactMatchA;
            }
            
            // 2. Prioridad: Comienza con el término de búsqueda
            $startsWithA = strpos($nameA, $searchQuery) === 0 ? 1 : 0;
            $startsWithB = strpos($nameB, $searchQuery) === 0 ? 1 : 0;
            
            if ($startsWithA !== $startsWithB) {
                return $startsWithB - $startsWithA;
            }
            
            // 3. Prioridad: Contiene el término de búsqueda
            $containsA = strpos($nameA, $searchQuery) !== false ? 1 : 0;
            $containsB = strpos($nameB, $searchQuery) !== false ? 1 : 0;
            
            if ($containsA !== $containsB) {
                return $containsB - $containsA;
            }
            
            // 4. Prioridad: Similaridad del nombre (mayor peso)
            $similarityA = $this->calculateSimilarity($nameA, $searchQuery);
            $similarityB = $this->calculateSimilarity($nameB, $searchQuery);
            
            if (abs($similarityA - $similarityB) > 0.1) {
                return ($similarityB - $similarityA) * 100;
            }
            
            // 5. Desempate: Popularidad total
            $popularityA = 0;
            $popularityB = 0;
            
            foreach ($a['platforms'] as $platform) {
                $popularityA += $platform['popularity'] ?? 0;
            }
            
            foreach ($b['platforms'] as $platform) {
                $popularityB += $platform['popularity'] ?? 0;
            }
            
            return $popularityB - $popularityA;
        });        return $artists;
    }

    /**
     * Calcular similitud entre dos strings
     */
    private function calculateSimilarity($str1, $str2)
    {
        return 1 - (levenshtein($str1, $str2) / max(strlen($str1), strlen($str2)));
    }    /**
     * Obtener plataformas disponibles con información detallada
     */
    public function getAvailablePlatforms()
    {
        $available = [];
        $platformNames = [
            'spotify' => 'Spotify',
            'deezer' => 'Deezer',
            'lastfm' => 'Last.fm',
            'soundcloud' => 'SoundCloud'
        ];

        foreach ($platformNames as $key => $name) {
            $available[$key] = [
                'name' => $name,
                'enabled' => isset($this->platforms[$key]),
                'configured' => isset($this->config[$key]) && ($this->config[$key]['enabled'] ?? false)
            ];
        }

        return $available;
    }

    /**
     * Obtener lista simple de nombres de plataformas disponibles
     */
    public function getAvailablePlatformNames()
    {
        return array_keys($this->platforms);
    }

    /**
     * Obtener solo las claves de plataformas disponibles (compatibilidad)
     */
    public function getAvailablePlatformKeys()
    {
        return array_keys($this->platforms);
    }

    /**
     * Limpiar caché
     */
    public function clearCache()
    {
        $this->cache = [];
    }    /**
     * Buscar artistas en plataformas específicas
     */
    public function searchArtistsInPlatforms($query, $selectedPlatforms = ['all'], $limit = 20)
    {
        $this->lastSearchQuery = $query; // Guardar para algoritmo de relevancia
        
        // Si se selecciona "all" o el array está vacío, buscar en todas las plataformas
        if (in_array('all', $selectedPlatforms) || empty($selectedPlatforms)) {
            return $this->searchArtists($query, 'all', $limit);
        }

        $cacheKey = "search_platforms_" . md5($query . implode(',', $selectedPlatforms) . $limit);
        
        // Verificar caché
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $results = [
            'query' => $query,
            'selected_platforms' => $selectedPlatforms,
            'total_results' => 0,
            'platforms' => [],
            'combined_results' => []
        ];

        $allArtists = [];

        // Buscar solo en las plataformas seleccionadas
        foreach ($selectedPlatforms as $platformName) {
            if (!isset($this->platforms[$platformName])) {
                $results['platforms'][$platformName] = [
                    'status' => 'error',
                    'error' => 'Plataforma no disponible',
                    'count' => 0,
                    'results' => []
                ];
                continue;
            }

            $platform = $this->platforms[$platformName];
            
            try {
                $platformResults = $platform->searchArtists($query, 'all', $limit);
                
                $results['platforms'][$platformName] = [
                    'status' => 'success',
                    'count' => count($platformResults),
                    'results' => $platformResults
                ];

                // Agregar al conjunto combinado
                foreach ($platformResults as $artist) {
                    $artist['source_platform'] = $platformName;
                    $allArtists[] = $artist;
                }

            } catch (Exception $e) {
                $results['platforms'][$platformName] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'count' => 0,
                    'results' => []
                ];
            }
        }

        // Combinar y eliminar duplicados por nombre
        $results['combined_results'] = $this->mergeDuplicateArtists($allArtists);
        $results['total_results'] = count($results['combined_results']);

        // Guardar en caché
        $this->cache[$cacheKey] = $results;

        return $results;
    }

    /**
     * Obtener métricas combinadas de todas las plataformas para un artista
     */
    public function getArtistCombinedMetrics($artistName, $countryCode = null)
    {
        $metrics = [
            'total_followers' => 0,
            'avg_popularity' => 0,
            'platforms_data' => [],
            'platforms_count' => 0,
            'last_updated' => date('Y-m-d H:i:s')
        ];

        $popularityValues = [];
        $totalPlatforms = 0;

        foreach ($this->platforms as $platformName => $platform) {
            try {
                // Buscar el artista en esta plataforma
                $searchResults = $platform->searchArtists($artistName, $countryCode ?? 'all', 5);
                
                foreach ($searchResults as $artist) {
                    // Verificar si es una coincidencia exacta o muy similar
                    if ($this->calculateSimilarity(strtolower($artistName), strtolower($artist['name'])) > 0.85) {
                        $metrics['platforms_data'][$platformName] = [
                            'name' => $artist['name'],
                            'followers' => $artist['followers'] ?? 0,
                            'popularity' => $artist['popularity'] ?? 0,
                            'url' => $artist['url'] ?? null,
                            'image' => $artist['image'] ?? null,
                            'status' => 'found'
                        ];

                        if ($artist['followers'] ?? 0 > 0) {
                            $metrics['total_followers'] += $artist['followers'];
                        }

                        if ($artist['popularity'] ?? 0 > 0) {
                            $popularityValues[] = $artist['popularity'];
                        }

                        $totalPlatforms++;
                        break; // Solo tomar el primer resultado que coincida
                    }
                }

                if (!isset($metrics['platforms_data'][$platformName])) {
                    $metrics['platforms_data'][$platformName] = [
                        'status' => 'not_found'
                    ];
                }

            } catch (Exception $e) {
                $metrics['platforms_data'][$platformName] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Calcular popularidad promedio
        if (!empty($popularityValues)) {
            $metrics['avg_popularity'] = round(array_sum($popularityValues) / count($popularityValues), 1);
        }

        $metrics['platforms_count'] = $totalPlatforms;

        return $metrics;
    }

    /**
     * Obtener datos de seguimiento histórico para un artista
     */
    public function getArtistTrackingData($artistId, $countryCode, $startDate = null)
    {
        // Este método podría implementarse para obtener datos históricos
        // Por ahora retornamos datos de muestra
        return [
            'artist_id' => $artistId,
            'country_code' => $countryCode,
            'tracking_period' => [
                'start' => $startDate ?? date('Y-m-d', strtotime('-30 days')),
                'end' => date('Y-m-d')
            ],
            'metrics_history' => [],
            'platforms_available' => array_keys($this->platforms)
        ];
    }

    // ...existing code...
}
