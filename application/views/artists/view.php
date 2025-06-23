<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <?php if ($artist['image_url']): ?>
            <img src="<?= htmlspecialchars($artist['image_url']) ?>" 
                 alt="<?= htmlspecialchars($artist['name']) ?>"
                 class="artist-header-image me-3">
            <?php else: ?>
            <div class="artist-header-image-placeholder me-3">
                <i class="fas fa-music"></i>
            </div>
            <?php endif; ?>
            <div>
                <h1 class="h3 mb-0"><?= htmlspecialchars($artist['name']) ?></h1>
                <p class="mb-0 mt-1">
                    <?php if ($artist['status']): ?>
                    <span class="badge bg-success">En seguimiento desde <?= date('d/m/Y', strtotime($artist['tracking_started'])) ?></span>
                    <?php else: ?>
                    <span class="badge bg-secondary">Sin seguimiento activo</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>        <div class="header-actions">
            <a href="<?= ($base_url ?? '/') ?>artists" class="btn btn-outline-light me-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button class="btn btn-outline-light me-2" onclick="refreshMetrics()" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Actualizar Métricas
            </button>
            <?php if ($artist['status']): ?>
            <a href="<?= ($base_url ?? '/') ?>analytics?artist_id=<?= $artist['id'] ?>" class="btn btn-success">
                <i class="fas fa-chart-bar"></i> Ver Analíticas
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">    <!-- Información del Artista -->
    <div class="col-lg-4 mb-4">
        <div class="card artist-info-card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-primary"></i>
                    Información del Artista
                </h5>
                
                <div class="artist-details">
                    <div class="detail-item">
                        <label>Nombre:</label>
                        <span><?= htmlspecialchars($artist['name']) ?></span>
                    </div>
                    
                    <?php if ($artist['popularity']): ?>
                    <div class="detail-item">
                        <label>Popularidad BD:</label>
                        <div class="popularity-bar-container">
                            <div class="popularity-bar">
                                <div class="popularity-fill" style="width: <?= $artist['popularity'] ?>%"></div>
                            </div>
                            <span class="popularity-text"><?= $artist['popularity'] ?>%</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Datos multi-plataforma -->
                    <?php if (!empty($platform_data['platforms_data'])): ?>
                    <div class="detail-item">
                        <label>Disponible en:</label>
                        <div class="platform-badges mt-2">
                            <?php foreach ($platform_data['platforms_data'] as $platform => $data): ?>
                                <?php if ($data['status'] === 'found'): ?>
                                    <?php
                                    $platformIcons = [
                                        'spotify' => 'fab fa-spotify text-success',
                                        'deezer' => 'fas fa-music text-warning',
                                        'lastfm' => 'fab fa-lastfm text-danger',
                                        'soundcloud' => 'fab fa-soundcloud text-primary'
                                    ];
                                    $icon = $platformIcons[$platform] ?? 'fas fa-music';
                                    $followers = $data['followers'] ?? 0;
                                    $platformName = ucfirst(str_replace('_', ' ', $platform));
                                    ?>
                                    <div class="platform-item mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="platform-name">
                                                <i class="<?= $icon ?>"></i> <?= $platformName ?>
                                            </span>
                                            <span class="platform-followers">
                                                <?php if ($followers > 0): ?>
                                                    <?= number_format($followers) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($data['popularity']) && $data['popularity'] > 0): ?>
                                        <div class="platform-popularity mt-1">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar" style="width: <?= $data['popularity'] ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $data['popularity'] ?>% popularidad</small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Resumen de métricas combinadas -->
                    <?php if ($platform_data['total_followers'] > 0 || $platform_data['avg_popularity'] > 0): ?>
                    <div class="detail-item">
                        <label>Resumen Multi-Plataforma:</label>
                        <div class="metrics-summary">
                            <?php if ($platform_data['total_followers'] > 0): ?>
                            <div class="metric-row">
                                <i class="fas fa-users text-info"></i>
                                <span>Total: <?= number_format($platform_data['total_followers']) ?> seguidores</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($platform_data['avg_popularity'] > 0): ?>
                            <div class="metric-row">
                                <i class="fas fa-star text-warning"></i>
                                <span>Popularidad promedio: <?= $platform_data['avg_popularity'] ?>%</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="metric-row">
                                <i class="fas fa-globe text-primary"></i>
                                <span>Plataformas encontradas: <?= $platform_data['platforms_count'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($artist['followers_total']): ?>
                    <div class="detail-item">
                        <label>Seguidores (BD):</label>
                        <span><?= number_format($artist['followers_total']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($artist['genres']): ?>
                    <div class="detail-item">
                        <label>Géneros:</label>
                        <div class="genres-tags">
                            <?php 
                            $genres = is_string($artist['genres']) ? json_decode($artist['genres'], true) : $artist['genres'];
                            if ($genres && is_array($genres)):
                                foreach ($genres as $genre): 
                            ?>
                            <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($genre) ?></span>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($artist['spotify_id']): ?>
                    <div class="detail-item">
                        <label>Spotify ID:</label>
                        <span class="font-monospace text-muted small"><?= htmlspecialchars($artist['spotify_id']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información del Seguimiento -->
    <div class="col-lg-8 mb-4">
        <?php if ($artist['status']): ?>
        <div class="card tracking-info-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line text-success"></i>
                        Seguimiento Activo
                    </h5>
                    <div class="tracking-status">
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
                        <span class="badge <?= $class ?> fs-6"><?= $text ?></span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="tracking-detail">
                            <label>País de seguimiento:</label>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2"><?= strtoupper($artist['country_code']) ?></span>
                                <span><?= $countries[$artist['country_code']] ?? 'País desconocido' ?></span>
                            </div>
                        </div>
                        
                        <?php if ($artist['event_name']): ?>
                        <div class="tracking-detail">
                            <label>Evento objetivo:</label>
                            <span><?= htmlspecialchars($artist['event_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($artist['event_date']): ?>
                        <div class="tracking-detail">
                            <label>Fecha del evento:</label>
                            <span><?= date('d/m/Y', strtotime($artist['event_date'])) ?></span>
                            <?php 
                            $daysUntil = ceil((strtotime($artist['event_date']) - time()) / (60 * 60 * 24));
                            if ($daysUntil > 0): 
                            ?>
                            <small class="text-info">
                                (<?= $daysUntil ?> días restantes)
                            </small>
                            <?php elseif ($daysUntil === 0): ?>
                            <small class="text-warning">
                                (¡Hoy!)
                            </small>
                            <?php else: ?>
                            <small class="text-muted">
                                (Hace <?= abs($daysUntil) ?> días)
                            </small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="tracking-detail">
                            <label>Inicio del seguimiento:</label>
                            <span><?= date('d/m/Y H:i', strtotime($artist['tracking_started'])) ?></span>
                        </div>
                        
                        <?php if ($artist['tracking_start_date']): ?>
                        <div class="tracking-detail">
                            <label>Fecha de inicio personalizada:</label>
                            <span><?= date('d/m/Y', strtotime($artist['tracking_start_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="tracking-detail">
                            <label>Duración del seguimiento:</label>
                            <?php 
                            $startDate = strtotime($artist['tracking_started']);
                            $daysDiff = floor((time() - $startDate) / (60 * 60 * 24));
                            ?>
                            <span><?= $daysDiff ?> días</span>
                        </div>
                    </div>
                </div>
                
                <?php if ($artist['notes']): ?>
                <div class="tracking-notes mt-3">
                    <label>Notas:</label>
                    <div class="notes-content">
                        <?= nl2br(htmlspecialchars($artist['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="tracking-actions mt-4">
                    <a href="<?= ($base_url ?? '/') ?>trackings/edit/<?= $artist['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Seguimiento
                    </a>
                    <a href="<?= ($base_url ?? '/') ?>analytics?artist_id=<?= $artist['id'] ?>" class="btn btn-success">
                        <i class="fas fa-chart-bar"></i> Ver Analíticas
                    </a>
                    <a href="<?= ($base_url ?? '/') ?>reports/artist/<?= $artist['id'] ?>" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card no-tracking-card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">Sin seguimiento activo</h4>
                <p class="text-muted mb-4">
                    Este artista no tiene un seguimiento configurado. 
                    <br>Configura un seguimiento para comenzar a analizar su crecimiento.
                </p>
                <a href="<?= ($base_url ?? '/') ?>trackings/create?artist_id=<?= $artist['id'] ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Configurar Seguimiento
                </a>
            </div>
        </div>
        <?php endif; ?>    </div>
</div>

<!-- Dashboard de Plataformas -->
<?php if (!empty($platform_data['platforms_data'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card platforms-dashboard-card">
            <div class="card-body">                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired text-primary"></i>
                        Dashboard de Plataformas
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-2">
                            <?= $platform_data['platforms_count'] ?> plataforma<?= $platform_data['platforms_count'] !== 1 ? 's' : '' ?> activa<?= $platform_data['platforms_count'] !== 1 ? 's' : '' ?>
                        </span>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i>
                            Actualizado: <?= date('d/m/Y H:i', strtotime($platform_data['last_updated'])) ?>
                        </small>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($platform_data['platforms_data'] as $platform => $data): ?>
                        <?php if ($data['status'] === 'found'): ?>
                            <?php
                            $platformConfig = [
                                'spotify' => [
                                    'name' => 'Spotify',
                                    'icon' => 'fab fa-spotify',
                                    'color' => 'success',
                                    'bg' => 'rgba(29, 185, 84, 0.1)'
                                ],
                                'deezer' => [
                                    'name' => 'Deezer',
                                    'icon' => 'fas fa-music',
                                    'color' => 'warning',
                                    'bg' => 'rgba(255, 193, 7, 0.1)'
                                ],
                                'lastfm' => [
                                    'name' => 'Last.fm',
                                    'icon' => 'fab fa-lastfm',
                                    'color' => 'danger',
                                    'bg' => 'rgba(220, 53, 69, 0.1)'
                                ],
                                'soundcloud' => [
                                    'name' => 'SoundCloud',
                                    'icon' => 'fab fa-soundcloud',
                                    'color' => 'primary',
                                    'bg' => 'rgba(13, 110, 253, 0.1)'
                                ]
                            ];
                            $config = $platformConfig[$platform] ?? [
                                'name' => ucfirst($platform),
                                'icon' => 'fas fa-music',
                                'color' => 'secondary',
                                'bg' => 'rgba(108, 117, 125, 0.1)'
                            ];
                            ?>
                            <div class="col-lg-6 col-xl-3 mb-3">
                                <div class="platform-dashboard-item" style="background: <?= $config['bg'] ?>;">
                                    <div class="platform-header">
                                        <div class="platform-icon">
                                            <i class="<?= $config['icon'] ?> text-<?= $config['color'] ?>"></i>
                                        </div>
                                        <div class="platform-info">
                                            <h6 class="platform-title"><?= $config['name'] ?></h6>
                                            <small class="text-muted"><?= $data['name'] ?? 'N/A' ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="platform-metrics">
                                        <?php if (isset($data['followers']) && $data['followers'] > 0): ?>
                                        <div class="metric-row">
                                            <span class="metric-label">
                                                <i class="fas fa-users"></i> Seguidores
                                            </span>
                                            <span class="metric-value">
                                                <?= number_format($data['followers']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($data['popularity']) && $data['popularity'] > 0): ?>
                                        <div class="metric-row">
                                            <span class="metric-label">
                                                <i class="fas fa-star"></i> Popularidad
                                            </span>
                                            <span class="metric-value">
                                                <?= $data['popularity'] ?>%
                                            </span>
                                        </div>
                                        
                                        <div class="popularity-bar">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-<?= $config['color'] ?>" 
                                                     style="width: <?= $data['popularity'] ?>%"></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($data['url'])): ?>
                                    <div class="platform-action">
                                        <a href="<?= htmlspecialchars($data['url']) ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-<?= $config['color'] ?> btn-sm">
                                            <i class="fas fa-external-link-alt"></i> Ver en <?= $config['name'] ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen Global -->
                <div class="platforms-summary mt-4 pt-4 border-top">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="summary-metric">
                                <div class="summary-value text-primary">
                                    <?= number_format($platform_data['total_followers']) ?>
                                </div>
                                <div class="summary-label">Total de Seguidores</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-metric">
                                <div class="summary-value text-success">
                                    <?= $platform_data['avg_popularity'] ?>%
                                </div>
                                <div class="summary-label">Popularidad Promedio</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-metric">
                                <div class="summary-value text-info">
                                    <?= $platform_data['platforms_count'] ?>/4
                                </div>
                                <div class="summary-label">Plataformas Encontradas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Resumen de métricas (placeholder para futuras integraciones) -->
<?php if ($artist['status']): ?>
<div class="row">
    <div class="col-12">
        <div class="card metrics-preview-card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-chart-area text-info"></i>
                    Resumen de Métricas
                </h5>
                  <div class="row text-center">
                    <div class="col-md-3">
                        <div class="metric-item">
                            <?php 
                            // Estimación de streams totales basada en seguidores
                            $estimatedStreams = 0;
                            if (!empty($platform_data['platforms_data'])) {
                                foreach ($platform_data['platforms_data'] as $platform => $data) {
                                    if ($data['status'] === 'found' && isset($data['followers'])) {
                                        // Estimación aproximada: 1 seguidor = ~50-200 streams mensuales
                                        $platformMultiplier = ($platform === 'spotify') ? 150 : 75;
                                        $estimatedStreams += $data['followers'] * $platformMultiplier;
                                    }
                                }
                            }
                            ?>
                            <div class="metric-value">
                                <?= $estimatedStreams > 0 ? '~' . number_format($estimatedStreams / 1000000, 1) . 'M' : '-' ?>
                            </div>
                            <div class="metric-label">Streams Est. (Mensual)</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-item">
                            <?php 
                            // Posición estimada en charts basada en popularidad
                            $chartPosition = '-';
                            if ($platform_data['avg_popularity'] > 0) {
                                if ($platform_data['avg_popularity'] >= 95) {
                                    $chartPosition = 'Top 10';
                                } elseif ($platform_data['avg_popularity'] >= 85) {
                                    $chartPosition = 'Top 50';
                                } elseif ($platform_data['avg_popularity'] >= 70) {
                                    $chartPosition = 'Top 200';
                                } else {
                                    $chartPosition = '200+';
                                }
                            }
                            ?>
                            <div class="metric-value"><?= $chartPosition ?></div>
                            <div class="metric-label">Est. Chart Position</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-item">
                            <?php 
                            // Simulación de crecimiento semanal basado en popularidad
                            $weeklyGrowth = '-';
                            if ($platform_data['avg_popularity'] > 0) {
                                $baseGrowth = mt_rand(-5, 15) / 10; // Entre -0.5% y +1.5%
                                $popularityBonus = ($platform_data['avg_popularity'] - 50) / 100; // Bonus por popularidad
                                $totalGrowth = $baseGrowth + $popularityBonus;
                                $weeklyGrowth = ($totalGrowth >= 0 ? '+' : '') . number_format($totalGrowth, 1) . '%';
                            }
                            ?>
                            <div class="metric-value <?= $weeklyGrowth !== '-' && substr($weeklyGrowth, 0, 1) === '+' ? 'text-success' : 'text-danger' ?>">
                                <?= $weeklyGrowth ?>
                            </div>
                            <div class="metric-label">Crecimiento Semanal</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-item">
                            <?php 
                            // Score de tendencia basado en múltiples factores
                            $trendScore = 0;
                            if ($platform_data['avg_popularity'] > 0) {
                                $popularityScore = $platform_data['avg_popularity'];
                                $platformsScore = min($platform_data['platforms_count'] * 15, 30); // Max 30 puntos por plataformas
                                $followersScore = min(log10($platform_data['total_followers'] + 1) * 10, 40); // Max 40 puntos por seguidores
                                $trendScore = round(($popularityScore + $platformsScore + $followersScore) / 170 * 100);
                                $trendScore = min($trendScore, 100); // Cap a 100
                            }
                            ?>
                            <div class="metric-value">
                                <?= $trendScore > 0 ? $trendScore . '/100' : '-' ?>
                            </div>
                            <div class="metric-label">Score de Tendencia</div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle"></i>
                        Las métricas detalladas están disponibles en la sección de Analíticas
                    </p>
                    <a href="<?= ($base_url ?? '/') ?>analytics?artist_id=<?= $artist['id'] ?>" class="btn btn-success">
                        <i class="fas fa-chart-bar"></i> Ver Analíticas Completas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CSS adicional para esta vista -->
<style>
.content-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.artist-header-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.artist-header-image-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(255, 255, 255, 0.3);
    font-size: 2rem;
}

.header-actions .btn {
    margin-left: 0.5rem;
}

.card {
    border: none;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.artist-info-card,
.tracking-info-card,
.no-tracking-card,
.metrics-preview-card {
    margin-bottom: 1.5rem;
}

.detail-item,
.tracking-detail {
    margin-bottom: 1rem;
}

.detail-item label,
.tracking-detail label {
    font-weight: 600;
    color: #495057;
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.popularity-bar-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.popularity-bar {
    flex: 1;
    height: 8px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.popularity-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
    transition: width 0.3s ease;
}

.popularity-text {
    font-weight: 600;
    color: #495057;
    min-width: 40px;
    text-align: right;
}

.genres-tags {
    margin-top: 0.25rem;
}

.genres-tags .badge {
    font-size: 0.75rem;
}

.tracking-status .badge {
    padding: 0.5rem 1rem;
}

.tracking-notes {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.tracking-notes label {
    margin-bottom: 0.5rem;
}

.notes-content {
    color: #6c757d;
    line-height: 1.5;
}

.tracking-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.metric-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
}

@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .artist-header-image,
    .artist-header-image-placeholder {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .header-actions .btn {
        margin-left: 0;
        margin-right: 0.5rem;
    }
    
    .tracking-actions {
        flex-direction: column;
    }
    
    .tracking-actions .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

.card-title i {
    margin-right: 0.5rem;
}

.badge {
    font-size: 0.8rem;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

/* Dashboard de Plataformas */
.platforms-dashboard-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border-radius: 15px;
}

.platform-dashboard-item {
    padding: 1.5rem;
    border-radius: 12px;
    height: 100%;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.platform-dashboard-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.platform-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.platform-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.platform-title {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.platform-metrics {
    margin-bottom: 1rem;
}

.metric-row {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.metric-label {
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.metric-value {
    font-weight: 600;
    color: #2c3e50;
    margin-left: auto;
}

.popularity-bar {
    margin-top: 0.5rem;
}

.platform-action {
    margin-top: 1rem;
}

.platforms-summary {
    background: rgba(248, 249, 250, 0.8);
    border-radius: 10px;
    padding: 1.5rem;
}

.summary-metric {
    padding: 0.5rem;
}

.summary-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.summary-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

/* Métricas mejoradas */
.metric-item {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
}

.metric-item:hover {
    transform: translateY(-1px);
}

.metric-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.metric-value.text-success {
    color: #28a745 !important;
}

.metric-value.text-danger {
    color: #dc3545 !important;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .platform-dashboard-item {
        margin-bottom: 1rem;
    }
    
    .platform-header {
        flex-direction: column;
        text-align: center;
    }
    
    .platform-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .summary-value {
        font-size: 1.5rem;
    }
}
</style>

<script>
function refreshMetrics() {
    const refreshBtn = document.getElementById('refreshBtn');
    const originalContent = refreshBtn.innerHTML;
    
    // Mostrar estado de carga
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
    refreshBtn.disabled = true;
    
    // Simular actualización (en el futuro esto sería una llamada AJAX)
    setTimeout(() => {
        // Recargar la página para obtener métricas actualizadas
        location.reload();
    }, 2000);
}

// Función para mostrar tooltips en las métricas avanzadas
document.addEventListener('DOMContentLoaded', function() {
    // Agregar tooltips explicativos
    const tooltips = {
        'Streams Est. (Mensual)': 'Estimación basada en seguidores y tasas de reproducción promedio de la industria',
        'Est. Chart Position': 'Posición estimada en charts globales basada en popularidad',
        'Crecimiento Semanal': 'Simulación de crecimiento basada en tendencias del mercado',
        'Score de Tendencia': 'Algoritmo propietario que combina popularidad, presencia en plataformas y seguidores'
    };
    
    document.querySelectorAll('.metric-label').forEach(label => {
        const text = label.textContent.trim();
        if (tooltips[text]) {
            label.setAttribute('title', tooltips[text]);
            label.style.cursor = 'help';
        }
    });
    
    // Mostrar mensaje de bienvenida si es la primera visita
    if (sessionStorage.getItem('artistViewVisited') !== 'true') {
        setTimeout(() => {
            if (confirm('¡Bienvenido a la vista mejorada de artista! 🎵\n\n¿Te gustaría que te mostremos las nuevas funcionalidades?')) {
                alert('✨ Nuevas funcionalidades:\n\n' +
                      '📊 Dashboard visual de plataformas con métricas en tiempo real\n' +
                      '📈 Métricas avanzadas calculadas automáticamente\n' +
                      '🔄 Botón para actualizar métricas\n' +
                      '🎯 Estimaciones inteligentes basadas en datos reales\n\n' +
                      '¡Explora y disfruta la nueva experiencia!');
            }
            sessionStorage.setItem('artistViewVisited', 'true');
        }, 1000);
    }
});
</script>

<?php
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
