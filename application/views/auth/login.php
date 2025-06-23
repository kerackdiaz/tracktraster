<?php ob_start(); ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-chart-line"></i>
                <h1>TrackTraster</h1>
            </div>
            <p class="auth-subtitle">Analítica Musical Inteligente</p>
        </div>

        <div class="auth-form-container">
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
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
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            id="remember_me" 
                            name="remember_me" 
                            class="form-check-input" 
                            value="1"
                        >
                        <label for="remember_me" class="form-check-label">
                            Recordar credenciales por 30 días
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <div class="auth-links">
                <p>¿No tienes cuenta? <a href="<?= $base_url ?? '/tracktraster/' ?>auth/register">Regístrate aquí</a></p>
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
