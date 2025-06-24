<?php
/**
 * Dashboard Controller
 */

class Dashboard extends BaseController
{    public function __construct($config)
    {
        parent::__construct($config);
        $this->requireAuth(); // Require authentication for all dashboard methods
        
        // Initialize tracking lifecycle service
        try {
            require_once APPPATH . 'services/TrackingLifecycleService.php';
            $this->lifecycleService = new TrackingLifecycleService($this->db);
        } catch (Exception $e) {
            error_log("TrackingLifecycleService initialization error: " . $e->getMessage());
            $this->lifecycleService = null;
        }
    }    public function index()
    {
        $user = $this->session->getUser();
        
        // Update tracking statuses
        if ($this->lifecycleService) {
            try {
                $this->lifecycleService->updateTrackingStatuses();
            } catch (Exception $e) {
                error_log("Error updating tracking statuses: " . $e->getMessage());
            }
        }
        
        // Get user's trackings count and upcoming events
        $trackingsCount = 0;
        $upcomingEvents = [];
        try {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM artist_trackings WHERE user_id = ? AND status = 'active'",
                [$user['id']]
            );
            $trackingsCount = $result['count'] ?? 0;
            
            // Get upcoming events (next 30 days)
            $upcomingEvents = $this->db->fetchAll(
                "SELECT at.*, a.name as artist_name, a.image_url,
                        DATEDIFF(at.event_date, CURDATE()) as days_to_event
                 FROM artist_trackings at
                 JOIN artists a ON at.artist_id = a.id
                 WHERE at.user_id = ? AND at.status = 'active' 
                       AND at.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 ORDER BY at.event_date ASC
                 LIMIT 5",
                [$user['id']]
            );
            
        } catch (Exception $e) {
            // Table doesn't exist yet, that's ok
            $trackingsCount = 0;
            $upcomingEvents = [];
        }

        // Get recent alerts count (with error handling)
        $alertsCount = 0;
        try {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM alerts WHERE user_id = ? AND is_read = 0",
                [$user['id']]
            );
            $alertsCount = $result['count'] ?? 0;
        } catch (Exception $e) {
            // Table doesn't exist yet, that's ok
            $alertsCount = 0;
        }

        $data = [
            'title' => 'Dashboard - TrackTraster',
            'user' => $user,
            'trackings_count' => $trackingsCount,
            'alerts_count' => $alertsCount,
            'upcoming_events' => $upcomingEvents,
            'countries' => $this->config['countries']
        ];

        $this->loadView('dashboard/index', $data);
    }

    public function profile()
    {
        $user = $this->session->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile();
        } else {
            $this->showProfile();
        }
    }    private function showProfile()
    {
        $userId = $this->session->getUserId();
        
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );

        // Get user statistics
        $stats = [];
        try {
            $stats = [
                'total_trackings' => $this->db->fetchOne("SELECT COUNT(*) as count FROM artist_trackings WHERE user_id = ?", [$userId])['count'] ?? 0,
                'active_trackings' => $this->db->fetchOne("SELECT COUNT(*) as count FROM artist_trackings WHERE user_id = ? AND status = 'active'", [$userId])['count'] ?? 0,
                'days_registered' => floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24))
            ];
        } catch (Exception $e) {
            $stats = [
                'total_trackings' => 0,
                'active_trackings' => 0,
                'days_registered' => 0
            ];
        }

        // Get recent activity (mock data for now)
        $recent_activity = [
            [
                'icon' => 'plus',
                'description' => 'Nuevo seguimiento agregado',
                'time_ago' => 'Hace 2 horas'
            ],
            [
                'icon' => 'chart-line',
                'description' => 'Reporte generado exitosamente',
                'time_ago' => 'Hace 1 día'
            ],
            [
                'icon' => 'search',
                'description' => 'Búsqueda de artista realizada',
                'time_ago' => 'Hace 3 días'
            ]
        ];

        $data = [
            'title' => 'Mi Perfil - TrackTraster',
            'page_title' => 'Mi Perfil',
            'active_menu' => 'profile',
            'user' => $user,
            'stats' => $stats,
            'recent_activity' => $recent_activity,
            'countries' => $this->config['countries'],
            'csrf_token' => $this->generateCSRFToken(),
            'success' => $this->session->getFlash('success'),
            'error' => $this->session->getFlash('error')
        ];

        $this->loadView('dashboard/profile', $data);
    }

    private function updateProfile()
    {
        // Validate CSRF token
        if (!$this->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Token de seguridad inválido');
            $this->redirect('dashboard/profile');
        }

        $userId = $this->session->getUserId();
        $fullName = trim($_POST['full_name'] ?? '');
        $country = $_POST['country'] ?? '';
        $company = trim($_POST['company'] ?? '');

        // Validate input
        if (empty($fullName) || strlen($fullName) < 2) {
            $this->session->setFlash('error', 'El nombre completo es requerido (mínimo 2 caracteres)');
            $this->redirect('dashboard/profile');
        }

        if (empty($country) || !array_key_exists($country, $this->config['countries'])) {
            $this->session->setFlash('error', 'Selecciona un país válido');
            $this->redirect('dashboard/profile');
        }

        // Update user
        $result = $this->db->execute(
            "UPDATE users SET full_name = ?, country = ?, company = ?, updated_at = NOW() WHERE id = ?",
            [$fullName, $country, $company, $userId]
        );

        if ($result['affected_rows'] > 0) {
            // Update session data
            $_SESSION['user_name'] = $fullName;
            $this->session->setFlash('success', 'Perfil actualizado correctamente');
        } else {
            $this->session->setFlash('error', 'No se realizaron cambios');
        }

        $this->redirect('dashboard/profile');
    }
}
