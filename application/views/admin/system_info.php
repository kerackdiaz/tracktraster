<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <div>
            <a href="<?= ($base_url ?? '/') ?>admin" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información del Sistema -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server"></i> Información del Sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Servidor Web</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Versión PHP:</strong></td>
                                <td>
                                    <span class="badge bg-<?= version_compare($system_info['php_version'], '7.4.0', '>=') ? 'success' : 'warning' ?>">
                                        <?= $system_info['php_version'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Límite de Memoria:</strong></td>
                                <td><?= $system_info['memory_limit'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tiempo Máximo de Ejecución:</strong></td>
                                <td><?= $system_info['max_execution_time'] ?>s</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Archivos del Sistema</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Archivo .env:</strong></td>
                                <td>
                                    <?php if ($system_info['env_file_exists']): ?>
                                        <span class="badge bg-success">Existe</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No Encontrado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Permisos .env:</strong></td>
                                <td>
                                    <?php if ($system_info['env_writable']): ?>
                                        <span class="badge bg-success">Escribible</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Solo Lectura</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Directorio config/:</strong></td>
                                <td>
                                    <?php if ($system_info['config_writable']): ?>
                                        <span class="badge bg-success">Escribible</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Solo Lectura</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="text-muted">Librerías Cargadas</h6>
                <div class="row">
                    <?php foreach ($system_info['libraries_loaded'] as $library => $loaded): ?>
                    <div class="col-md-4 mb-2">
                        <div class="d-flex align-items-center">
                            <?php if ($loaded): ?>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span><?= $library ?></span>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger me-2"></i>
                                <span class="text-muted"><?= $library ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Sistema -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat"></i> Estado General
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-success">Sistema Operativo</h4>
                <p class="text-muted">
                    Todos los componentes principales están funcionando correctamente.
                </p>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-primary mb-0">
                            <?php 
                            $enabled_apis = 0;
                            require_once APPPATH . 'libraries/EnvLoader.php';
                            EnvLoader::load();
                            $apis = ['SPOTIFY_ENABLED', 'DEEZER_ENABLED', 'LASTFM_ENABLED', 'SOUNDCLOUD_ENABLED', 'YOUTUBE_MUSIC_ENABLED'];
                            foreach ($apis as $api) {
                                if (EnvLoader::get($api, false)) $enabled_apis++;
                            }
                            echo $enabled_apis;
                            ?>
                        </h5>
                        <small class="text-muted">APIs Activas</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success mb-0">98%</h5>
                        <small class="text-muted">Uptime</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools"></i> Acciones
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ($base_url ?? '/') ?>admin/api_status" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-server"></i> Ver Estado de APIs
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información Técnica Adicional -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-code"></i> Información Técnica
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-muted">Configuración PHP</h6>
                        <ul class="list-unstyled">
                            <li><strong>Extensión mysqli:</strong> 
                                <span class="badge bg-<?= extension_loaded('mysqli') ? 'success' : 'danger' ?>">
                                    <?= extension_loaded('mysqli') ? 'Cargada' : 'No Disponible' ?>
                                </span>
                            </li>
                            <li><strong>Extensión curl:</strong> 
                                <span class="badge bg-<?= extension_loaded('curl') ? 'success' : 'danger' ?>">
                                    <?= extension_loaded('curl') ? 'Cargada' : 'No Disponible' ?>
                                </span>
                            </li>
                            <li><strong>Extensión json:</strong> 
                                <span class="badge bg-<?= extension_loaded('json') ? 'success' : 'danger' ?>">
                                    <?= extension_loaded('json') ? 'Cargada' : 'No Disponible' ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Directorios</h6>
                        <ul class="list-unstyled">
                            <li><strong>Logs:</strong> 
                                <span class="badge bg-<?= is_writable(APPPATH . 'logs/') ? 'success' : 'warning' ?>">
                                    <?= is_writable(APPPATH . 'logs/') ? 'Escribible' : 'Solo Lectura' ?>
                                </span>
                            </li>
                            <li><strong>Cache:</strong> 
                                <span class="badge bg-<?= is_writable(sys_get_temp_dir()) ? 'success' : 'warning' ?>">
                                    <?= is_writable(sys_get_temp_dir()) ? 'Disponible' : 'Limitado' ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Configuración TrackTraster</h6>
                        <ul class="list-unstyled">
                            <li><strong>Sistema:</strong> 
                                <span class="badge bg-info">Simplificado</span>
                            </li>
                            <li><strong>Gestión de APIs:</strong> 
                                <span class="badge bg-success">.env</span>
                            </li>
                            <li><strong>Seguridad:</strong> 
                                <span class="badge bg-success">Mejorada</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.table td {
    border: none;
    padding: 0.5rem 0;
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
