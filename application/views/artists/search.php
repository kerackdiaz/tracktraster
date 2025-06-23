<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= ($base_url ?? '/') ?>artists" class="btn btn-outline-light">
            <i class="fas fa-arrow-left"></i> Volver a Artistas
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card search-card">
            <div class="card-body">
                
                <!-- Mensajes flash -->
                <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>                <div class="text-center mb-4">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4 class="mt-3 mb-2">Buscar Artista</h4>
                    <p class="text-muted">
                        Encuentra artistas en múltiples plataformas musicales y comienza a analizar su crecimiento en LATAM
                    </p>
                </div>                <!-- Selector visual de plataformas -->
                <div class="platform-selector-visual mb-4">
                    <h6 class="text-muted mb-3 text-center">Selecciona las plataformas donde buscar:</h6>
                    <div class="platform-toggle-group">
                        <?php
                        // Solo incluir plataformas disponibles y funcionales
                        $platformConfig = [
                            'all' => ['icon' => 'fas fa-globe', 'color' => 'primary', 'label' => 'Todas'],
                            'spotify' => ['icon' => 'fab fa-spotify', 'color' => 'success', 'label' => 'Spotify'],
                            'deezer' => ['icon' => 'fas fa-music', 'color' => 'primary', 'label' => 'Deezer'],
                            'lastfm' => ['icon' => 'fab fa-lastfm', 'color' => 'danger', 'label' => 'Last.fm']
                        ];
                        // Solo plataformas activas y funcionales
                        $platformOrder = ['spotify', 'deezer', 'lastfm'];
                        ?>
                        <?php foreach ($platformConfig as $platform => $config): ?>
                            <?php 
                            $isAvailable = $platform === 'all' || (isset($available_platforms[$platform]) && ($available_platforms[$platform]['enabled'] ?? false));
                            $isDisabled = !$isAvailable && $platform !== 'all';
                            ?>
                            <div class="platform-toggle <?= $isDisabled ? 'disabled' : '' ?>" 
                                 data-platform="<?= $platform ?>"
                                 data-available="<?= $isAvailable ? 'true' : 'false' ?>">
                                <input type="checkbox" 
                                       name="platforms[]" 
                                       value="<?= $platform ?>" 
                                       id="platform_<?= $platform ?>"
                                       <?= $platform === 'all' ? 'checked' : '' ?>
                                       <?= $isDisabled ? 'disabled' : '' ?>>
                                <label for="platform_<?= $platform ?>" class="platform-badge badge-<?= $config['color'] ?>">
                                    <i class="<?= $config['icon'] ?>"></i>
                                    <span><?= $config['label'] ?></span>
                                    <?php if ($isDisabled && $platform !== 'all'): ?>
                                        <small class="unavailable-text">No disponible</small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    // Mensaje verde de plataformas activas
                    $enabledPlatforms = [];
                    foreach ($platformOrder as $p) {
                        if (isset($available_platforms[$p]) && ($available_platforms[$p]['enabled'] ?? false)) {
                            $enabledPlatforms[] = $platformConfig[$p]['label'];
                        }
                    }
                    ?>
                    <?php if (count($enabledPlatforms) > 0): ?>
                        <div class="alert alert-success mt-3 mb-0 text-center">
                            <i class="fas fa-check-circle"></i> <strong>Todas las plataformas seleccionadas</strong><br>
                            Se buscará en: <?= htmlspecialchars(implode(', ', $enabledPlatforms)) ?> (<?= count($enabledPlatforms) ?> plataforma<?= count($enabledPlatforms) > 1 ? 's' : '' ?> disponible<?= count($enabledPlatforms) > 1 ? 's' : '' ?>)
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3 mb-0 text-center">
                            <i class="fas fa-exclamation-triangle"></i> No hay plataformas activas configuradas en el sistema.
                        </div>
                    <?php endif; ?>
                </div><form method="POST" id="searchForm" class="search-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="search-input-group">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="fas fa-music"></i>
                                    </span>
                                    <input type="text" 
                                           name="artist_name" 
                                           class="form-control" 
                                           placeholder="Nombre del artista..." 
                                           required 
                                           autocomplete="off"
                                           value="<?= htmlspecialchars($_POST['artist_name'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="searchBtn">
                            <i class="fas fa-search"></i> Buscar Artista
                        </button>
                    </div>
                </form>                <!-- Tips de búsqueda -->
                <div class="search-tips mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-lightbulb"></i> Tips de búsqueda:
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success"></i> Usa el nombre completo del artista</li>
                                <li><i class="fas fa-check text-success"></i> Evita caracteres especiales</li>
                                <li><i class="fas fa-check text-success"></i> Selecciona las plataformas de tu interés</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-info-circle text-info"></i> Búsqueda en múltiples plataformas</li>
                                <li><i class="fas fa-info-circle text-info"></i> Datos actualizados en tiempo real</li>
                                <li><i class="fas fa-info-circle text-info"></i> Incluye métricas de popularidad</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Artistas populares sugeridos -->
                <div class="popular-artists mt-5">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-fire"></i> Artistas populares en LATAM:
                    </h6>
                    <div class="row">
                        <?php 
                        $popularArtists = [
                            'Bad Bunny', 'Karol G', 'J Balvin', 'Shakira', 
                            'Ozuna', 'Maluma', 'Anuel AA', 'Daddy Yankee'
                        ];
                        foreach ($popularArtists as $artist): 
                        ?>
                        <div class="col-md-3 col-6 mb-2">
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm w-100 suggested-artist"
                                    data-artist="<?= htmlspecialchars($artist) ?>">
                                <?= htmlspecialchars($artist) ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

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

.search-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 15px;
}

.search-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
}

.search-input-group {
    margin: 2rem 0;
}

.input-group-lg .form-control {
    border-radius: 0;
    border: 2px solid #e9ecef;
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
}

/* Platform selector styling - New Visual Badge System */
.platform-selector-visual {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin: 2rem 0;
}

.platform-toggle-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
    margin-bottom: 1rem;
}

.platform-toggle {
    position: relative;
}

.platform-toggle input[type="checkbox"] {
    display: none;
}

.platform-toggle .platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    opacity: 0.6;
    transform: scale(0.95);
    filter: grayscale(0.3);
}

.platform-toggle .platform-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.platform-toggle:hover .platform-badge::before {
    opacity: 1;
}

.platform-toggle:hover .platform-badge {
    transform: scale(1.02);
    opacity: 0.8;
}

/* Badge colors - Estado NO seleccionado */
.platform-badge.badge-primary,
.platform-badge.badge-success,
.platform-badge.badge-danger,
.platform-badge.badge-dark,
.platform-badge.badge-warning {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: #fff;
}

/* Selected state - ESTADO CLARAMENTE VISIBLE */
.platform-toggle input[type="checkbox"]:checked + .platform-badge {
    opacity: 1;
    transform: scale(1.05);
    filter: none;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3), 0 4px 12px rgba(0, 0, 0, 0.2);
    position: relative;
}

/* Selected state - Colores de marca SOLO cuando está seleccionado */
.platform-toggle input[type="checkbox"]:checked + .platform-badge.badge-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.platform-toggle input[type="checkbox"]:checked + .platform-badge.badge-success {
    background: linear-gradient(135deg, #1db954, #1ed760);
    color: white;
}

.platform-toggle input[type="checkbox"]:checked + .platform-badge.badge-danger {
    background: linear-gradient(135deg, #ff0000, #cc0000);
    color: white;
}

.platform-toggle input[type="checkbox"]:checked + .platform-badge.badge-dark {
    background: linear-gradient(135deg, #343a40, #212529);
    color: white;
}

.platform-toggle input[type="checkbox"]:checked + .platform-badge.badge-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

/* Icono de check para estado seleccionado */
.platform-toggle input[type="checkbox"]:checked + .platform-badge::after {
    content: '✓';
    position: absolute;
    top: -5px;
    right: -5px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    border: 2px solid #fff;
}

/* Indicador de estado de selección */
.platform-selector-visual .selection-indicator {
    text-align: center;
    padding: 0.875rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    font-weight: 500;
    margin-top: 0.5rem;
    transition: all 0.3s ease;
}

.platform-selector-visual .selection-indicator.has-selection {
    background: rgba(40, 167, 69, 0.2);
    border: 1px solid rgba(40, 167, 69, 0.3);
    color: #28a745;
}

.platform-selector-visual .selection-indicator.no-selection {
    background: rgba(108, 117, 125, 0.2);
    border: 1px solid rgba(108, 117, 125, 0.3);
    color: #6c757d;
}

/* Efecto de pulso MÁS SUTIL para plataformas no seleccionadas */
.platform-toggle:not(:has(input:checked)) .platform-badge {
    animation: subtle-pulse 4s ease-in-out infinite;
}

@keyframes subtle-pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.7; }
}

/* Detener animación al hacer hover */
.platform-toggle:hover .platform-badge {
    animation: none;
    opacity: 0.8 !important;
}

/* Disabled state */
.platform-toggle.disabled .platform-badge {
    opacity: 0.5;
    cursor: not-allowed;
    filter: grayscale(0.5);
}

.platform-toggle .unavailable-text {
    display: block;
    font-size: 0.7rem;
    opacity: 0.8;
    margin-top: 0.2rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .platform-toggle-group {
        gap: 0.5rem;
    }
    
    .platform-toggle .platform-badge {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
}

/* Responsive design y mejoras adicionales */
.search-tips {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-top: 2rem;
}

.popular-artists {
    background: #fff;
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    margin-top: 1.5rem;
}

.suggested-artist {
    transition: all 0.3s ease;
    border-radius: 20px;
}

.suggested-artist:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
    transform: translateY(-2px);
}

.alert {
    border-radius: 10px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .search-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .platform-selector-visual {
        padding: 1rem;
        margin: 1.5rem 0;
    }
    
    .platform-toggle-group {
        gap: 0.5rem;
    }
    
    .platform-toggle .platform-badge {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    .input-group-lg .form-control {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    .input-group-lg .btn {
        padding: 0.75rem 1.5rem;
    }
    
    .search-tips, 
    .popular-artists {
        padding: 1rem;
        margin-top: 1rem;
    }
}

/* Loading state */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading .btn {
    position: relative;
}

.loading .btn::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- JavaScript para esta vista -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const artistInput = searchForm.querySelector('input[name="artist_name"]');
    
    // Handle platform selection
    const platformToggles = document.querySelectorAll('.platform-toggle input[type="checkbox"]');
    const allToggle = document.querySelector('input[value="all"]');
    
    // Initialize platform selection logic
    function initializePlatformSelection() {
        platformToggles.forEach(toggle => {
            toggle.addEventListener('change', handlePlatformToggle);
        });
    }
    
    function handlePlatformToggle(event) {
        const currentToggle = event.target;
        const isAll = currentToggle.value === 'all';
        
        if (isAll && currentToggle.checked) {
            // If "All" is selected, uncheck others
            platformToggles.forEach(toggle => {
                if (toggle.value !== 'all') {
                    toggle.checked = false;
                }
            });
        } else if (!isAll && currentToggle.checked) {
            // If specific platform is selected, uncheck "All"
            if (allToggle) {
                allToggle.checked = false;
            }
        }
        
        // If no platforms are selected, auto-select "All"
        const anySelected = Array.from(platformToggles).some(toggle => toggle.checked);
        if (!anySelected && allToggle) {
            allToggle.checked = true;
        }
        
        updateVisualFeedback();
    }    function updateVisualFeedback() {
        const selectedCount = Array.from(platformToggles).filter(t => t.checked && t.value !== 'all').length;
        const isAllSelected = allToggle && allToggle.checked;
        
        // Contar plataformas disponibles
        const availablePlatforms = Array.from(platformToggles)
            .filter(t => t.value !== 'all' && !t.disabled)
            .map(t => t.closest('.platform-toggle').querySelector('label span').textContent);
        
        // Update selection indicator ÚNICO
        const selectionIndicator = document.getElementById('selectionIndicator');
        
        if (selectionIndicator) {
            if (isAllSelected) {
                selectionIndicator.className = 'selection-indicator has-selection';
                selectionIndicator.innerHTML = `<i class="fas fa-check-circle"></i> <strong>Todas las plataformas seleccionadas</strong><br><small>Se buscará en: ${availablePlatforms.join(', ')} (${availablePlatforms.length} plataforma${availablePlatforms.length > 1 ? 's' : ''} disponible${availablePlatforms.length > 1 ? 's' : ''})</small>`;
            } else if (selectedCount > 0) {
                const selectedPlatforms = Array.from(platformToggles)
                    .filter(t => t.checked && t.value !== 'all')
                    .map(t => {
                        const label = t.closest('.platform-toggle').querySelector('label span').textContent;
                        return label;
                    });
                selectionIndicator.className = 'selection-indicator has-selection';
                selectionIndicator.innerHTML = `<i class="fas fa-check-circle"></i> <strong>${selectedCount} plataforma${selectedCount > 1 ? 's' : ''} seleccionada${selectedCount > 1 ? 's' : ''}</strong><br><small>Buscando en: ${selectedPlatforms.join(', ')}</small>`;
            } else {
                selectionIndicator.className = 'selection-indicator no-selection';
                selectionIndicator.innerHTML = `<i class="fas fa-hand-pointer"></i> <strong>Selecciona las plataformas donde buscar</strong><br><small>Disponibles: ${availablePlatforms.join(', ')} - Las plataformas seleccionadas aparecerán con colores brillantes y un ✓</small>`;
            }
        }
    }
      // Initialize everything
    initializePlatformSelection();
    updateVisualFeedback();

    // Handle suggested artists
    const suggestedArtists = document.querySelectorAll('.suggested-artist');
    suggestedArtists.forEach(button => {
        button.addEventListener('click', function() {
            const artistName = this.getAttribute('data-artist');
            artistInput.value = artistName;
            artistInput.focus();
        });
    });

    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        const artistName = artistInput.value.trim();
        if (!artistName) {
            e.preventDefault();
            showAlert('error', 'Por favor ingresa el nombre de un artista');
            return;
        }

        // Show loading state
        searchForm.classList.add('loading');
        const submitBtn = searchForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            submitBtn.disabled = true;
        }
    });

    // Auto-focus on input
    artistInput.focus();

    // Handle enter key properly
    artistInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchForm.submit();
        }
    });
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.card-body');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php 
$content = ob_get_clean();
$base_url = '/';
include APPPATH . 'views/layouts/dashboard.php';
?>
