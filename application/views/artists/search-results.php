<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">        <div>
            <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
            <p class="mb-0 mt-1">
                <i class="fas fa-search"></i>
                Resultados para: "<strong><?= htmlspecialchars($search_term) ?></strong>"
                <?php if (isset($selected_platforms) && !in_array('all', $selected_platforms)): ?>
                    <br><small class="text-muted">
                        Buscando en: 
                        <?php 
                        $platformLabels = [
                            'spotify' => 'Spotify',
                            'deezer' => 'Deezer', 
                            'youtube_music' => 'YouTube Music',
                            'apple_music' => 'Apple Music',
                            'amazon_music' => 'Amazon Music'
                        ];
                        $labels = array_map(function($p) use ($platformLabels) {
                            return $platformLabels[$p] ?? ucfirst($p);
                        }, $selected_platforms);
                        echo implode(', ', $labels);
                        ?>
                    </small>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-outline-light">
            <i class="fas fa-search"></i> Nueva Búsqueda
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($results['combined_results'])): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">Sin resultados</h4>
                <p class="text-muted mb-4">
                    No se encontraron artistas con el nombre "<?= htmlspecialchars($search_term) ?>".
                    <br>Intenta con una búsqueda diferente o revisa las plataformas disponibles.
                </p>
                
                <!-- Mostrar estado de plataformas con más detalle -->
                <?php if (!empty($results['platforms'])): ?>
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Estado de las plataformas:</h6>
                    <div class="row justify-content-center">
                        <?php foreach ($results['platforms'] as $platform => $data): ?>
                        <div class="col-auto mb-2">
                            <?php if ($data['status'] === 'success'): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> <?= ucfirst($platform) ?>: <?= $data['count'] ?> resultados
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger" title="<?= htmlspecialchars($data['error'] ?? 'Error desconocido') ?>">
                                    <i class="fas fa-times"></i> <?= ucfirst($platform) ?>: Error
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Sugerencias si hay errores -->
                    <?php 
                    $hasErrors = false;
                    foreach ($results['platforms'] as $data) {
                        if ($data['status'] !== 'success') {
                            $hasErrors = true;
                            break;
                        }
                    }
                    ?>
                    <?php if ($hasErrors): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Algunas plataformas no están disponibles:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Verifica tu conexión a internet</li>
                            <li>Algunas APIs pueden estar temporalmente no disponibles</li>
                            <li>Contacta al administrador si el problema persiste</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <a href="<?= ($base_url ?? '/') ?>artists/search" class="btn btn-primary mt-3">
                    <i class="fas fa-search"></i> Realizar Nueva Búsqueda
                </a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Estadísticas de búsqueda -->
        <div class="search-stats mb-4">
            <div class="card bg-light">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                <span>Se encontraron <strong><?= $results['total_results'] ?></strong> artistas únicos</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <!-- Badges de plataformas -->
                            <?php foreach ($results['platforms'] as $platform => $data): ?>
                                <span class="badge <?= $data['status'] === 'success' ? 'bg-success' : 'bg-warning' ?> me-1">
                                    <?= ucfirst($platform) ?>: <?= $data['count'] ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="results-grid">
            <?php foreach ($results['combined_results'] as $index => $artist): ?>
            <div class="artist-card" data-artist-index="<?= $index ?>">
                <div class="card h-100">
                    <div class="artist-image">
                        <?php if (!empty($artist['image'])): ?>
                        <img src="<?= htmlspecialchars($artist['image']) ?>" 
                             alt="<?= htmlspecialchars($artist['name']) ?>"
                             class="card-img-top">
                        <?php else: ?>
                        <div class="card-img-top artist-placeholder">
                            <i class="fas fa-user-music"></i>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Badge de popularidad -->
                        <?php if (isset($artist['popularity']) && $artist['popularity'] > 0): ?>
                        <div class="popularity-badge">
                            <span class="badge bg-dark"><?= $artist['popularity'] ?>%</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?= htmlspecialchars($artist['name']) ?></h5>
                        
                        <!-- Información de plataformas -->
                        <div class="platforms-info mb-3">
                            <?php 
                            $platformIcons = [
                                'spotify' => 'fab fa-spotify text-success',
                                'deezer' => 'fas fa-music text-primary',
                                'youtube_music' => 'fab fa-youtube text-danger',
                                'apple_music' => 'fab fa-apple text-dark',
                                'amazon_music' => 'fab fa-amazon text-warning'
                            ];
                            ?>
                            <?php foreach ($artist['platforms'] as $platform => $platformData): ?>
                                <?php $iconClass = $platformIcons[$platform] ?? 'fas fa-music text-secondary'; ?>
                                <div class="platform-item mb-1">
                                    <i class="<?= $iconClass ?>"></i>
                                    <span class="platform-name"><?= ucfirst(str_replace('_', ' ', $platform)) ?></span>
                                    <?php if (!empty($platformData['followers'])): ?>
                                        <span class="followers-count text-muted ms-2">
                                            <?= number_format($platformData['followers']) ?> seguidores
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Géneros -->
                        <?php if (!empty($artist['genres'])): ?>
                        <div class="genres mb-3">
                            <?php foreach (array_slice($artist['genres'], 0, 3) as $genre): ?>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($genre) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Métricas -->
                        <div class="artist-metrics mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="metric">
                                        <div class="metric-value"><?= $artist['popularity'] ?? 0 ?></div>
                                        <div class="metric-label">Popularidad</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric">
                                        <div class="metric-value"><?= count($artist['platforms']) ?></div>
                                        <div class="metric-label">Plataformas</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric">
                                        <div class="metric-value">
                                            <?php 
                                            $totalFollowers = 0;
                                            foreach ($artist['platforms'] as $pData) {
                                                $totalFollowers += $pData['followers'] ?? 0;
                                            }
                                            echo $totalFollowers > 0 ? (($totalFollowers >= 1000000) ? number_format($totalFollowers/1000000, 1) . 'M' : (($totalFollowers >= 1000) ? number_format($totalFollowers/1000, 1) . 'K' : $totalFollowers)) : 'N/A';
                                            ?>
                                        </div>
                                        <div class="metric-label">Seguidores</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <form method="POST" action="<?= ($base_url ?? '/') ?>artists/add" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="artist_name" value="<?= htmlspecialchars($artist['name']) ?>">
                            <input type="hidden" name="platforms_data" value="<?= htmlspecialchars(json_encode($artist['platforms'])) ?>">
                            <input type="hidden" name="image_url" value="<?= htmlspecialchars($artist['image'] ?? '') ?>">
                            <input type="hidden" name="popularity" value="<?= $artist['popularity'] ?? 0 ?>">
                            <input type="hidden" name="followers" value="<?= $totalFollowers ?>">
                            <input type="hidden" name="genres" value="<?= htmlspecialchars(json_encode($artist['genres'] ?? [])) ?>">
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Agregar a Seguimiento
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<!-- CSS adicional -->
<style>
.content-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.artist-card {
    transition: transform 0.3s ease;
}

.artist-card:hover {
    transform: translateY(-5px);
}

.artist-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.artist-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.artist-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.popularity-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.metric {
    padding: 0.5rem;
}

.metric-value {
    font-size: 1.25rem;
    font-weight: bold;
    color: #495057;
}

.metric-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
}

.platform-item {
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .results-grid {
        grid-template-columns: 1fr;
    }
    
    .content-header {
        padding: 1rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
