<?php ob_start(); ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <div class="header-actions">
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key"></i> Cambiar Contraseña
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Información del usuario -->
        <div class="card profile-card">
            <div class="card-body text-center">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h4 class="mt-3"><?= htmlspecialchars($user['name'] ?? '') ?></h4>
                <p class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                <div class="profile-stats">
                    <div class="stat">
                        <h5><?= $stats['total_trackings'] ?? 0 ?></h5>
                        <span>Seguimientos</span>
                    </div>
                    <div class="stat">
                        <h5><?= $stats['active_trackings'] ?? 0 ?></h5>
                        <span>Activos</span>
                    </div>
                    <div class="stat">
                        <h5><?= $stats['days_registered'] ?? 0 ?></h5>
                        <span>Días</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad reciente -->
        <div class="card activity-card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-info"></i>
                    Actividad Reciente
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activity)): ?>
                <p class="text-muted text-center">No hay actividad reciente</p>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?= $activity['icon'] ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p class="mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                            <small class="text-muted"><?= $activity['time_ago'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

        <!-- Formulario de perfil -->
        <div class="card profile-form-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit text-primary"></i>
                    Información Personal
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="profileForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre completo</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="name" 
                                       id="name"
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                       required 
                                       maxlength="100">
                                <div class="invalid-feedback">
                                    El nombre es requerido
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       id="email"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       required 
                                       maxlength="150">
                                <div class="invalid-feedback">
                                    El email es requerido y debe ser válido
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Zona horaria</label>
                                <select class="form-select" name="timezone" id="timezone">
                                    <option value="America/Argentina/Buenos_Aires" <?= ($user['timezone'] ?? '') === 'America/Argentina/Buenos_Aires' ? 'selected' : '' ?>>Buenos Aires (GMT-3)</option>
                                    <option value="America/Mexico_City" <?= ($user['timezone'] ?? '') === 'America/Mexico_City' ? 'selected' : '' ?>>Ciudad de México (GMT-6)</option>
                                    <option value="America/Bogota" <?= ($user['timezone'] ?? '') === 'America/Bogota' ? 'selected' : '' ?>>Bogotá (GMT-5)</option>
                                    <option value="America/Lima" <?= ($user['timezone'] ?? '') === 'America/Lima' ? 'selected' : '' ?>>Lima (GMT-5)</option>
                                    <option value="America/Santiago" <?= ($user['timezone'] ?? '') === 'America/Santiago' ? 'selected' : '' ?>>Santiago (GMT-3)</option>
                                    <option value="America/Sao_Paulo" <?= ($user['timezone'] ?? '') === 'America/Sao_Paulo' ? 'selected' : '' ?>>São Paulo (GMT-3)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="language" class="form-label">Idioma</label>
                                <select class="form-select" name="language" id="language">
                                    <option value="es" <?= ($user['language'] ?? 'es') === 'es' ? 'selected' : '' ?>>Español</option>
                                    <option value="en" <?= ($user['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                                    <option value="pt" <?= ($user['language'] ?? '') === 'pt' ? 'selected' : '' ?>>Português</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Biografía / Descripción</label>
                        <textarea class="form-control" 
                                  name="bio" 
                                  id="bio" 
                                  rows="4"
                                  placeholder="Cuéntanos sobre ti, tu trabajo con artistas o tu interés en la música..."
                                  maxlength="500"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        <div class="form-text">
                            <span id="bio-counter"><?= strlen($user['bio'] ?? '') ?></span>/500 caracteres
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= ($base_url ?? '/tracktraster/') ?>dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary" id="updateBtn">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configuraciones de cuenta -->
        <div class="card account-settings-card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog text-warning"></i>
                    Configuraciones de Cuenta
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <h6>Notificaciones por email</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                       <?= ($user['email_notifications'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="emailNotifications">
                                    Recibir actualizaciones por correo
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-item mt-3">
                            <h6>Recordar sesión</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="rememberSession" 
                                       <?= ($user['remember_session'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rememberSession">
                                    Mantener sesión iniciada
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="setting-item">
                            <h6>Información de la cuenta</h6>
                            <p class="text-muted small">
                                <strong>Registrado:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?><br>
                                <strong>Última conexión:</strong> <?= isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Primera vez' ?><br>
                                <strong>IP actual:</strong> <?= $_SERVER['REMOTE_ADDR'] ?>
                            </p>
                        </div>
                        
                        <div class="setting-item mt-3">
                            <h6>Zona de peligro</h6>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash"></i> Eliminar cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cambiar contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= ($base_url ?? '/tracktraster/') ?>dashboard/change-password" id="passwordForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <input type="password" class="form-control" name="current_password" id="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required minlength="8">
                        <div class="form-text">Mínimo 8 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal eliminar cuenta -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Cuenta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                </div>
                <p>Al eliminar tu cuenta se borrarán permanentemente:</p>
                <ul>
                    <li>Todos tus seguimientos de artistas</li>
                    <li>Datos de analíticas recolectados</li>
                    <li>Reportes generados</li>
                    <li>Configuraciones personalizadas</li>
                </ul>
                <p>Escribe <strong>ELIMINAR</strong> para confirmar:</p>
                <input type="text" class="form-control" id="deleteConfirmation" placeholder="ELIMINAR">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Eliminar Cuenta
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

.profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 3rem;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.profile-stats .stat {
    text-align: center;
}

.profile-stats .stat h5 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.profile-stats .stat span {
    color: #6c757d;
    font-size: 0.9rem;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #667eea;
}

.activity-content {
    flex: 1;
}

.setting-item h6 {
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.card {
    border: none;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.profile-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .content-header {
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .profile-stats {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<!-- JavaScript para esta vista -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const bioTextarea = document.getElementById('bio');
    const bioCounter = document.getElementById('bio-counter');
    const deleteConfirmation = document.getElementById('deleteConfirmation');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Bio character counter
    if (bioTextarea && bioCounter) {
        bioTextarea.addEventListener('input', function() {
            const length = this.value.length;
            bioCounter.textContent = length;
            
            if (length > 450) {
                bioCounter.style.color = '#dc3545';
            } else if (length > 400) {
                bioCounter.style.color = '#ffc107';
            } else {
                bioCounter.style.color = '#6c757d';
            }
        });
    }

    // Delete account confirmation
    if (deleteConfirmation && confirmDeleteBtn) {
        deleteConfirmation.addEventListener('input', function() {
            confirmDeleteBtn.disabled = this.value !== 'ELIMINAR';
        });
    }

    // Form validation
    profileForm.addEventListener('submit', function(e) {
        if (!profileForm.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        profileForm.classList.add('was-validated');
    });

    // Password form validation
    const passwordForm = document.getElementById('passwordForm');
    passwordForm.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return;
        }
        
        if (!passwordForm.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        passwordForm.classList.add('was-validated');
    });
});
</script>

<?php 
$content = ob_get_clean();
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/dashboard.php';
?>
