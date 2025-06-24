<?php
/**
 * TrackingLifecycleService - Maneja el ciclo de vida del seguimiento hasta el evento
 */

class TrackingLifecycleService
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Actualizar el estado de todos los trackings según fechas
     */
    public function updateTrackingStatuses()
    {
        $today = date('Y-m-d');
        
        // 1. Marcar como 'completed' los eventos que ya pasaron
        $this->db->execute(
            "UPDATE artist_trackings 
             SET tracking_status = 'completed', status = 'completed', updated_at = NOW()
             WHERE event_date < ? AND tracking_status != 'completed'",
            [$today]
        );
        
        // 2. Marcar como 'ongoing' los que están en curso (evento en el futuro)
        $this->db->execute(
            "UPDATE artist_trackings 
             SET tracking_status = 'ongoing', updated_at = NOW()
             WHERE event_date >= ? AND tracking_start_date <= ? 
             AND tracking_status = 'pending' AND status = 'active'",
            [$today, $today]
        );
        
        // 3. Los que aún no han empezado quedan como 'pending'
        $this->db->execute(
            "UPDATE artist_trackings 
             SET tracking_status = 'pending', updated_at = NOW()
             WHERE tracking_start_date > ? AND tracking_status != 'pending'",
            [$today]
        );
        
        error_log("TrackingLifecycleService: Estados de tracking actualizados");
    }
    
    /**
     * Obtener información del ciclo de vida de un tracking
     */
    public function getTrackingLifecycle($trackingId)
    {
        $tracking = $this->db->fetchOne(
            "SELECT *, DATEDIFF(event_date, CURDATE()) as days_to_event,
                     DATEDIFF(CURDATE(), tracking_start_date) as days_tracking
             FROM artist_trackings WHERE id = ?",
            [$trackingId]
        );
        
        if (!$tracking) {
            return null;
        }
        
        $today = date('Y-m-d');
        $eventDate = $tracking['event_date'];
        $startDate = $tracking['tracking_start_date'];
        
        // Calcular información del timeline
        $lifecycle = [
            'tracking_id' => $trackingId,
            'event_name' => $tracking['event_name'],
            'event_date' => $eventDate,
            'event_city' => $tracking['event_city'],
            'event_venue' => $tracking['event_venue'],
            'tracking_start_date' => $startDate,
            'days_to_event' => (int)$tracking['days_to_event'],
            'days_tracking' => (int)$tracking['days_tracking'],
            'current_status' => $tracking['tracking_status'],
            'is_event_past' => $eventDate < $today,
            'is_tracking_active' => $startDate <= $today,
            'progress_percentage' => 0,
            'phase' => 'pre-tracking'
        ];
        
        // Calcular progreso y fase
        if ($eventDate && $startDate) {
            $totalDays = (strtotime($eventDate) - strtotime($startDate)) / (60 * 60 * 24);
            $daysPassed = (strtotime($today) - strtotime($startDate)) / (60 * 60 * 24);
            
            if ($totalDays > 0) {
                $lifecycle['progress_percentage'] = min(100, max(0, ($daysPassed / $totalDays) * 100));
            }
            
            // Determinar fase
            if ($today < $startDate) {
                $lifecycle['phase'] = 'pre-tracking';
            } elseif ($today > $eventDate) {
                $lifecycle['phase'] = 'post-event';
            } else {
                $daysLeft = $lifecycle['days_to_event'];
                if ($daysLeft > 30) {
                    $lifecycle['phase'] = 'early-tracking';
                } elseif ($daysLeft > 7) {
                    $lifecycle['phase'] = 'mid-tracking';
                } elseif ($daysLeft > 0) {
                    $lifecycle['phase'] = 'pre-event';
                } else {
                    $lifecycle['phase'] = 'event-day';
                }
            }
        }
        
        return $lifecycle;
    }
    
    /**
     * Obtener métricas adaptadas al ciclo de vida del evento
     */
    public function getEventContextualMetrics($trackingId)
    {
        $lifecycle = $this->getTrackingLifecycle($trackingId);
        
        if (!$lifecycle) {
            return null;
        }
        
        // Obtener métricas base
        $startMetrics = $this->getMetricsForDate($trackingId, $lifecycle['tracking_start_date']);
        $currentMetrics = $this->getLatestMetrics($trackingId);
        
        // Calcular crecimiento hacia el evento
        $eventMetrics = [
            'lifecycle' => $lifecycle,
            'start_metrics' => $startMetrics,
            'current_metrics' => $currentMetrics,
            'growth_towards_event' => [],
            'projected_event_metrics' => [],
            'recommendations' => []
        ];
          // Calcular crecimiento por plataforma
        if ($startMetrics && $currentMetrics) {
            foreach (['spotify', 'deezer', 'lastfm', 'youtube'] as $platform) {
                if (isset($startMetrics[$platform]) && isset($currentMetrics[$platform])) {
                    $start = $startMetrics[$platform];
                    $current = $currentMetrics[$platform];
                    
                    $growth = [];
                    if (isset($start['followers']) && isset($current['followers'])) {
                        $growth['followers_growth'] = $current['followers'] - $start['followers'];
                        $growth['followers_growth_percentage'] = $start['followers'] > 0 ? 
                            (($current['followers'] - $start['followers']) / $start['followers']) * 100 : 0;
                    }
                    
                    if (isset($start['popularity']) && isset($current['popularity'])) {
                        $growth['popularity_growth'] = $current['popularity'] - $start['popularity'];
                    }
                    
                    $eventMetrics['growth_towards_event'][$platform] = $growth;
                }
            }
        }
        
        // Generar recomendaciones según la fase
        $eventMetrics['recommendations'] = $this->generatePhaseRecommendations($lifecycle);
        
        // Agregar la información del lifecycle al resultado final
        $eventMetrics['lifecycle'] = $lifecycle;
        
        return $eventMetrics;
    }
    
    /**
     * Obtener métricas para una fecha específica
     */
    private function getMetricsForDate($trackingId, $date)
    {
        $metrics = [];
        
        // Spotify
        $spotify = $this->db->fetchOne(
            "SELECT * FROM spotify_metrics 
             WHERE tracking_id = ? AND metric_date = ?",
            [$trackingId, $date]
        );
        if ($spotify) {
            $metrics['spotify'] = $spotify;
        }
        
        // Deezer
        $deezer = $this->db->fetchOne(
            "SELECT * FROM deezer_metrics 
             WHERE tracking_id = ? AND metric_date = ?",
            [$trackingId, $date]
        );
        if ($deezer) {
            $metrics['deezer'] = $deezer;
        }
        
        // Last.fm
        $lastfm = $this->db->fetchOne(
            "SELECT * FROM lastfm_metrics 
             WHERE tracking_id = ? AND metric_date = ?",
            [$trackingId, $date]
        );
        if ($lastfm) {
            $metrics['lastfm'] = $lastfm;
        }
        
        // YouTube
        $youtube = $this->db->fetchOne(
            "SELECT * FROM youtube_metrics 
             WHERE tracking_id = ? AND metric_date = ?",
            [$trackingId, $date]
        );
        if ($youtube) {
            $metrics['youtube'] = $youtube;
        }
        
        return $metrics;
    }
    
    /**
     * Obtener métricas más recientes
     */
    private function getLatestMetrics($trackingId)
    {
        $metrics = [];
        
        // Spotify
        $spotify = $this->db->fetchOne(
            "SELECT * FROM spotify_metrics 
             WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 1",
            [$trackingId]
        );
        if ($spotify) {
            $metrics['spotify'] = $spotify;
        }
        
        // Deezer
        $deezer = $this->db->fetchOne(
            "SELECT * FROM deezer_metrics 
             WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 1",
            [$trackingId]
        );
        if ($deezer) {
            $metrics['deezer'] = $deezer;
        }
        
        // Last.fm
        $lastfm = $this->db->fetchOne(
            "SELECT * FROM lastfm_metrics 
             WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 1",
            [$trackingId]
        );
        if ($lastfm) {
            $metrics['lastfm'] = $lastfm;
        }
        
        // YouTube
        $youtube = $this->db->fetchOne(
            "SELECT * FROM youtube_metrics 
             WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 1",
            [$trackingId]
        );
        if ($youtube) {
            $metrics['youtube'] = $youtube;
        }
        
        return $metrics;
    }
    
    /**
     * Generar recomendaciones según la fase del evento
     */
    private function generatePhaseRecommendations($lifecycle)
    {
        $recommendations = [];
        
        switch ($lifecycle['phase']) {
            case 'pre-tracking':
                $recommendations[] = "El seguimiento aún no ha comenzado. Comenzará el " . date('d/m/Y', strtotime($lifecycle['tracking_start_date']));
                break;
                
            case 'early-tracking':
                $recommendations[] = "Estás en la fase inicial del seguimiento. Es momento de crear contenido promocional.";
                $recommendations[] = "Considera anunciar el evento en redes sociales para generar expectativa.";
                break;
                
            case 'mid-tracking':
                $recommendations[] = "Fase media del seguimiento. Intensifica la promoción del evento.";
                $recommendations[] = "Interactúa más con fans y considera colaboraciones.";
                break;
                
            case 'pre-event':
                $recommendations[] = "¡Última semana antes del evento! Máxima intensidad promocional.";
                $recommendations[] = "Recuerda a los fans sobre el evento y comparte detalles de último momento.";
                break;
                
            case 'event-day':
                $recommendations[] = "¡Es el día del evento! Documenta todo y comparte en tiempo real.";
                break;
                
            case 'post-event':
                $recommendations[] = "El evento ya pasó. Analiza el impacto y agradece a los asistentes.";
                $recommendations[] = "Comparte highlights y considera planificar el próximo evento.";
                break;
        }
        
        return $recommendations;
    }
}
?>
