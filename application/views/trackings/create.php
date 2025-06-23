<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= ($base_url ?? '/tracktraster/') ?>trackings" class="btn btn-outline-light">
            <i class="fas fa-arrow-left"></i> Volver a Seguimientos
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
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
        <?php endif; ?>

        <!-- Información del artista (si se ha seleccionado) -->
        <?php if ($artist): ?>
        <div class="card artist-info-card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-music text-primary"></i>
                    Configurar seguimiento para:
                </h5>
                <div class="d-flex align-items-center">
                    <?php if ($artist['image_url']): ?>
                    <img src="<?= htmlspecialchars($artist['image_url']) ?>" 
                         alt="<?= htmlspecialchars($artist['name']) ?>"
                         class="rounded-circle me-3" 
                         style="width: 60px; height: 60px; object-fit: cover;">
                    <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3"
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-music text-white fa-lg"></i>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($artist['name']) ?></h4>
                        <div class="artist-details">
                            <?php if ($artist['popularity']): ?>
                            <span class="badge bg-primary me-2">Popularidad: <?= $artist['popularity'] ?>%</span>
                            <?php endif; ?>
                            <?php if ($artist['followers_total']): ?>
                            <span class="badge bg-info"><?= number_format($artist['followers_total']) ?> seguidores</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario de seguimiento -->
        <div class="card tracking-form-card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-chart-line text-success"></i>
                    Configuración del Seguimiento
                </h5>
                
                <form method="POST" id="trackingForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <?php if ($artist): ?>
                    <input type="hidden" name="artist_id" value="<?= $artist['id'] ?>">
                    <?php else: ?>
                    
                    <!-- Selector de artista (si no se ha preseleccionado) -->
                    <div class="mb-4">
                        <label for="artist_id" class="form-label required">Artista</label>
                        <div class="artist-selector">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Primero debe <a href="<?= ($base_url ?? '/tracktraster/') ?>artists/search">buscar y agregar un artista</a> antes de crear un seguimiento.
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- País de seguimiento -->
                    <div class="mb-4">
                        <label for="country_code" class="form-label required">País de seguimiento</label>
                        <select class="form-select" name="country_code" id="country_code" required>
                            <option value="">Selecciona un país...</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            País en el que quieres analizar el crecimiento del artista
                        </div>
                        <div class="invalid-feedback">
                            Por favor selecciona un país
                        </div>
                    </div>

                    <!-- Evento específico (opcional) -->
                    <div class="mb-4">
                        <label for="event_name" class="form-label">Evento específico <span class="text-muted">(opcional)</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="event_name" 
                               id="event_name"
                               placeholder="Ej: Concierto en Argentina, Lanzamiento de álbum..."
                               maxlength="200">
                        <div class="form-text">
                            Si estás haciendo seguimiento para un evento específico, descríbelo aquí
                        </div>
                    </div>

                    <!-- Fecha del evento (opcional) -->
                    <div class="mb-4">
                        <label for="event_date" class="form-label">Fecha del evento <span class="text-muted">(opcional)</span></label>
                        <input type="date" 
                               class="form-control" 
                               name="event_date" 
                               id="event_date"
                               min="<?= date('Y-m-d') ?>">
                        <div class="form-text">
                            Fecha objetivo del evento. Si no se especifica, el seguimiento será continuo
                        </div>
                    </div>

                    <!-- Fecha de inicio del seguimiento -->
                    <div class="mb-4">
                        <label for="tracking_start_date" class="form-label">Fecha de inicio del seguimiento</label>
                        <input type="date" 
                               class="form-control" 
                               name="tracking_start_date" 
                               id="tracking_start_date"
                               value="<?= date('Y-m-d') ?>"
                               max="<?= date('Y-m-d') ?>"
                               required>
                        <div class="form-text">
                            Fecha desde la cual comenzar a analizar los datos (por defecto hoy)
                        </div>
                        <div class="invalid-feedback">
                            La fecha de inicio es requerida
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notas <span class="text-muted">(opcional)</span></label>
                        <textarea class="form-control" 
                                  name="notes" 
                                  id="notes" 
                                  rows="4"
                                  placeholder="Agrega cualquier información adicional sobre este seguimiento..."
                                  maxlength="1000"></textarea>
                        <div class="form-text">
                            <span id="notes-counter">0</span>/1000 caracteres
                        </div>
                    </div>

                    <!-- Configuración avanzada -->
                    <div class="card advanced-config mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-decoration-none p-0" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#advancedOptions">
                                    <i class="fas fa-cog"></i> Configuración Avanzada
                                    <i class="fas fa-chevron-down ms-2"></i>
                                </button>
                            </h6>
                        </div>
                        <div class="collapse" id="advancedOptions">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Frecuencia de recolección</label>
                                            <select class="form-select" name="frequency">
                                                <option value="daily" selected>Diaria</option>
                                                <option value="weekly">Semanal</option>
                                                <option value="monthly">Mensual</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Alertas automáticas</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="enable_alerts" id="enable_alerts" checked>
                                                <label class="form-check-label" for="enable_alerts">
                                                    Enviar notificaciones por cambios significativos
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= ($base_url ?? '/tracktraster/') ?><?= $artist ? 'artists/view/' . $artist['id'] : 'trackings' ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-play"></i> Iniciar Seguimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="card info-card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-info-circle text-info"></i>
                    ¿Qué datos se recolectarán?
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Posición en charts musicales</li>
                            <li><i class="fas fa-check text-success"></i> Número de streams</li>
                            <li><i class="fas fa-check text-success"></i> Crecimiento de seguidores</li>
                            <li><i class="fas fa-check text-success"></i> Popularidad en redes sociales</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Menciones en medios</li>
                            <li><i class="fas fa-check text-success"></i> Engagement en plataformas</li>
                            <li><i class="fas fa-check text-success"></i> Tendencias de búsqueda</li>
                            <li><i class="fas fa-check text-success"></i> Comparativas regionales</li>
                        </ul>
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

.card {
    border: none;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.artist-info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.tracking-form-card {
    background: white;
}

.advanced-config {
    background: #f8f9fa;
}

.advanced-config .card-header {
    background: transparent;
    border-bottom: 1px solid #dee2e6;
}

.info-card {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.alert {
    border-radius: 10px;
}

.badge {
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-lg {
        width: 100%;
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
    const trackingForm = document.getElementById('trackingForm');
    const submitBtn = document.getElementById('submitBtn');
    const notesTextarea = document.getElementById('notes');
    const notesCounter = document.getElementById('notes-counter');
    const eventNameInput = document.getElementById('event_name');
    const eventDateInput = document.getElementById('event_date');

    // Form validation
    trackingForm.addEventListener('submit', function(e) {
        if (!trackingForm.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            // Show loading state
            trackingForm.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando seguimiento...';
            submitBtn.disabled = true;
        }
        
        trackingForm.classList.add('was-validated');
    });

    // Notes character counter
    if (notesTextarea && notesCounter) {
        notesTextarea.addEventListener('input', function() {
            const length = this.value.length;
            notesCounter.textContent = length;
            
            if (length > 900) {
                notesCounter.style.color = '#dc3545';
            } else if (length > 800) {
                notesCounter.style.color = '#ffc107';
            } else {
                notesCounter.style.color = '#6c757d';
            }
        });
    }

    // Auto-enable event date when event name is entered
    if (eventNameInput && eventDateInput) {
        eventNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                eventDateInput.removeAttribute('disabled');
                eventDateInput.parentElement.style.opacity = '1';
            } else {
                eventDateInput.value = '';
                eventDateInput.parentElement.style.opacity = '0.6';
            }
        });
    }

    // Validation feedback
    const inputs = trackingForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
});
</script>

<?php 
$content = ob_get_clean();
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/dashboard.php';
?>
