<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <?php if ($selected_artist): ?>
        <div class="header-actions">
            <a href="<?= ($base_url ?? '/') ?>analytics/export?artist_id=<?= $selected_artist['id'] ?>" 
               class="btn btn-outline-light me-2">
                <i class="fas fa-download"></i> Exportar Datos
            </a>
            <a href="<?= ($base_url ?? '/') ?>reports/artist/<?= $selected_artist['id'] ?>" 
               class="btn btn-success">
                <i class="fas fa-file-alt"></i> Generar Reporte
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Selector de artista -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label for="artistSelector" class="form-label mb-2">Seleccionar artista para analizar:</label>
                        <select class="form-select" id="artistSelector" onchange="changeArtist()">
                            <option value="">Selecciona un artista...</option>                            <?php foreach ($tracked_artists as $artist): ?>
                            <option value="<?= $artist['id'] ?>" 
                                    <?= ($selected_artist && $selected_artist['id'] == $artist['id']) ? 'selected' : '' ?>
                                    data-event="<?= htmlspecialchars($artist['event_name']) ?>"
                                    data-days="<?= $artist['days_to_event'] ?>"
                                    data-status="<?= $artist['tracking_status'] ?>">
                                <?= htmlspecialchars($artist['name']) ?>
                                <?php if ($artist['event_name']): ?>
                                - <?= htmlspecialchars($artist['event_name']) ?>
                                <?php if ($artist['days_to_event'] > 0): ?>
                                (<?= $artist['days_to_event'] ?> días)
                                <?php elseif ($artist['days_to_event'] == 0): ?>
                                (HOY)
                                <?php else: ?>
                                (evento pasado)
                                <?php endif; ?>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <?php if (empty($tracked_artists)): ?>
                        <div class="text-center">
                            <p class="text-muted mb-2">No tienes artistas en seguimiento</p>
                            <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Artista
                            </a>
                        </div>
                        <?php elseif ($selected_artist): ?>                        <div class="selected-artist-info text-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <?php if ($selected_artist['image_url']): ?>
                                <img src="<?= htmlspecialchars($selected_artist['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($selected_artist['name']) ?>"
                                     class="rounded-circle me-2" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="text-start">
                                    <strong><?= htmlspecialchars($selected_artist['name']) ?></strong>
                                    <br><small class="text-muted">
                                        Seguimiento en <?= $countries[$selected_artist['country_code']] ?? 'País desconocido' ?>
                                        <?php if ($selected_artist['event_name']): ?>
                                        - <?= htmlspecialchars($selected_artist['event_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$selected_artist): ?>
        <!-- Vista sin artista seleccionado -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-chart-bar text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">Analíticas Avanzadas</h4>
                <p class="text-muted mb-4">
                    Selecciona un artista para ver análisis detallados de su crecimiento, 
                    tendencias y métricas en tiempo real.
                </p>
                <?php if (empty($tracked_artists)): ?>
                <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Buscar Primer Artista
                </a>
                <?php else: ?>
                <p class="text-info">
                    <i class="fas fa-arrow-up"></i>
                    Usa el selector de arriba para elegir un artista
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
          <!-- Dashboard de analíticas -->
        <div class="analytics-dashboard">
              <?php if (isset($analytics['message'])): ?>
            <!-- Mensaje informativo -->
            <div class="alert alert-<?= strpos($analytics['message'], 'recién creado') !== false ? 'success' : 'info' ?> mb-4">
                <i class="fas fa-<?= strpos($analytics['message'], 'recién creado') !== false ? 'check-circle' : 'info-circle' ?>"></i>
                <?= htmlspecialchars($analytics['message']) ?>
                
                <?php if (strpos($analytics['message'], 'recién creado') !== false): ?>
                <hr class="my-2">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <strong>¿Qué sigue?</strong>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= ($base_url ?? '/') ?>analytics/populateMetrics?populate_key=populate_metrics_2025" 
                           class="btn btn-outline-success btn-sm"
                           onclick="return confirm('¿Inicializar métricas ahora? Esto puede tomar unos minutos.')">
                            <i class="fas fa-database"></i> Inicializar Métricas
                        </a>
                        <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search"></i> Agregar Otro Artista
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Métricas principales -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon streams">
                            <i class="fas fa-play"></i>
                        </div>                        <div class="metric-content">
                            <h3 class="metric-value"><?= number_format($analytics['summary']['total_followers']) ?></h3>
                            <p class="metric-label">Seguidores Totales</p>
                            <div class="metric-trend <?= $analytics['trends']['followers_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['followers_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs(round($analytics['trends']['followers_trend'], 1)) ?>% vs semana anterior
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon followers">
                            <i class="fas fa-users"></i>
                        </div>                        <div class="metric-content">
                            <h3 class="metric-value"><?= $analytics['summary']['monthly_listeners'] > 0 ? number_format($analytics['summary']['monthly_listeners']) : 'N/A' ?></h3>
                            <p class="metric-label">Oyentes Mensuales</p>
                            <div class="metric-trend <?= $analytics['trends']['listeners_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['listeners_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs(round($analytics['trends']['listeners_trend'], 1)) ?>% crecimiento
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon charts">
                            <i class="fas fa-trophy"></i>
                        </div>                        <div class="metric-content">
                            <h3 class="metric-value"><?= $analytics['summary']['current_popularity'] ?>%</h3>
                            <p class="metric-label">Popularidad Actual</p>
                            <div class="metric-trend <?= $analytics['trends']['popularity_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['popularity_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs(round($analytics['trends']['popularity_trend'], 1)) ?>% cambio
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon social">
                            <i class="fas fa-hashtag"></i>
                        </div>                        <div class="metric-content">
                            <h3 class="metric-value"><?= $analytics['summary']['platforms_count'] ?></h3>
                            <p class="metric-label">Plataformas Activas</p>
                            <div class="metric-trend">
                                <i class="fas fa-music"></i>
                                Spotify, Deezer, Last.fm
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card chart-card">                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line text-primary"></i>
                                Crecimiento de Seguidores (Últimos 30 días)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="followersChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt text-success"></i>
                                Top Ciudades
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="cities-list">
                                <?php foreach ($analytics['regional_data']['top_cities'] as $index => $city): ?>
                                <div class="city-item">
                                    <div class="city-rank"><?= $index + 1 ?></div>
                                    <div class="city-info">
                                        <div class="city-name"><?= htmlspecialchars($city['name']) ?></div>
                                        <div class="city-streams"><?= number_format($city['streams']) ?> streams</div>
                                    </div>
                                    <div class="city-bar">
                                        <?php 
                                        $maxStreams = max(array_column($analytics['regional_data']['top_cities'], 'streams'));
                                        $percentage = ($city['streams'] / $maxStreams) * 100;
                                        ?>
                                        <div class="city-bar-fill" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métricas adicionales -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card chart-card">                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-headphones text-info"></i>
                                Oyentes Last.fm
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="listenersChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star text-warning"></i>
                                Score de Popularidad
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="popularityChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del período -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="period-stat">
                                <h4><?= $analytics['summary']['tracking_days'] ?></h4>
                                <p class="text-muted">Días de seguimiento</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="period-stat">
                                <h4><?= date('d/m/Y', strtotime($selected_artist['tracking_start_date'])) ?></h4>
                                <p class="text-muted">Fecha de inicio</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="period-stat">
                                <h4><?= $selected_artist['event_date'] ? date('d/m/Y', strtotime($selected_artist['event_date'])) : 'Continuo' ?></h4>
                                <p class="text-muted">Evento objetivo</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="period-stat">
                                <h4><?= strtoupper($selected_artist['country_code']) ?></h4>
                                <p class="text-muted">País de análisis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
        <?php if ($selected_artist && ($lifecycle || $selected_artist['event_name'])): ?>
        <!-- Información del evento y progreso -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> 
                    <?= ($lifecycle && $lifecycle['event_name']) || $selected_artist['event_name'] ? 'Progreso hacia el Evento' : 'Información del Seguimiento' ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($lifecycle): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">
                                    <?= $lifecycle['event_name'] ? htmlspecialchars($lifecycle['event_name']) : 'Seguimiento General' ?>
                                </span>
                                <span class="badge bg-<?= 
                                    $lifecycle['phase'] == 'pre-tracking' ? 'secondary' :
                                    ($lifecycle['phase'] == 'early-tracking' ? 'info' :
                                    ($lifecycle['phase'] == 'mid-tracking' ? 'warning' :
                                    ($lifecycle['phase'] == 'pre-event' ? 'danger' :
                                    ($lifecycle['phase'] == 'event-day' ? 'success' : 'dark'))))
                                ?>">
                                    <?= 
                                        $lifecycle['phase'] == 'pre-tracking' ? 'Pre-seguimiento' :
                                        ($lifecycle['phase'] == 'early-tracking' ? 'Seguimiento Inicial' :
                                        ($lifecycle['phase'] == 'mid-tracking' ? 'Seguimiento Medio' :
                                        ($lifecycle['phase'] == 'pre-event' ? 'Pre-evento' :
                                        ($lifecycle['phase'] == 'event-day' ? 'Día del Evento' : 'Post-evento'))))
                                    ?>
                                </span>
                            </div>
                            
                            <?php if ($lifecycle['event_date']): ?>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-gradient" 
                                     role="progressbar" 
                                     style="width: <?= $lifecycle['progress_percentage'] ?>%"
                                     aria-valuenow="<?= $lifecycle['progress_percentage'] ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= round($lifecycle['progress_percentage'], 1) ?>%
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-primary">
                                        <i class="fas fa-play"></i>
                                        <div class="small">Inicio</div>
                                        <div class="fw-bold"><?= date('d/m', strtotime($lifecycle['tracking_start_date'])) ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-info">
                                        <i class="fas fa-clock"></i>
                                        <div class="small">Días restantes</div>
                                        <div class="fw-bold">
                                            <?= $lifecycle['days_to_event'] > 0 ? $lifecycle['days_to_event'] : ($lifecycle['days_to_event'] == 0 ? 'HOY' : 'Pasado') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-success">
                                        <i class="fas fa-flag-checkered"></i>
                                        <div class="small">Evento</div>
                                        <div class="fw-bold"><?= date('d/m', strtotime($lifecycle['event_date'])) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Días de seguimiento</h6>
                                <div class="h4 mb-1"><?= $lifecycle['days_tracking'] ?></div>
                                <small class="text-muted">
                                    Desde <?= date('d/m/Y', strtotime($lifecycle['tracking_start_date'])) ?>
                                </small>
                                
                                <?php if ($lifecycle['event_city'] || $lifecycle['event_venue']): ?>
                                <hr class="my-2">
                                <div class="small">
                                    <?php if ($lifecycle['event_venue']): ?>
                                    <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lifecycle['event_venue']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($lifecycle['event_city']): ?>
                                    <div><i class="fas fa-city"></i> <?= htmlspecialchars($lifecycle['event_city']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Información básica cuando no hay lifecycle disponible -->
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <strong>Seguimiento iniciado</strong><br>
                    <?php if ($selected_artist['event_name']): ?>
                    <strong>Evento:</strong> <?= htmlspecialchars($selected_artist['event_name']) ?><br>
                    <?php endif; ?>
                    <?php if ($selected_artist['event_date']): ?>
                    <strong>Fecha del evento:</strong> <?= date('d/m/Y', strtotime($selected_artist['event_date'])) ?><br>
                    <strong>Días restantes:</strong> <?= max(0, floor((strtotime($selected_artist['event_date']) - time()) / (60 * 60 * 24))) ?><br>
                    <?php endif; ?>
                    <?php if ($selected_artist['event_city']): ?>
                    <strong>Ciudad:</strong> <?= htmlspecialchars($selected_artist['event_city']) ?><br>
                    <?php endif; ?>
                    <small class="text-muted">Las métricas de progreso se activarán una vez que el sistema inicialice completamente.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?></div>
        <?php endif; ?>
        
        <?php if ($selected_artist && $lifecycle && isset($analytics['lifecycle']['recommendations'])): ?>
        <!-- Recomendaciones según la fase del evento -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb text-warning"></i>
                    Recomendaciones para esta Fase
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Fase actual:</strong> 
                    <?= 
                        $lifecycle['phase'] == 'pre-tracking' ? 'Pre-seguimiento' :
                        ($lifecycle['phase'] == 'early-tracking' ? 'Seguimiento Inicial' :
                        ($lifecycle['phase'] == 'mid-tracking' ? 'Seguimiento Medio' :
                        ($lifecycle['phase'] == 'pre-event' ? 'Pre-evento' :
                        ($lifecycle['phase'] == 'event-day' ? 'Día del Evento' : 'Post-evento'))))
                    ?>
                    <?php if ($lifecycle['days_to_event'] > 0): ?>
                    - <?= $lifecycle['days_to_event'] ?> días para el evento
                    <?php elseif ($lifecycle['days_to_event'] == 0): ?>
                    - ¡Hoy es el evento!
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <?php foreach ($analytics['lifecycle']['recommendations'] as $index => $recommendation): ?>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <span class="badge bg-primary rounded-pill"><?= $index + 1 ?></span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0"><?= htmlspecialchars($recommendation) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (isset($analytics['growth_towards_event']) && !empty($analytics['growth_towards_event'])): ?>
                <hr>
                <h6>Crecimiento hacia el evento:</h6>
                <div class="row">
                    <?php foreach ($analytics['growth_towards_event'] as $platform => $growth): ?>
                    <?php if (isset($growth['followers_growth'])): ?>
                    <div class="col-md-3 mb-2">
                        <div class="text-center">
                            <div class="text-uppercase small text-muted"><?= $platform ?></div>
                            <div class="fw-bold <?= $growth['followers_growth'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $growth['followers_growth'] >= 0 ? '+' : '' ?><?= number_format($growth['followers_growth']) ?>
                            </div>
                            <div class="small text-muted">
                                (<?= $growth['followers_growth_percentage'] >= 0 ? '+' : '' ?><?= round($growth['followers_growth_percentage'], 1) ?>%)
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
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

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: white;
}

.metric-icon.streams { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.metric-icon.followers { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.metric-icon.charts { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
.metric-icon.social { background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%); }

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.metric-trend {
    font-size: 0.8rem;
    font-weight: 600;
}

.metric-trend.positive { color: #28a745; }
.metric-trend.negative { color: #dc3545; }

.chart-card {
    margin-bottom: 1.5rem;
}

.cities-list {
    max-height: 300px;
    overflow-y: auto;
}

.city-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.city-rank {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
    margin-right: 1rem;
}

.city-info {
    flex: 1;
    margin-right: 1rem;
}

.city-name {
    font-weight: 600;
    color: #2c3e50;
}

.city-streams {
    font-size: 0.8rem;
    color: #6c757d;
}

.city-bar {
    width: 60px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.city-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
}

.period-stat h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .header-actions {
        margin-top: 1rem;
    }
    
    .metric-card {
        margin-bottom: 1rem;
    }
    
    .metric-value {
        font-size: 1.5rem;
    }
}
</style>

<!-- JavaScript para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function changeArtist() {
    const selector = document.getElementById('artistSelector');
    if (selector.value) {
        window.location.href = '<?= ($base_url ?? '/') ?>analytics?artist_id=' + selector.value;
    }
}

<?php if ($selected_artist && $analytics): ?>
// Datos reales para los gráficos
const followersData = <?= json_encode($analytics['charts']['followers_growth']) ?>;
const popularityData = <?= json_encode($analytics['charts']['popularity_score']) ?>;
const listenersData = <?= json_encode($analytics['charts']['listeners_growth']) ?>;

// Configuración común de gráficos
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            }
        },
        y: {
            grid: {
                color: '#f8f9fa'
            }
        }
    }
};

// Gráfico de Seguidores (principal)
const followersCtx = document.getElementById('followersChart').getContext('2d');
new Chart(followersCtx, {
    type: 'line',
    data: {
        labels: followersData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            data: followersData.map(item => item.value),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: commonOptions
});

// Gráfico de Oyentes (Last.fm)
const listenersCtx = document.getElementById('listenersChart').getContext('2d');
new Chart(listenersCtx, {
    type: 'bar',
    data: {
        labels: listenersData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            data: listenersData.map(item => item.value),
            backgroundColor: '#667eea',
            borderRadius: 4
        }]
    },
    options: commonOptions
});

// Gráfico de Popularidad
const popularityCtx = document.getElementById('popularityChart').getContext('2d');
new Chart(popularityCtx, {
    type: 'line',
    data: {
        labels: popularityData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            data: popularityData.map(item => item.value),
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: commonOptions
});
<?php endif; ?>
</script>

<?php 
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
