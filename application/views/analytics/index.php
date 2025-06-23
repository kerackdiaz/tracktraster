<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <?php if ($selected_artist): ?>
        <div class="header-actions">
            <a href="<?= ($base_url ?? '/tracktraster/') ?>analytics/export?artist_id=<?= $selected_artist['id'] ?>" 
               class="btn btn-outline-light me-2">
                <i class="fas fa-download"></i> Exportar Datos
            </a>
            <a href="<?= ($base_url ?? '/tracktraster/') ?>reports/artist/<?= $selected_artist['id'] ?>" 
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
                            <option value="">Selecciona un artista...</option>
                            <?php foreach ($tracked_artists as $artist): ?>
                            <option value="<?= $artist['id'] ?>" 
                                    <?= ($selected_artist && $selected_artist['id'] == $artist['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($artist['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <?php if (empty($tracked_artists)): ?>
                        <div class="text-center">
                            <p class="text-muted mb-2">No tienes artistas en seguimiento</p>
                            <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/search" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Artista
                            </a>
                        </div>
                        <?php elseif ($selected_artist): ?>
                        <div class="selected-artist-info text-center">
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
                <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/search" class="btn btn-primary btn-lg">
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
            
            <!-- Métricas principales -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon streams">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="metric-content">
                            <h3 class="metric-value"><?= number_format($analytics['summary']['total_streams']) ?></h3>
                            <p class="metric-label">Streams Totales</p>
                            <div class="metric-trend <?= $analytics['trends']['streams_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['streams_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs($analytics['trends']['streams_trend']) ?>% vs semana anterior
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon followers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-content">
                            <h3 class="metric-value">+<?= number_format($analytics['summary']['followers_growth']) ?></h3>
                            <p class="metric-label">Nuevos Seguidores</p>
                            <div class="metric-trend <?= $analytics['trends']['followers_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['followers_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs($analytics['trends']['followers_trend']) ?>% crecimiento
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon charts">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="metric-content">
                            <h3 class="metric-value">#<?= $analytics['summary']['chart_position'] ?></h3>
                            <p class="metric-label">Posición Charts</p>
                            <div class="metric-trend <?= $analytics['trends']['popularity_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['popularity_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs($analytics['trends']['popularity_trend']) ?> posiciones
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card">
                        <div class="metric-icon social">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <div class="metric-content">
                            <h3 class="metric-value"><?= number_format($analytics['summary']['social_mentions']) ?></h3>
                            <p class="metric-label">Menciones Sociales</p>
                            <div class="metric-trend <?= $analytics['trends']['engagement_trend'] >= 0 ? 'positive' : 'negative' ?>">
                                <i class="fas fa-arrow-<?= $analytics['trends']['engagement_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs($analytics['trends']['engagement_trend']) ?>% engagement
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line text-primary"></i>
                                Streams Diarios (Últimos 30 días)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="streamsChart" height="300"></canvas>
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
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-plus text-info"></i>
                                Crecimiento de Seguidores
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="followersChart" height="200"></canvas>
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
        window.location.href = '<?= ($base_url ?? '/tracktraster/') ?>analytics?artist_id=' + selector.value;
    }
}

<?php if ($selected_artist && $analytics): ?>
// Datos para los gráficos
const streamsData = <?= json_encode($analytics['charts']['daily_streams']) ?>;
const followersData = <?= json_encode($analytics['charts']['followers_growth']) ?>;
const popularityData = <?= json_encode($analytics['charts']['popularity_score']) ?>;

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

// Gráfico de Streams
const streamsCtx = document.getElementById('streamsChart').getContext('2d');
new Chart(streamsCtx, {
    type: 'line',
    data: {
        labels: streamsData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            data: streamsData.map(item => item.value),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: commonOptions
});

// Gráfico de Seguidores
const followersCtx = document.getElementById('followersChart').getContext('2d');
new Chart(followersCtx, {
    type: 'bar',
    data: {
        labels: followersData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            data: followersData.map(item => item.value),
            backgroundColor: '#28a745',
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
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/dashboard.php';
?>
