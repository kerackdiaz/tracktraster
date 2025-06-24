<?php ob_start(); ?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-chart-area"></i>
            </div>
            <div class="stats-content">
                <h3><?= $trackings_count ?></h3>
                <p>Seguimientos Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stats-content">
                <h3><?= $alerts_count ?></h3>
                <p>Alertas Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-music"></i>
            </div>
            <div class="stats-content">
                <h3>0</h3>
                <p>Artistas Registrados</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stats-content">
                <h3>0%</h3>
                <p>Crecimiento Promedio</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/' ?>artists/search" class="quick-action-btn">
                            <i class="fas fa-search"></i>
                            <span>Buscar Artista</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/' ?>trackings/create" class="quick-action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Seguimiento</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/' ?>reports/generate" class="quick-action-btn">
                            <i class="fas fa-file-export"></i>
                            <span>Generar Reporte</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/' ?>analytics" class="quick-action-btn">
                            <i class="fas fa-chart-pie"></i>
                            <span>Ver Analíticas</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/' ?>analytics/systemDiagnostic?debug_key=diagnostic_2025" class="quick-action-btn" target="_blank">
                            <i class="fas fa-stethoscope"></i>
                            <span>Diagnóstico Sistema</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-card">
            <div class="welcome-content">
                <h2>¡Bienvenido, <?= htmlspecialchars($user['full_name']) ?>!</h2>
                <p>Empezemos a trackear el crecimiento de tus artistas. Puedes comenzar buscando un artista y creando tu primer seguimiento.</p>
                <div class="welcome-actions">
                    <a href="<?= $base_url ?? '/' ?>artists/search" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar Artista
                    </a>
                    <a href="<?= $base_url ?? '/' ?>trackings" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Ver Seguimientos
                    </a>
                </div>
            </div>
            <div class="welcome-graphic">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($upcoming_events)): ?>
<!-- Próximos Eventos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt text-warning"></i> Próximos Eventos
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="col-lg-6 mb-3">
                        <div class="upcoming-event-card">
                            <div class="event-artist">
                                <?php if ($event['image_url']): ?>
                                <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($event['artist_name']) ?>"
                                     class="artist-thumb">
                                <?php else: ?>
                                <div class="artist-thumb-placeholder">
                                    <i class="fas fa-music"></i>
                                </div>
                                <?php endif; ?>
                                <div class="event-info">
                                    <h6 class="mb-1"><?= htmlspecialchars($event['artist_name']) ?></h6>
                                    <div class="event-name"><?= htmlspecialchars($event['event_name']) ?></div>
                                    <?php if ($event['event_city']): ?>
                                    <div class="event-location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?= htmlspecialchars($event['event_city']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="event-countdown">
                                <?php if ($event['days_to_event'] == 0): ?>
                                <div class="countdown-today">¡HOY!</div>
                                <?php elseif ($event['days_to_event'] == 1): ?>
                                <div class="countdown-tomorrow">Mañana</div>
                                <?php else: ?>
                                <div class="countdown-days"><?= $event['days_to_event'] ?></div>
                                <div class="countdown-label">días</div>
                                <?php endif; ?>
                                <div class="event-date">
                                    <?= date('d/m/Y', strtotime($event['event_date'])) ?>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a href="<?= $base_url ?? '/' ?>analytics?artist_id=<?= $event['artist_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-chart-line"></i> Analytics
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-activity"></i> Actividad Reciente
                </h5>
            </div>
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-chart-area"></i>
                    <h6>No hay seguimientos activos</h6>
                    <p>Comienza creando tu primer seguimiento de artista para ver la actividad aquí.</p>
                    <a href="<?= $base_url ?? '/' ?>artists/search" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Crear Seguimiento
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell"></i> Notificaciones
                </h5>
            </div>
            <div class="card-body">
                <div class="empty-state small">
                    <i class="fas fa-bell-slash"></i>
                    <h6>Sin notificaciones</h6>
                    <p>Las alertas y notificaciones aparecerán aquí.</p>
                </div>
            </div>
        </div>    </div>
</div>

<style>
.upcoming-event-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.upcoming-event-card:hover {
    background: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.event-artist {
    display: flex;
    align-items: center;
    flex: 1;
}

.artist-thumb {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 1rem;
}

.artist-thumb-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #6c757d;
}

.event-info h6 {
    margin: 0;
    color: #2c3e50;
}

.event-name {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.event-location {
    color: #6c757d;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.event-countdown {
    text-align: center;
    margin: 0 1rem;
}

.countdown-today {
    font-size: 1.2rem;
    font-weight: bold;
    color: #dc3545;
    animation: pulse 2s infinite;
}

.countdown-tomorrow {
    font-size: 1.1rem;
    font-weight: bold;
    color: #fd7e14;
}

.countdown-days {
    font-size: 1.5rem;
    font-weight: bold;
    color: #0d6efd;
    line-height: 1;
}

.countdown-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
}

.event-date {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

<?php 
$content = ob_get_clean();
$base_url = '/';
$page_title = 'Dashboard';
$active_menu = 'dashboard';
include APPPATH . 'views/layouts/dashboard.php';
?>
