<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/search" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Seguimiento
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($trackings)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">¡Comienza a hacer seguimiento!</h4>
                <p class="text-muted mb-4">
                    No tienes seguimientos activos. Busca un artista y configura tu primer seguimiento 
                    para analizar su crecimiento en LATAM.
                </p>
                <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/search" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Buscar Artista
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
                                <h6 class="card-title">Total Seguimientos</h6>
                                <h3 class="mb-0"><?= count($trackings) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
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
                                <h6 class="card-title">Activos</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($trackings, function($t) { return $t['status'] === 'active'; })) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-play fa-2x"></i>
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
                                <h6 class="card-title">Eventos Próximos</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($trackings, function($t) { 
                                        return $t['tracking_status'] === 'pending'; 
                                    })) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar fa-2x"></i>
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
                                <h6 class="card-title">Completados</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($trackings, function($t) { 
                                        return $t['tracking_status'] === 'completed'; 
                                    })) ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="active">Activos</option>
                            <option value="paused">Pausados</option>
                            <option value="completed">Completados</option>
                            <option value="cancelled">Cancelados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="countryFilter">
                            <option value="">Todos los países</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="trackingStatusFilter">
                            <option value="">Todos los seguimientos</option>
                            <option value="pending">Con eventos próximos</option>
                            <option value="ongoing">En curso</option>
                            <option value="completed">Eventos pasados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de seguimientos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Seguimientos Activos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="trackingsTable">
                        <thead>
                            <tr>
                                <th>Artista</th>
                                <th>País</th>
                                <th>Evento</th>
                                <th>Fecha Evento</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trackings as $tracking): ?>
                            <tr data-status="<?= $tracking['status'] ?>" 
                                data-country="<?= $tracking['country_code'] ?>"
                                data-tracking-status="<?= $tracking['tracking_status'] ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($tracking['image_url']): ?>
                                        <img src="<?= htmlspecialchars($tracking['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($tracking['artist_name']) ?>"
                                             class="rounded-circle me-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-music text-white"></i>
                                        </div>
                                        <?php endif; ?>                        <div>
                            <strong><?= htmlspecialchars($tracking['artist_name']) ?></strong>
                            <?php if ($tracking['popularity']): ?>
                            <br><small class="text-muted">Popularidad BD: <?= $tracking['popularity'] ?>%</small>
                            <?php endif; ?>
                            
                            <!-- Mostrar datos de múltiples plataformas -->
                            <?php if (!empty($tracking['platform_metrics']['platforms_data'])): ?>
                            <br><div class="platform-badges mt-1">
                                <?php foreach ($tracking['platform_metrics']['platforms_data'] as $platform => $data): ?>
                                    <?php if ($data['status'] === 'found'): ?>
                                        <?php
                                        $platformIcons = [
                                            'spotify' => 'fab fa-spotify text-success',
                                            'deezer' => 'fas fa-music text-warning',
                                            'youtube_music' => 'fab fa-youtube text-danger',
                                            'apple_music' => 'fab fa-apple text-dark'
                                        ];
                                        $icon = $platformIcons[$platform] ?? 'fas fa-music';
                                        $followers = $data['followers'] ?? 0;
                                        ?>
                                        <span class="badge bg-light text-dark me-1" title="<?= ucfirst(str_replace('_', ' ', $platform)) ?>: <?= number_format($followers) ?> seguidores">
                                            <i class="<?= $icon ?>"></i>
                                            <?php if ($followers > 0): ?>
                                                <?= $followers >= 1000000 ? round($followers/1000000, 1) . 'M' : ($followers >= 1000 ? round($followers/1000, 1) . 'K' : $followers) ?>
                                            <?php else: ?>
                                                ✓
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Resumen de métricas combinadas -->
                            <?php if ($tracking['total_followers_all_platforms'] > 0 || $tracking['avg_popularity_all_platforms'] > 0): ?>
                            <br><small class="text-info">
                                <i class="fas fa-users"></i> Total: <?= number_format($tracking['total_followers_all_platforms']) ?> seguidores
                                <?php if ($tracking['avg_popularity_all_platforms'] > 0): ?>
                                | <i class="fas fa-star"></i> <?= $tracking['avg_popularity_all_platforms'] ?>% popularidad
                                <?php endif; ?>
                            </small>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= strtoupper($tracking['country_code']) ?>
                                    </span>
                                    <br><small class="text-muted"><?= $countries[$tracking['country_code']] ?? 'País desconocido' ?></small>
                                </td>
                                <td>
                                    <?php if ($tracking['event_name']): ?>
                                    <?= htmlspecialchars($tracking['event_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Sin evento específico</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tracking['event_date']): ?>
                                    <?= date('d/m/Y', strtotime($tracking['event_date'])) ?>
                                    <?php 
                                    $daysUntil = ceil((strtotime($tracking['event_date']) - time()) / (60 * 60 * 24));
                                    if ($daysUntil > 0): 
                                    ?>
                                    <br><small class="text-info"><?= $daysUntil ?> días restantes</small>
                                    <?php elseif ($daysUntil === 0): ?>
                                    <br><small class="text-warning">¡Hoy!</small>
                                    <?php else: ?>
                                    <br><small class="text-muted">Hace <?= abs($daysUntil) ?> días</small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $startDate = strtotime($tracking['tracking_start_date']);
                                    $daysDiff = floor((time() - $startDate) / (60 * 60 * 24));
                                    ?>
                                    <strong><?= $daysDiff ?></strong> días
                                    <br><small class="text-muted">Desde <?= date('d/m/Y', $startDate) ?></small>
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
                                    $class = $statusClass[$tracking['status']] ?? 'bg-secondary';
                                    $text = $statusText[$tracking['status']] ?? 'Desconocido';
                                    ?>
                                    <span class="badge <?= $class ?>"><?= $text ?></span>
                                </td>
                                <td>
                                    <?php if ($tracking['event_date']): ?>
                                    <?php 
                                    $totalDays = ceil((strtotime($tracking['event_date']) - strtotime($tracking['tracking_start_date'])) / (60 * 60 * 24));
                                    $elapsedDays = ceil((time() - strtotime($tracking['tracking_start_date'])) / (60 * 60 * 24));
                                    $progress = min(100, max(0, ($elapsedDays / max(1, $totalDays)) * 100));
                                    ?>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?= $progress ?>%"
                                             title="<?= round($progress, 1) ?>% completado"></div>
                                    </div>
                                    <small class="text-muted"><?= round($progress, 1) ?>%</small>
                                    <?php else: ?>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" 
                                             style="width: 100%"
                                             title="Seguimiento continuo"></div>
                                    </div>
                                    <small class="text-muted">Continuo</small>
                                    <?php endif; ?>
                                </td>                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/view/<?= $tracking['artist_id'] ?>" 
                                           class="btn btn-outline-primary" title="Ver artista">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= ($base_url ?? '/tracktraster/') ?>trackings/edit/<?= $tracking['id'] ?>" 
                                           class="btn btn-outline-warning" title="Editar seguimiento">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= ($base_url ?? '/tracktraster/') ?>analytics?artist_id=<?= $tracking['artist_id'] ?>" 
                                           class="btn btn-outline-success" title="Ver analíticas">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Eliminar seguimiento"
                                                onclick="confirmDelete('<?= $tracking['id'] ?>', '<?= htmlspecialchars($tracking['artist_name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación para eliminar seguimiento -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5>¿Estás seguro de que quieres eliminar este seguimiento?</h5>
                    <p class="text-muted">
                        Se eliminará el seguimiento de <strong id="artistName"></strong><br>
                        <strong>Esta acción no se puede deshacer</strong>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Eliminar Seguimiento
                </button>
            </div>
        </div>
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

.progress {
    border-radius: 4px;
}

.platform-badges .badge {
    font-size: 0.7em;
    padding: 0.25em 0.5em;
    border: 1px solid #dee2e6;
}

.platform-badges .badge:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

.platform-badges .badge i {
    margin-right: 0.25em;
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
    
    .d-flex.align-items-center .me-3 {
        margin-right: 0.5rem !important;
    }
}
</style>

<!-- JavaScript para esta vista -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const countryFilter = document.getElementById('countryFilter');
    const trackingStatusFilter = document.getElementById('trackingStatusFilter');
    const table = document.getElementById('trackingsTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const statusValue = statusFilter.value;
        const countryValue = countryFilter.value;
        const trackingStatusValue = trackingStatusFilter.value;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowCountry = row.getAttribute('data-country');
            const rowTrackingStatus = row.getAttribute('data-tracking-status');

            const statusMatch = !statusValue || rowStatus === statusValue;
            const countryMatch = !countryValue || rowCountry === countryValue;
            const trackingStatusMatch = !trackingStatusValue || rowTrackingStatus === trackingStatusValue;

            if (statusMatch && countryMatch && trackingStatusMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update visible count
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        updateResultsCount(visibleRows.length, rows.length);
    }

    function updateResultsCount(visible, total) {
        const cardHeader = document.querySelector('.card-header h5');
        if (visible === total) {
            cardHeader.textContent = 'Seguimientos Activos';
        } else {
            cardHeader.textContent = `Seguimientos Activos (${visible} de ${total})`;
        }
    }    statusFilter.addEventListener('change', filterTable);
    countryFilter.addEventListener('change', filterTable);
    trackingStatusFilter.addEventListener('change', filterTable);
    
    // Configurar el botón de confirmación del modal de eliminación
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentTrackingId) {
            deleteTracking(currentTrackingId);
        }
    });
});

function clearFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('countryFilter').value = '';
    document.getElementById('trackingStatusFilter').value = '';
    
    const table = document.getElementById('trackingsTable');
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    const cardHeader = document.querySelector('.card-header h5');
    cardHeader.textContent = 'Seguimientos Activos';
}

// Confirmación y eliminación de seguimientos
let currentTrackingId = null;

function confirmDelete(trackingId, artistName) {
    currentTrackingId = trackingId;
    document.getElementById('artistName').textContent = artistName;
    
    // Mostrar el modal de Bootstrap
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function deleteTracking(trackingId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= ($base_url ?? '/tracktraster/') ?>trackings/delete/' + trackingId;
    
    // Agregar CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $csrf_token ?? '' ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Event listener para el botón de confirmación del modal
document.addEventListener('DOMContentLoaded', function() {
    // ... código existente ...
    
    // Configurar el botón de confirmación del modal
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentTrackingId) {
            deleteTracking(currentTrackingId);
        }
    });
});
</script>

<?php 
$content = ob_get_clean();
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/dashboard.php';
?>
