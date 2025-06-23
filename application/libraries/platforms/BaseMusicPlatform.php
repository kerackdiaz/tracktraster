<?php
/**
 * BaseMusicPlatform - Clase base para todas las plataformas musicales
 */

abstract class BaseMusicPlatform
{
    protected $config;
    protected $apiKey;
    protected $baseUrl;
    protected $rateLimitDelay = 1; // segundos entre requests
    protected $lastRequestTime = 0;

    public function __construct($config)
    {
        $this->config = $config;
        $this->initialize();
    }

    /**
     * Inicializar configuración específica de la plataforma
     */
    abstract protected function initialize();

    /**
     * Buscar artistas en la plataforma
     */
    abstract public function searchArtists($query, $country = 'all', $limit = 20);

    /**
     * Obtener detalles de un artista
     */
    abstract public function getArtistDetails($artistId);

    /**
     * Obtener métricas de un artista
     */
    abstract public function getArtistMetrics($artistId, $country = 'all');

    /**
     * Obtener top tracks de un artista
     */
    abstract public function getTopTracks($artistId, $country = 'all', $limit = 10);

    /**
     * Realizar request HTTP con rate limiting
     */
    protected function makeRequest($url, $headers = [], $method = 'GET', $data = null)
    {
        // Rate limiting
        $timeSinceLastRequest = microtime(true) - $this->lastRequestTime;
        if ($timeSinceLastRequest < $this->rateLimitDelay) {
            usleep(($this->rateLimitDelay - $timeSinceLastRequest) * 1000000);
        }

        $ch = curl_init();
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'User-Agent: TrackTraster/1.0'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, // Para desarrollo local
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        $this->lastRequestTime = microtime(true);

        if ($error) {
            throw new Exception("CURL Error: $error");
        }

        if ($httpCode >= 400) {
            throw new Exception("HTTP Error $httpCode: $response");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Normalizar datos de artista para formato consistente
     */
    protected function normalizeArtistData($data)
    {
        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? null,
            'url' => $data['url'] ?? null,
            'followers' => $data['followers'] ?? null,
            'popularity' => $data['popularity'] ?? null,
            'genres' => $data['genres'] ?? [],
            'country' => $data['country'] ?? null,
            'verified' => $data['verified'] ?? false
        ];
    }

    /**
     * Normalizar datos de track
     */
    protected function normalizeTrackData($data)
    {
        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? '',
            'artist' => $data['artist'] ?? '',
            'album' => $data['album'] ?? '',
            'duration' => $data['duration'] ?? 0,
            'popularity' => $data['popularity'] ?? null,
            'preview_url' => $data['preview_url'] ?? null,
            'image' => $data['image'] ?? null,
            'url' => $data['url'] ?? null
        ];
    }

    /**
     * Mapear códigos de país a formato de la plataforma
     */
    protected function mapCountryCode($country)
    {
        $countryMap = [
            'all' => null,
            'AR' => 'AR', // Argentina
            'BO' => 'BO', // Bolivia
            'BR' => 'BR', // Brasil
            'CL' => 'CL', // Chile
            'CO' => 'CO', // Colombia
            'CR' => 'CR', // Costa Rica
            'CU' => 'CU', // Cuba
            'DO' => 'DO', // República Dominicana
            'EC' => 'EC', // Ecuador
            'SV' => 'SV', // El Salvador
            'GT' => 'GT', // Guatemala
            'HN' => 'HN', // Honduras
            'MX' => 'MX', // México
            'NI' => 'NI', // Nicaragua
            'PA' => 'PA', // Panamá
            'PY' => 'PY', // Paraguay
            'PE' => 'PE', // Perú
            'PR' => 'PR', // Puerto Rico
            'UY' => 'UY', // Uruguay
            'VE' => 'VE'  // Venezuela
        ];

        return $countryMap[$country] ?? $country;
    }

    /**
     * Log de errores de la plataforma
     */
    protected function logError($message, $context = [])
    {
        $logMessage = date('Y-m-d H:i:s') . " - " . get_class($this) . ": $message";
        if (!empty($context)) {
            $logMessage .= " - Context: " . json_encode($context);
        }
        
        // En desarrollo, mostrar en pantalla. En producción, guardar en archivo
        error_log($logMessage);
    }
}
?>
