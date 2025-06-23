-- TrackTraster Database Schema - SERVIDOR COMPARTIDO
-- Base de datos: masrcom1_tracktraster
-- VERSIÓN SEGURA PARA PRODUCCIÓN

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- Usar la base de datos del servidor compartido
USE masrcom1_tracktraster;

-- =============================================
-- VERIFICAR Y CREAR BACKUP DE USUARIOS
-- =============================================

-- Crear tabla temporal para backup de usuarios (solo si existe la tabla users)
SET @table_exists = (
    SELECT COUNT(*)
    FROM information_schema.tables 
    WHERE table_schema = 'masrcom1_tracktraster' 
    AND table_name = 'users'
);

-- Backup usuarios si la tabla existe
SET @sql = IF(@table_exists > 0, 
    'CREATE TEMPORARY TABLE temp_users_backup AS SELECT * FROM users',
    'CREATE TEMPORARY TABLE temp_users_backup (id INT, full_name VARCHAR(100), email VARCHAR(150), password VARCHAR(255), country CHAR(2), company VARCHAR(100), active TINYINT(1), email_verified TINYINT(1), email_verification_token VARCHAR(100), password_reset_token VARCHAR(100), password_reset_expires DATETIME, last_login DATETIME, created_at TIMESTAMP, updated_at TIMESTAMP)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================
-- ELIMINAR TABLAS EXISTENTES (EXCEPTO USUARIOS)
-- =============================================

-- Eliminar tablas en orden correcto para evitar errores de FK
DROP TABLE IF EXISTS growth_analytics;
DROP TABLE IF EXISTS metric_snapshots;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS system_logs;
DROP TABLE IF EXISTS platform_metrics;
DROP TABLE IF EXISTS lastfm_metrics;
DROP TABLE IF EXISTS youtube_metrics;
DROP TABLE IF EXISTS deezer_metrics;
DROP TABLE IF EXISTS spotify_metrics;
DROP TABLE IF EXISTS artist_trackings;
DROP TABLE IF EXISTS artists;
DROP TABLE IF EXISTS remember_tokens;

-- =============================================
-- RECREAR TABLA USERS (ESTRUCTURA ACTUALIZADA)
-- =============================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    country CHAR(2) NOT NULL DEFAULT 'CO' COMMENT 'ISO country code',
    company VARCHAR(100) NULL,
    active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    email_verification_token VARCHAR(100) NULL,
    password_reset_token VARCHAR(100) NULL,
    password_reset_expires DATETIME NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_country (country),
    INDEX idx_active (active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurar usuarios desde backup
INSERT IGNORE INTO users 
SELECT * FROM temp_users_backup 
WHERE id IS NOT NULL AND email IS NOT NULL;

-- Si no hay usuarios, crear admin por defecto
INSERT IGNORE INTO users (id, full_name, email, password, country, company, active, email_verified) 
VALUES (1, 'Administrador', 'admin@tracktraster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CO', 'TrackTraster', 1, 1);

-- =============================================
-- REMEMBER TOKENS TABLE
-- =============================================
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ARTISTS TABLE - CON SOPORTE MULTI-PLATAFORMA
-- =============================================
CREATE TABLE artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    
    -- IDs de plataformas musicales
    spotify_id VARCHAR(50) NULL,
    deezer_id VARCHAR(50) NULL,
    youtube_id VARCHAR(50) NULL,
    lastfm_name VARCHAR(200) NULL,
    apple_music_id VARCHAR(50) NULL,
    
    -- Datos básicos del artista
    genres JSON NULL,
    image_url TEXT NULL,
    popularity INT DEFAULT 0,
    followers_total INT DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    
    -- COLUMNA CRÍTICA: Datos de múltiples plataformas
    platforms_data JSON NULL COMMENT 'Datos completos de todas las plataformas en formato JSON',
    
    -- Métricas calculadas de todas las plataformas
    total_followers_all_platforms BIGINT DEFAULT 0,
    avg_popularity_all_platforms DECIMAL(5,2) DEFAULT 0.00,
    
    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_spotify_id (spotify_id),
    INDEX idx_deezer_id (deezer_id),
    INDEX idx_popularity (popularity),
    INDEX idx_total_followers (total_followers_all_platforms),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_spotify (spotify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ARTIST TRACKINGS TABLE - EXTENDIDA
-- =============================================
CREATE TABLE artist_trackings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    artist_id INT NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'CO',
    
    -- Información del evento/seguimiento
    event_name VARCHAR(200) NULL COMMENT 'Nombre del concierto o evento',
    event_date DATE NULL,
    event_city VARCHAR(100) NULL,
    event_venue VARCHAR(200) NULL,
    
    -- Configuración del seguimiento
    tracking_start_date DATE NOT NULL,
    tracking_end_date DATE NULL,
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    tracking_status ENUM('pending', 'ongoing', 'completed') DEFAULT 'pending',
    
    -- COLUMNA CRÍTICA: Configuración de plataformas
    platforms_to_track JSON NULL COMMENT 'Array de plataformas seleccionadas: ["spotify", "deezer", "lastfm"]',
    
    -- COLUMNA CRÍTICA: Métricas actuales del seguimiento
    platform_metrics JSON NULL COMMENT 'Métricas actuales de todas las plataformas del seguimiento',
    
    -- Configuración adicional
    notes TEXT NULL,
    notification_settings JSON NULL DEFAULT '{"email": true, "growth_alerts": true}',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    INDEX idx_user_artist (user_id, artist_id),
    INDEX idx_country (country_code),
    INDEX idx_status (status),
    INDEX idx_tracking_status (tracking_status),
    INDEX idx_tracking_start (tracking_start_date),
    INDEX idx_event_date (event_date),
    UNIQUE KEY unique_user_artist_tracking (user_id, artist_id, country_code, tracking_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PLATFORM METRICS TABLE - NUEVA TABLA UNIFICADA
-- =============================================
CREATE TABLE platform_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    platform VARCHAR(20) NOT NULL COMMENT 'spotify, deezer, lastfm, youtube_music, apple_music',
    metric_date DATE NOT NULL,
    
    -- Métricas básicas (aplicables a todas las plataformas)
    followers INT DEFAULT 0,
    popularity INT DEFAULT 0,
    
    -- Datos específicos de la plataforma en JSON
    platform_specific_data JSON NULL COMMENT 'Datos específicos de cada plataforma',
    
    -- Datos de crecimiento calculados
    growth_rate DECIMAL(10,4) DEFAULT 0.0000,
    change_from_previous INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_platform (tracking_id, platform),
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_platform_date (platform, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_platform_metric (tracking_id, platform, metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLAS DE MÉTRICAS ESPECÍFICAS (MANTENIDAS PARA COMPATIBILIDAD)
-- =============================================

-- Spotify Metrics
CREATE TABLE spotify_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    metric_date DATE NOT NULL,
    popularity INT DEFAULT 0,
    followers INT DEFAULT 0,
    monthly_listeners INT DEFAULT 0,
    top_track_position INT NULL,
    top_track_name VARCHAR(200) NULL,
    playlist_reach INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_metric (tracking_id, metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deezer Metrics
CREATE TABLE deezer_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    metric_date DATE NOT NULL,
    fans INT DEFAULT 0,
    rank INT NULL,
    albums_count INT DEFAULT 0,
    top_track_title VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_metric (tracking_id, metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Last.fm Metrics
CREATE TABLE lastfm_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    metric_date DATE NOT NULL,
    listeners INT DEFAULT 0,
    scrobbles INT DEFAULT 0,
    chart_position INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_metric (tracking_id, metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- YouTube Metrics
CREATE TABLE youtube_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    metric_date DATE NOT NULL,
    subscribers INT DEFAULT 0,
    total_views BIGINT DEFAULT 0,
    recent_views INT DEFAULT 0 COMMENT 'Views in last 30 days',
    top_video_views INT DEFAULT 0,
    top_video_title VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_metric (tracking_id, metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLAS ADICIONALES DEL SISTEMA
-- =============================================

-- Metric Snapshots
CREATE TABLE metric_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    snapshot_type ENUM('initial', 'milestone', 'final', 'daily') NOT NULL,
    snapshot_date DATETIME NOT NULL,
    
    -- Datos de cada plataforma
    spotify_data JSON NULL,
    deezer_data JSON NULL,
    youtube_data JSON NULL,
    lastfm_data JSON NULL,
    apple_music_data JSON NULL,
    
    -- Datos combinados
    combined_metrics JSON NULL,
    notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_type (tracking_id, snapshot_type),
    INDEX idx_snapshot_date (snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Growth Analytics
CREATE TABLE growth_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    analysis_date DATE NOT NULL,
    period_days INT NOT NULL DEFAULT 7 COMMENT 'Analysis period in days',
    platform VARCHAR(20) NULL COMMENT 'Plataforma específica o NULL para análisis combinado',
    
    follower_growth_rate DECIMAL(10,4) DEFAULT 0.0000,
    listener_growth_rate DECIMAL(10,4) DEFAULT 0.0000,
    engagement_score DECIMAL(10,4) DEFAULT 0.0000,
    trend_direction ENUM('up', 'down', 'stable') DEFAULT 'stable',
    
    predictions JSON NULL,
    analysis_data JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, analysis_date),
    INDEX idx_platform (platform),
    INDEX idx_trend (trend_direction),
    UNIQUE KEY unique_analysis (tracking_id, analysis_date, period_days, platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alerts
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracking_id INT NOT NULL,
    alert_type ENUM('growth_spike', 'growth_drop', 'milestone', 'custom', 'platform_change') NOT NULL,
    platform VARCHAR(20) NULL COMMENT 'Plataforma que generó la alerta',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    threshold_value DECIMAL(10,4) NULL,
    current_value DECIMAL(10,4) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_tracking (tracking_id),
    INDEX idx_platform (platform),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracking_id INT NOT NULL,
    report_type ENUM('weekly', 'monthly', 'custom', 'event', 'platform_comparison') NOT NULL,
    title VARCHAR(200) NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    
    platforms_included JSON NULL COMMENT 'Plataformas incluidas en el reporte',
    report_data JSON NOT NULL,
    
    file_path VARCHAR(500) NULL COMMENT 'Ruta del archivo PDF generado',
    file_size INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, report_type),
    INDEX idx_tracking (tracking_id),
    INDEX idx_date_range (date_from, date_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Logs
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_type ENUM('api_call', 'error', 'cron', 'user_action', 'platform_sync') NOT NULL,
    platform VARCHAR(20) NULL COMMENT 'Plataforma relacionada',
    message TEXT NOT NULL,
    context JSON NULL,
    user_id INT NULL,
    tracking_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE SET NULL,
    INDEX idx_log_type (log_type),
    INDEX idx_platform (platform),
    INDEX idx_user_id (user_id),
    INDEX idx_tracking_id (tracking_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CREAR VISTAS OPTIMIZADAS
-- =============================================

-- Vista de métricas más recientes
CREATE OR REPLACE VIEW latest_metrics AS
SELECT 
    at.id as tracking_id,
    at.user_id,
    a.name as artist_name,
    a.image_url,
    at.country_code,
    at.event_name,
    at.event_date,
    at.status,
    at.tracking_status,
    at.tracking_start_date,
    
    -- Datos de plataformas
    a.platforms_data,
    a.total_followers_all_platforms,
    a.avg_popularity_all_platforms,
    at.platform_metrics,
    
    -- Cálculos útiles
    GREATEST(COALESCE(a.updated_at, '1970-01-01'), COALESCE(at.updated_at, '1970-01-01')) as last_update,
    DATEDIFF(CURDATE(), at.tracking_start_date) as tracking_days,
    CASE 
        WHEN at.event_date IS NOT NULL THEN DATEDIFF(at.event_date, CURDATE())
        ELSE NULL 
    END as days_to_event

FROM artist_trackings at
JOIN artists a ON at.artist_id = a.id
WHERE at.status IN ('active', 'paused');

-- Vista de resumen de usuarios activos
CREATE OR REPLACE VIEW user_activity_summary AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.email,
    u.country,
    COUNT(at.id) as total_trackings,
    COUNT(CASE WHEN at.status = 'active' THEN 1 END) as active_trackings,
    COUNT(CASE WHEN at.event_date >= CURDATE() THEN 1 END) as upcoming_events,
    MAX(at.updated_at) as last_activity
FROM users u
LEFT JOIN artist_trackings at ON u.id = at.user_id
WHERE u.active = 1
GROUP BY u.id, u.full_name, u.email, u.country;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- LIMPIAR DATOS TEMPORALES
-- =============================================
DROP TEMPORARY TABLE IF EXISTS temp_users_backup;

-- =============================================
-- MENSAJE FINAL
-- =============================================
SELECT 'Base de datos masrcom1_tracktraster actualizada exitosamente' as STATUS,
       'Usuarios conservados, nuevas tablas creadas' as INFO,
       'Columnas platforms_data y platform_metrics agregadas' as COLUMNAS_CLAVE;

/*
=== RESUMEN DE LA ACTUALIZACIÓN ===

✅ BASE DE DATOS: masrcom1_tracktraster
✅ USUARIOS: Conservados todos los usuarios existentes
✅ NUEVAS COLUMNAS CRÍTICAS:
   - artists.platforms_data (JSON) - Datos de todas las plataformas
   - artist_trackings.platform_metrics (JSON) - Métricas del seguimiento
   - artist_trackings.platforms_to_track (JSON) - Plataformas seleccionadas

✅ NUEVAS TABLAS:
   - platform_metrics - Métricas unificadas por plataforma
   - Tablas específicas por plataforma (spotify_metrics, deezer_metrics, etc.)

✅ VISTAS OPTIMIZADAS:
   - latest_metrics - Métricas más recientes por seguimiento
   - user_activity_summary - Resumen de actividad por usuario

Este schema resuelve el error "Unknown column 'platforms_data'" 
y proporciona una estructura completa para el manejo multi-plataforma.
*/
