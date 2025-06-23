<?php ob_start(); ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-chart-line"></i>
                <h1>TrackTraster</h1>
            </div>
            <p class="auth-subtitle">Crear Nueva Cuenta</p>
        </div>

        <div class="auth-form-container">
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user"></i> Nombre Completo
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-control" 
                        required 
                        minlength="2"
                        value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        required 
                        autocomplete="email"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    >
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <div class="password-input-container">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control" 
                                    required 
                                    minlength="6"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Confirmar Contraseña
                            </label>
                            <div class="password-input-container">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-control" 
                                    required 
                                    minlength="6"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country" class="form-label">
                                <i class="fas fa-flag"></i> País
                            </label>
                            <select id="country" name="country" class="form-select" required>
                                <option value="">Selecciona tu país</option>
                                <?php foreach ($countries as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= (isset($_POST['country']) && $_POST['country'] === $code) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company" class="form-label">
                                <i class="fas fa-building"></i> Empresa (Opcional)
                            </label>
                            <input 
                                type="text" 
                                id="company" 
                                name="company" 
                                class="form-control"
                                value="<?= isset($_POST['company']) ? htmlspecialchars($_POST['company']) : '' ?>"
                            >
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
            </form>

            <div class="auth-links">
                <p>¿Ya tienes cuenta? <a href="<?= $base_url ?? '/tracktraster/' ?>auth/login">Inicia sesión aquí</a></p>
            </div>
        </div>

        <div class="auth-footer">
            <p>&copy; 2025 TrackTraster. Todos los derechos reservados.</p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$base_url = '/tracktraster/';
include APPPATH . 'views/layouts/auth.php';
?>

<script>
// Password confirmation validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});
</script>
