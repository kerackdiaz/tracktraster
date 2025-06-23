<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
    </div>
</div>

<div class="row">
      <!-- Tarjetas de acceso rápido -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-server"></i> Estado del Sistema
                </h6>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Monitorea el estado de las APIs configuradas y ejecuta pruebas automáticas de conectividad.
                </p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-eye text-info"></i> Estado en tiempo real</li>
                    <li><i class="fas fa-flask text-info"></i> Pruebas automáticas</li>
                    <li><i class="fas fa-cog text-info"></i> Configuración .env</li>
                    <li><i class="fas fa-shield-alt text-info"></i> Gestión segura</li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-info">
                    <i class="fas fa-server"></i> Ver Estado
                </a>
            </div>
        </div>
    </div>    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Revisa la información técnica del sistema, versión de PHP, configuración y estado de archivos.
                </p>
                <div class="text-center">
                    <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                    <p class="mb-0">
                        <strong>Estado del Sistema</strong><br>
                        <span class="badge bg-success">Operativo</span>
                    </p>
                </div>
            </div>
            <div class="card-footer">                <a href="<?= ($base_url ?? '/') ?>admin/system_info" class="btn btn-warning">
                    <i class="fas fa-info-circle"></i> Ver Información
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </h6>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Revisa el uso de las APIs, métricas de rendimiento y estadísticas del sistema.
                </p>
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-primary">2</h5>
                        <small>APIs Activas</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success">98%</h5>
                        <small>Uptime</small>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-info" disabled>
                    <i class="fas fa-chart-bar"></i> Próximamente
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Panel de estado de plataformas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-music"></i> Estado de Plataformas Musicales
                </h5>
            </div>            <div class="card-body">
                <div class="alert alert-info">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle mt-1 me-2"></i>
                        <div>
                            <strong>Gestión Simplificada</strong>
                            <p class="mb-2">
                                Las credenciales se configuran directamente en el archivo <code>.env</code> por seguridad.
                            </p>
                            <p class="mb-0">
                                <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-sm btn-primary">
                                    <i class="fas fa-server"></i> Ver Estado Detallado
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Plataforma</th>
                                <th>Estado</th>
                                <th>Configuración</th>
                                <th>Última Prueba</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fab fa-spotify text-success"></i>
                                    <strong>Spotify</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">Activa</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Configurada</span>
                                </td>
                                <td>
                                    <small class="text-muted">Hace 5 min</small>
                                </td>
                                <td>
                                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas fa-music text-warning"></i>
                                    <strong>Deezer</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">Activa</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">API Pública</span>
                                </td>
                                <td>
                                    <small class="text-muted">Hace 5 min</small>
                                </td>
                                <td>
                                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fab fa-youtube text-danger"></i>
                                    <strong>YouTube Music</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">Activa</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Configurada</span>
                                </td>
                                <td>
                                    <small class="text-muted">Hace 5 min</small>
                                </td>
                                <td>
                                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fab fa-lastfm text-danger"></i>
                                    <strong>Last.fm</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">Activa</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Configurada</span>
                                </td>
                                <td>
                                    <small class="text-muted">Hace 5 min</small>
                                </td>
                                <td>
                                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información del sistema -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Versión TrackTraster:</dt>
                    <dd class="col-sm-6">v1.0.0</dd>
                    
                    <dt class="col-sm-6">PHP:</dt>
                    <dd class="col-sm-6"><?= PHP_VERSION ?></dd>
                    
                    <dt class="col-sm-6">Base de Datos:</dt>
                    <dd class="col-sm-6">MySQL</dd>
                    
                    <dt class="col-sm-6">Última Actualización:</dt>
                    <dd class="col-sm-6"><?= date('d/m/Y H:i') ?></dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools"></i> Acciones Rápidas
                </h6>
            </div>            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-outline-primary">
                        <i class="fas fa-server"></i> Ver Estado de APIs
                    </a>
                    <a href="<?= ($base_url ?? '/') ?>admin/system_info" class="btn btn-outline-info">
                        <i class="fas fa-info-circle"></i> Información del Sistema
                    </a>
                    <button class="btn btn-outline-success" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.border-primary:hover {
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.3);
}

.border-success:hover {
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
}

.border-info:hover {
    box-shadow: 0 0 20px rgba(23, 162, 184, 0.3);
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}
</style>

<?php 
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
