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
                        <a href="<?= $base_url ?? '/tracktraster/' ?>artists/search" class="quick-action-btn">
                            <i class="fas fa-search"></i>
                            <span>Buscar Artista</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/tracktraster/' ?>trackings/create" class="quick-action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Seguimiento</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/tracktraster/' ?>reports/generate" class="quick-action-btn">
                            <i class="fas fa-file-export"></i>
                            <span>Generar Reporte</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= $base_url ?? '/tracktraster/' ?>analytics" class="quick-action-btn">
                            <i class="fas fa-chart-pie"></i>
                            <span>Ver Analíticas</span>
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
                    <a href="<?= $base_url ?? '/tracktraster/' ?>artists/search" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar Artista
                    </a>
                    <a href="<?= $base_url ?? '/tracktraster/' ?>trackings" class="btn btn-outline-primary">
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
                    <a href="<?= $base_url ?? '/tracktraster/' ?>artists/search" class="btn btn-primary btn-sm">
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
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$base_url = '/tracktraster/';
$page_title = 'Dashboard';
$active_menu = 'dashboard';
include APPPATH . 'views/layouts/dashboard.php';
?>
