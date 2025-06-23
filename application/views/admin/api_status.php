<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <div>
            <button id="refreshTests" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt"></i> Refrescar Pruebas
            </button>
            <a href="<?= ($base_url ?? '/tracktraster/') ?>admin" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Estado de las APIs -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server"></i> Estado de Plataformas Musicales
                </h5>
                <small class="text-muted">
                    Configuración basada en variables de entorno (.env) - Última verificación: <?= $last_check ?>
                </small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle mt-1 me-2"></i>
                        <div>
                            <strong>Configuración Manual Requerida</strong>
                            <p class="mb-2">
                                Las credenciales se configuran directamente en el archivo <code>.env</code> por seguridad.
                            </p>
                            <p class="mb-0">
                                <strong>Para configurar:</strong> Edita el archivo <code>.env</code> en la raíz del proyecto y reinicia el servidor.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row" id="apiStatusGrid">
                    <?php foreach ($api_status as $platform => $status): ?>
                    <div class="col-md-6 mb-3">
                        <div class="api-platform-card">
                            <div class="platform-header">
                                <div class="platform-icon">
                                    <i class="<?= $status['icon'] ?> text-<?= $status['color'] ?>"></i>
                                </div>
                                <div class="platform-info">
                                    <h6 class="platform-name"><?= $status['name'] ?></h6>
                                    <div class="platform-status">
                                        <?php if ($status['enabled'] && $status['configured']): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php elseif ($status['enabled'] && !$status['configured']): ?>
                                            <span class="badge bg-warning">Sin Configurar</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Deshabilitada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="platform-details">
                                <div class="detail-row">
                                    <span class="detail-label">Estado:</span>
                                    <span class="detail-value">
                                        <?= $status['enabled'] ? 'Habilitada' : 'Deshabilitada' ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Configuración:</span>
                                    <span class="detail-value">
                                        <?= $status['configured'] ? 'Completa' : 'Pendiente' ?>
                                    </span>
                                </div>
                                <?php if ($status['enabled'] && !$status['configured']): ?>
                                <div class="detail-row">
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Requiere credenciales en .env
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pruebas Automáticas -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-flask"></i> Pruebas Automáticas
                </h5>
                <small class="text-muted">
                    Conectividad en tiempo real
                </small>
            </div>
            <div class="card-body" id="testResults">
                <?php if (isset($test_results['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($test_results['error']) ?>
                </div>
                <?php else: ?>
                    <?php foreach ($test_results as $platform => $result): ?>
                        <?php if ($platform !== 'error'): ?>
                        <div class="test-result-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="test-platform">
                                    <strong><?= $result['name'] ?></strong>
                                    <br>
                                    <small class="text-muted">Búsqueda: "<?= $result['test_query'] ?>"</small>
                                </div>
                                <div class="test-status">
                                    <?php if ($result['status'] === 'success'): ?>
                                        <span class="badge bg-success">✓ OK</span>
                                        <br>
                                        <small class="text-muted"><?= $result['count'] ?> resultados</small>
                                    <?php elseif ($result['status'] === 'error'): ?>
                                        <span class="badge bg-danger">✗ Error</span>
                                        <br>
                                        <small class="text-danger"><?= htmlspecialchars($result['error']) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">⚠ <?= ucfirst($result['status']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($result['response_time'])): ?>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    Tiempo: <?= $result['response_time'] ?>ms
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Guía de Configuración -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-book"></i> Guía de Configuración Manual
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="configGuide">
                    <!-- Spotify -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#spotifyConfig">
                                <i class="fab fa-spotify text-success me-2"></i>
                                Configurar Spotify
                            </button>
                        </h2>
                        <div id="spotifyConfig" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol>
                                    <li>Ve a <a href="https://developer.spotify.com/dashboard" target="_blank">Spotify Developer Dashboard</a></li>
                                    <li>Crea una nueva aplicación</li>
                                    <li>Copia las credenciales y agrégalas al archivo <code>.env</code>:</li>
                                </ol>
                                <div class="code-block">
                                    <pre><code>SPOTIFY_CLIENT_ID=tu_client_id_aqui
SPOTIFY_CLIENT_SECRET=tu_client_secret_aqui
SPOTIFY_ENABLED=true</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last.fm -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#lastfmConfig">
                                <i class="fab fa-lastfm text-danger me-2"></i>
                                Configurar Last.fm
                            </button>
                        </h2>
                        <div id="lastfmConfig" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol>
                                    <li>Ve a <a href="https://www.last.fm/api/account/create" target="_blank">Last.fm API</a></li>
                                    <li>Crea una nueva aplicación</li>
                                    <li>Agrega las credenciales al archivo <code>.env</code>:</li>
                                </ol>
                                <div class="code-block">
                                    <pre><code>LASTFM_API_KEY=tu_api_key_aqui
LASTFM_API_SECRET=tu_api_secret_aqui
LASTFM_ENABLED=true</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SoundCloud -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#soundcloudConfig">
                                <i class="fab fa-soundcloud text-primary me-2"></i>
                                Configurar SoundCloud
                            </button>
                        </h2>
                        <div id="soundcloudConfig" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Nota:</strong> SoundCloud ha limitado su API pública.
                                </div>
                                <ol>
                                    <li>Si tienes acceso, ve a <a href="https://soundcloud.com/you/apps" target="_blank">SoundCloud Apps</a></li>
                                    <li>Agrega las credenciales al archivo <code>.env</code>:</li>
                                </ol>
                                <div class="code-block">
                                    <pre><code>SOUNDCLOUD_CLIENT_ID=tu_client_id_aqui
SOUNDCLOUD_ENABLED=true</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- YouTube Music -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#youtubeConfig">
                                <i class="fab fa-youtube text-danger me-2"></i>
                                Configurar YouTube Music
                            </button>
                        </h2>
                        <div id="youtubeConfig" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol>
                                    <li>Ve a <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a></li>
                                    <li>Habilita YouTube Data API v3</li>
                                    <li>Crea una API Key</li>
                                    <li>Agrega al archivo <code>.env</code>:</li>
                                </ol>
                                <div class="code-block">
                                    <pre><code>YOUTUBE_MUSIC_API_KEY=tu_api_key_aqui
YOUTUBE_MUSIC_ENABLED=true</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.api-platform-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    height: 100%;
    transition: box-shadow 0.2s ease;
}

.api-platform-card:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
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

.platform-name {
    margin: 0;
    font-weight: 600;
}

.platform-status {
    margin-top: 0.25rem;
}

.platform-details {
    font-size: 0.9rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
}

.test-result-item {
    margin-bottom: 1rem;
}

.code-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 1rem;
    margin-top: 1rem;
}

.code-block pre {
    margin: 0;
    color: #495057;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card-header h5, .card-header small {
    color: white;
}

@media (max-width: 768px) {
    .platform-header {
        flex-direction: column;
        text-align: center;
    }
    
    .platform-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('refreshTests');
    
    refreshBtn.addEventListener('click', function() {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        
        // Hacer petición AJAX para refrescar pruebas
        fetch('<?= ($base_url ?? '/tracktraster/') ?>admin/refresh_tests', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Actualizar la vista con los nuevos datos
                updateTestResults(data.test_results);
                updateApiStatus(data.api_status);
                
                // Actualizar timestamp
                const timestampElement = document.querySelector('.text-muted');
                if (timestampElement) {
                    timestampElement.innerHTML = 
                        'Configuración basada en variables de entorno (.env) - Última verificación: ' + data.last_check;
                }
                
                // Mostrar mensaje de éxito
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Pruebas actualizadas correctamente
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.querySelector('.content-header').insertAdjacentHTML('afterend', alertHtml);
                
                // Auto-remove alert after 3 seconds
                setTimeout(() => {
                    const alert = document.querySelector('.alert-success');
                    if (alert) alert.remove();
                }, 3000);
            } else {
                throw new Error(data.message || 'Error desconocido en la respuesta');
            }
        })        .catch(error => {
            console.error('Error:', error);
            
            // Mostrar error detallado
            const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> Error al actualizar las pruebas: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.querySelector('.content-header').insertAdjacentHTML('afterend', alertHtml);
        })
        .finally(() => {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refrescar Pruebas';
        });
    });
      function updateTestResults(results) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;
        
        let html = '';
        
        if (results.error) {
            html = `<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                ${results.error}
            </div>`;
        } else {
            for (const [platform, result] of Object.entries(results)) {
                if (platform === 'error') continue;
                
                let statusBadge = '';
                let statusText = '';
                
                if (result.status === 'success') {
                    statusBadge = '<span class="badge bg-success">✓ OK</span>';
                    statusText = `<small class="text-muted">${result.count} resultados</small>`;
                } else if (result.status === 'error') {
                    statusBadge = '<span class="badge bg-danger">✗ Error</span>';
                    statusText = `<small class="text-danger">${result.error || 'Error desconocido'}</small>`;
                } else {
                    statusBadge = `<span class="badge bg-warning">⚠ ${result.status}</span>`;
                    statusText = '';
                }
                
                html += `
                    <div class="test-result-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="test-platform">
                                <strong>${result.name}</strong>
                                <br>
                                <small class="text-muted">Búsqueda: "${result.test_query || 'N/A'}"</small>
                            </div>
                            <div class="test-status">
                                ${statusBadge}
                                <br>
                                ${statusText}
                            </div>
                        </div>
                        ${result.response_time ? `
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                Tiempo: ${result.response_time}ms
                            </small>
                        </div>
                        ` : ''}
                    </div>
                    <hr>
                `;
            }
        }
        
        testContainer.innerHTML = html;
    }
    
    function updateApiStatus(status) {
        const statusGrid = document.getElementById('apiStatusGrid');
        if (!statusGrid) return;
        
        let html = '';
        
        for (const [platform, statusInfo] of Object.entries(status)) {
            let badgeClass = 'secondary';
            let badgeText = 'Deshabilitada';
            
            if (statusInfo.enabled && statusInfo.configured) {
                badgeClass = 'success';
                badgeText = 'Activa';
            } else if (statusInfo.enabled && !statusInfo.configured) {
                badgeClass = 'warning';
                badgeText = 'Sin Configurar';
            }
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="api-platform-card">
                        <div class="platform-header">
                            <div class="platform-icon">
                                <i class="${statusInfo.icon} text-${statusInfo.color}"></i>
                            </div>
                            <div class="platform-info">
                                <h6 class="platform-name">${statusInfo.name}</h6>
                                <div class="platform-status">
                                    <span class="badge bg-${badgeClass}">${badgeText}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="platform-details">
                            <div class="detail-row">
                                <span class="detail-label">Estado:</span>
                                <span class="detail-value">
                                    ${statusInfo.enabled ? 'Habilitada' : 'Deshabilitada'}
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Configuración:</span>
                                <span class="detail-value">
                                    ${statusInfo.configured ? 'Completa' : 'Pendiente'}
                                </span>
                            </div>
                            ${(statusInfo.enabled && !statusInfo.configured) ? `
                            <div class="detail-row">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Requiere credenciales en .env
                                </small>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
        
        statusGrid.innerHTML = html;
    }
    
    // Auto-refresh cada 5 minutos
    setInterval(() => {
        refreshBtn.click();
    }, 300000);
});
</script>

<?php
$content = ob_get_clean();
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/dashboard.php';
?>
