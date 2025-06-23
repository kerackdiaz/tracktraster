<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard - TrackTraster' ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">    <link href="<?= $base_url ?? '/' ?>assets/css/dashboard.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $base_url ?? '/' ?>assets/img/favicon.ico">
</head>
<body class="dashboard-body">
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-chart-line"></i>
                <span>TrackTraster</span>
            </div>
            <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
          <div class="sidebar-menu">
            <a href="<<?= $base_url ?? '/' ?>dashboard" class="menu-item <?= isset($active_menu) && $active_menu === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="<<?= $base_url ?? '/' ?>artists" class="menu-item <?= isset($active_menu) && $active_menu === 'artists' ? 'active' : '' ?>">
                <i class="fas fa-music"></i>
                <span>Artistas</span>
            </a>
            
            <a href="<<?= $base_url ?? '/' ?>trackings" class="menu-item <?= isset($active_menu) && $active_menu === 'trackings' ? 'active' : '' ?>">
                <i class="fas fa-chart-area"></i>
                <span>Seguimientos</span>
            </a>
            
            <a href="<<?= $base_url ?? '/' ?>analytics" class="menu-item <?= isset($active_menu) && $active_menu === 'analytics' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Analíticas</span>
            </a>
              <a href="<<?= $base_url ?? '/' ?>reports" class="menu-item <?= isset($active_menu) && $active_menu === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Reportes</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="<<?= $base_url ?? '/' ?>admin" class="menu-item <?= isset($active_menu) && $active_menu === 'admin' ? 'active' : '' ?>">
                <i class="fas fa-tools"></i>
                <span>Administración</span>
            </a>
            
            <a href="<<?= $base_url ?? '/' ?>dashboard/profile" class="menu-item <?= isset($active_menu) && $active_menu === 'profile' ? 'active' : '' ?>">
                <i class="fas fa-user-cog"></i>
                <span>Mi Perfil</span>
            </a>
            
            <a href="<<?= $base_url ?? '/' ?>auth/logout" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
            </div>
            
            <div class="topbar-right">
                <div class="user-menu dropdown">
                    <button class="user-menu-toggle" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'Usuario') ?></span>
                            <small class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<<?= $base_url ?? '/' ?>dashboard/profile">
                            <i class="fas fa-user-edit"></i> Mi Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<<?= $base_url ?? '/' ?>auth/logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <?php if (isset($content)) echo $content; ?>
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay d-lg-none" onclick="toggleSidebar()"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= $base_url ?? '/' ?>assets/js/dashboard.js"></script>
    
    <?php if (isset($scripts)) echo $scripts; ?>
    
</body>
</html>
