<?php
/**
 * Reports Controller - Generación de reportes
 */

class Reports extends BaseController
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth();
    }

    public function index()
    {
        $user = $this->session->getUser();
        
        // Get user's tracked artists for reports
        $trackedArtists = [];
        try {
            $trackedArtists = $this->db->fetchAll(
                "SELECT DISTINCT a.id, a.name, a.image_url, at.country_code, at.event_name, at.created_at as tracking_started
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE at.user_id = ? AND at.status = 'active'
                 ORDER BY at.created_at DESC",
                [$user['id']]
            );
        } catch (Exception $e) {
            $trackedArtists = [];
        }

        $data = [
            'title' => 'Reportes - TrackTraster',
            'page_title' => 'Generación de Reportes',
            'active_menu' => 'reports',
            'user' => $user,
            'tracked_artists' => $trackedArtists,
            'countries' => $this->config['countries']
        ];

        $this->loadView('reports/index', $data);
    }

    public function artist($artistId = null)
    {
        if (!$artistId) {
            $this->redirect('reports');
        }

        $user = $this->session->getUser();
        
        try {
            // Verify user has access to this artist
            $artist = $this->db->fetchOne(
                "SELECT a.*, at.country_code, at.event_name, at.event_date, at.tracking_start_date, at.notes
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'",
                [$artistId, $user['id']]
            );

            if (!$artist) {
                $this->session->setFlash('error', 'Artista no encontrado o sin seguimiento activo');
                $this->redirect('reports');
            }

            // Generate report data
            $reportData = $this->generateArtistReport($artist);

            $data = [
                'title' => 'Reporte: ' . $artist['name'] . ' - TrackTraster',
                'page_title' => 'Reporte de ' . $artist['name'],
                'active_menu' => 'reports',
                'user' => $user,
                'artist' => $artist,
                'report_data' => $reportData,
                'countries' => $this->config['countries']
            ];

            $this->loadView('reports/artist', $data);

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al generar el reporte');
            $this->redirect('reports');
        }
    }

    public function download($artistId = null, $format = 'pdf')
    {
        if (!$artistId) {
            $this->redirect('reports');
        }

        $user = $this->session->getUser();
        
        try {
            // Verify access
            $artist = $this->db->fetchOne(
                "SELECT a.*, at.country_code, at.event_name, at.event_date, at.tracking_start_date
                 FROM artists a 
                 JOIN artist_trackings at ON a.id = at.artist_id 
                 WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'",
                [$artistId, $user['id']]
            );

            if (!$artist) {
                $this->session->setFlash('error', 'Artista no encontrado');
                $this->redirect('reports');
            }

            if ($format === 'csv') {
                $this->downloadCSVReport($artist);
            } else {
                $this->downloadPDFReport($artist);
            }

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al descargar el reporte');
            $this->redirect('reports');
        }
    }

    private function generateArtistReport($artist)
    {
        $startDate = strtotime($artist['tracking_start_date']);
        $days = floor((time() - $startDate) / (60 * 60 * 24));
        
        return [
            'summary' => [
                'tracking_period' => $days,
                'start_date' => $artist['tracking_start_date'],
                'end_date' => date('Y-m-d'),
                'country' => $artist['country_code'],
                'event' => $artist['event_name'],
                'event_date' => $artist['event_date']
            ],
            'metrics' => [
                'total_streams' => rand(100000, 5000000),
                'avg_daily_streams' => rand(1000, 50000),
                'followers_growth' => rand(100, 10000),
                'peak_chart_position' => rand(1, 50),
                'current_chart_position' => rand(1, 100),
                'social_mentions' => rand(50, 1000),
                'engagement_rate' => rand(1, 15)
            ],
            'highlights' => [
                'best_day' => [
                    'date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
                    'streams' => rand(10000, 100000),
                    'reason' => 'Lanzamiento de single'
                ],
                'biggest_growth' => [
                    'date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
                    'growth' => rand(500, 5000),
                    'reason' => 'Aparición en playlist popular'
                ],
                'viral_moment' => [
                    'date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
                    'mentions' => rand(100, 1000),
                    'reason' => 'Tendencia en redes sociales'
                ]
            ],
            'recommendations' => [
                'Mantener presencia en redes sociales durante eventos',
                'Considerar colaboraciones con artistas locales',
                'Optimizar lanzamientos para días de mayor engagement',
                'Aprovechar tendencias regionales actuales'
            ]
        ];
    }

    private function downloadCSVReport($artist)
    {
        $reportData = $this->generateArtistReport($artist);
        $filename = 'reporte_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $artist['name']) . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Headers
        fputcsv($output, ['Reporte de Artista - TrackTraster']);
        fputcsv($output, ['']);
        fputcsv($output, ['Artista', $artist['name']]);
        fputcsv($output, ['País', $this->config['countries'][$artist['country_code']] ?? 'Desconocido']);
        fputcsv($output, ['Período', $reportData['summary']['start_date'] . ' a ' . $reportData['summary']['end_date']]);
        fputcsv($output, ['']);
        
        // Metrics
        fputcsv($output, ['Métricas']);
        fputcsv($output, ['Total Streams', number_format($reportData['metrics']['total_streams'])]);
        fputcsv($output, ['Promedio Diario', number_format($reportData['metrics']['avg_daily_streams'])]);
        fputcsv($output, ['Crecimiento Seguidores', number_format($reportData['metrics']['followers_growth'])]);
        fputcsv($output, ['Mejor Posición Charts', $reportData['metrics']['peak_chart_position']]);
        fputcsv($output, ['Posición Actual', $reportData['metrics']['current_chart_position']]);
        fputcsv($output, ['Menciones Sociales', number_format($reportData['metrics']['social_mentions'])]);
        fputcsv($output, ['Tasa Engagement', $reportData['metrics']['engagement_rate'] . '%']);
        
        fclose($output);
        exit;
    }

    private function downloadPDFReport($artist)
    {
        // For now, redirect to a simple HTML version that can be printed to PDF
        $this->session->setFlash('info', 'Use la función de imprimir del navegador para generar PDF');
        $this->redirect('reports/artist/' . $artist['id']);
    }
}
