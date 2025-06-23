<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <div class="header-actions">
            <a href="<?= ($base_url ?? '/') ?>analytics" class="btn btn-outline-light">
                <i class="fas fa-chart-bar"></i> Ver Analíticas
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($tracked_artists)): ?>
        <!-- Vista sin artistas -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-file-alt text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">Reportes Profesionales</h4>
                <p class="text-muted mb-4">
                    No tienes artistas en seguimiento para generar reportes. 
                    <br>Agrega artistas para acceder a reportes detallados de crecimiento y analíticas.
                </p>
                <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Buscar Primer Artista
                </a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Introducción -->
        <div class="card intro-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="card-title">
                            <i class="fas fa-file-chart-line text-primary"></i>
                            Reportes Profesionales de Crecimiento
                        </h5>
                        <p class="card-text text-muted mb-0">
                            Genera reportes detallados con métricas, tendencias y recomendaciones 
                            para el crecimiento de tus artistas en LATAM.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="report-stats">
                            <h3 class="text-primary"><?= count($tracked_artists) ?></h3>
                            <p class="text-muted mb-0">Artistas disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipos de reportes -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="report-type-card">
                    <div class="report-type-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5>Reporte Individual</h5>
                    <p class="text-muted">
                        Análisis completo de un artista específico con métricas detalladas, 
                        tendencias y recomendaciones personalizadas.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Métricas de crecimiento</li>
                        <li><i class="fas fa-check"></i> Análisis de tendencias</li>
                        <li><i class="fas fa-check"></i> Comparativas temporales</li>
                        <li><i class="fas fa-check"></i> Recomendaciones estratégicas</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="report-type-card">
                    <div class="report-type-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Reporte Comparativo</h5>
                    <p class="text-muted">
                        Compara el rendimiento de múltiples artistas para identificar 
                        patrones y oportunidades de crecimiento.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Comparación directa</li>
                        <li><i class="fas fa-check"></i> Rankings por métricas</li>
                        <li><i class="fas fa-check"></i> Análisis de mercado</li>
                        <li><i class="fas fa-clock text-muted"></i> Próximamente</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="report-type-card">
                    <div class="report-type-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <h5>Reporte Periódico</h5>
                    <p class="text-muted">
                        Resúmenes automáticos semanales o mensuales con las métricas 
                        más importantes de todos tus artistas.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Resúmenes ejecutivos</li>
                        <li><i class="fas fa-check"></i> Alertas automáticas</li>
                        <li><i class="fas fa-check"></i> Envío por email</li>
                        <li><i class="fas fa-clock text-muted"></i> Próximamente</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Lista de artistas para reportes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i>
                    Generar Reportes Individuales
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Artista</th>
                                <th>País</th>
                                <th>Evento</th>
                                <th>Seguimiento desde</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracked_artists as $artist): ?>
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
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= strtoupper($artist['country_code']) ?>
                                    </span>
                                    <br><small class="text-muted"><?= $countries[$artist['country_code']] ?? 'País desconocido' ?></small>
                                </td>
                                <td>
                                    <?php if ($artist['event_name']): ?>
                                    <?= htmlspecialchars($artist['event_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Seguimiento general</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($artist['tracking_started'])) ?>
                                    <br><small class="text-muted">
                                        <?php 
                                        $days = floor((time() - strtotime($artist['tracking_started'])) / (60 * 60 * 24));
                                        echo $days . ' días';
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                    $days = floor((time() - strtotime($artist['tracking_started'])) / (60 * 60 * 24));
                                    if ($days < 7): 
                                    ?>
                                    <span class="badge bg-warning">Recolectando datos</span>
                                    <?php elseif ($days < 30): ?>
                                    <span class="badge bg-info">Datos parciales</span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Datos completos</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= ($base_url ?? '/') ?>reports/artist/<?= $artist['id'] ?>" 
                                           class="btn btn-primary" title="Ver reporte">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="<?= ($base_url ?? '/') ?>reports/download/<?= $artist['id'] ?>/csv" 
                                           class="btn btn-outline-success" title="Descargar CSV">
                                            <i class="fas fa-file-csv"></i>
                                        </a>
                                        <a href="<?= ($base_url ?? '/') ?>reports/download/<?= $artist['id'] ?>/pdf" 
                                           class="btn btn-outline-danger" title="Descargar PDF">
                                            <i class="fas fa-file-pdf"></i>
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

        <!-- Información adicional -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-lightbulb text-warning"></i>
                            Consejos para mejores reportes
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Mantén seguimientos activos por al menos 30 días</li>
                            <li><i class="fas fa-check text-success"></i> Configura eventos específicos para análisis dirigidos</li>
                            <li><i class="fas fa-check text-success"></i> Revisa reportes semanalmente para detectar tendencias</li>
                            <li><i class="fas fa-check text-success"></i> Combina datos de múltiples países para análisis regional</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-chart-pie text-info"></i>
                            Métricas incluidas en reportes
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-play text-primary"></i> Streams totales y promedio diario</li>
                            <li><i class="fas fa-users text-success"></i> Crecimiento de seguidores</li>
                            <li><i class="fas fa-trophy text-warning"></i> Posiciones en charts musicales</li>
                            <li><i class="fas fa-hashtag text-info"></i> Menciones en redes sociales</li>
                            <li><i class="fas fa-heart text-danger"></i> Engagement y interacciones</li>
                            <li><i class="fas fa-map-marker-alt text-secondary"></i> Análisis geográfico detallado</li>
                        </ul>
                    </div>
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
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.intro-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.report-stats h3 {
    font-size: 3rem;
    font-weight: 700;
}

.report-type-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    margin-bottom: 1.5rem;
}

.report-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
}

.report-type-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.report-type-card h5 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.feature-list {
    list-style: none;
    padding: 0;
    text-align: left;
}

.feature-list li {
    padding: 0.25rem 0;
    font-size: 0.9rem;
}

.feature-list i {
    width: 16px;
    margin-right: 0.5rem;
}

.info-card {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
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
    
    .report-type-card {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .report-type-icon {
        width: 60px;
        height: 60px;
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
