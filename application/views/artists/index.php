<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar Artista
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($artists)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-music text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">¡Comienza tu análisis musical!</h4>
                <p class="text-muted mb-4">
                    No tienes artistas en seguimiento. Busca y agrega artistas para comenzar a analizar 
                    su crecimiento en países de LATAM.
                </p>
                <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Buscar Primer Artista
                </a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Artistas Seguidos</h6>
                                <h3 class="mb-0"><?= count($artists) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Seguimientos Activos</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($artists, function($a) { return $a['status'] === 'active'; })) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Países Monitoreados</h6>
                                <h3 class="mb-0">
                                    <?= count(array_unique(array_column($artists, 'country_code'))) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-globe-americas fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Eventos Próximos</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($artists, function($a) { 
                                        return $a['event_date'] && strtotime($a['event_date']) > time(); 
                                    })) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de artistas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Artistas en Seguimiento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Artista</th>
                                <th>País</th>
                                <th>Evento</th>
                                <th>Fecha Evento</th>
                                <th>Estado</th>
                                <th>Inicio Seguimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artists as $artist): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($artist['image_url']): ?>
                                        <img src="<?= htmlspecialchars($artist['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($artist['name']) ?>"
                                             class="rounded-circle me-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-music text-white"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($artist['name']) ?></strong>
                                            <?php if ($artist['popularity']): ?>
                                            <br><small class="text-muted">Popularidad: <?= $artist['popularity'] ?>%</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($artist['country_code']): ?>
                                    <span class="badge bg-secondary">
                                        <?= strtoupper($artist['country_code']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($artist['event_name']): ?>
                                    <?= htmlspecialchars($artist['event_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Sin evento</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($artist['event_date']): ?>
                                    <?= date('d/m/Y', strtotime($artist['event_date'])) ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'active' => 'bg-success',
                                        'paused' => 'bg-warning',
                                        'completed' => 'bg-info',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    $statusText = [
                                        'active' => 'Activo',
                                        'paused' => 'Pausado',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    $class = $statusClass[$artist['status']] ?? 'bg-secondary';
                                    $text = $statusText[$artist['status']] ?? 'Desconocido';
                                    ?>
                                    <span class="badge <?= $class ?>"><?= $text ?></span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($artist['tracking_started'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= ($base_url ?? '/') ?>artists/view/<?= $artist['id'] ?>" 
                                           class="btn btn-outline-primary" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= ($base_url ?? '/') ?>analytics?artist_id=<?= $artist['id'] ?>" 
                                           class="btn btn-outline-success" title="Ver analíticas">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <a href="<?= ($base_url ?? '/') ?>trackings/edit/<?= $artist['id'] ?>" 
                                           class="btn btn-outline-warning" title="Editar seguimiento">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CSS adicional para esta vista -->
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

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.badge {
    font-size: 0.75em;
}

@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
