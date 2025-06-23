-- TrackTraster Database Schema
-- MySQL Database Structure - VERSIÓN COMPLETA Y ACTUALIZADA

SET FOREIGN_KEY_CHECKS = 0;

-- Usar la base de datos existente sin eliminarla
USE masrcom1_tracktraster;

-- =============================================
-- BACKUP Y RECREACIÓN DE TABLAS (CONSERVAR USUARIOS)
-- =============================================

-- Backup de usuarios existentes
CREATE TEMPORARY TABLE temp_users AS SELECT * FROM users WHERE 1;

-- Eliminar todas las tablas excepto users (mantenemos usuarios)
DROP TABLE IF EXISTS growth_analytics;
DROP TABLE IF EXISTS metric_snapshots;
DROP TABLE IF EXISTS lastfm_metrics;
DROP TABLE IF EXISTS youtube_metrics;
DROP TABLE IF EXISTS spotify_metrics;
DROP TABLE IF EXISTS deezer_metrics;
DROP TABLE IF EXISTS platform_metrics;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS system_logs;
DROP TABLE IF EXISTS artist_trackings;
DROP TABLE IF EXISTS artists;
DROP TABLE IF EXISTS remember_tokens;

-- =============================================
-- USERS TABLE (RECREAR PARA ASEGURAR ESTRUCTURA)
-- =============================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    country CHAR(2) NOT NULL COMMENT 'ISO country code',
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
) ENGINE=InnoDB;

-- Restaurar usuarios existentes
INSERT INTO users SELECT * FROM temp_users;
DROP TEMPORARY TABLE temp_users;

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
) ENGINE=InnoDB;

-- =============================================
-- ARTISTS TABLE (EXTENDIDA CON MÚLTIPLES PLATAFORMAS)
-- =============================================
CREATE TABLE artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    
    -- IDs de plataformas
    spotify_id VARCHAR(50) NULL UNIQUE,
    deezer_id VARCHAR(50) NULL,
    youtube_id VARCHAR(50) NULL,
    lastfm_name VARCHAR(200) NULL,
    apple_music_id VARCHAR(50) NULL,
    
    -- Datos básicos
    genres JSON NULL,
    image_url TEXT NULL,
    popularity INT DEFAULT 0,
    followers_total INT DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    
    -- Datos de múltiples plataformas (JSON)
    platforms_data JSON NULL COMMENT 'Datos de todas las plataformas en formato JSON',
    
    -- Métricas combinadas
    total_followers_all_platforms BIGINT DEFAULT 0,
    avg_popularity_all_platforms DECIMAL(5,2) DEFAULT 0,
    
    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_spotify_id (spotify_id),
    INDEX idx_deezer_id (deezer_id),
    INDEX idx_popularity (popularity),
    INDEX idx_total_followers (total_followers_all_platforms),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- =============================================
-- ARTIST TRACKINGS TABLE (EXTENDIDA)
-- =============================================
CREATE TABLE artist_trackings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    artist_id INT NOT NULL,
    country_code CHAR(2) NOT NULL,
    
    -- Información del evento
    event_name VARCHAR(200) NULL COMMENT 'Concert or event name',
    event_date DATE NULL,
    event_city VARCHAR(100) NULL,
    event_venue VARCHAR(200) NULL,
    
    -- Configuración del seguimiento
    tracking_start_date DATE NOT NULL,
    tracking_end_date DATE NULL,
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    tracking_status ENUM('pending', 'ongoing', 'completed') DEFAULT 'pending',
    
    -- Plataformas a seguir
    platforms_to_track JSON NULL COMMENT 'Array de plataformas seleccionadas para seguimiento',
    
    -- Métricas combinadas del seguimiento
    platform_metrics JSON NULL COMMENT 'Métricas actuales de todas las plataformas',
    
    -- Notas y configuración
    notes TEXT NULL,
    notification_settings JSON NULL,
    
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
    UNIQUE KEY unique_tracking (user_id, artist_id, country_code, tracking_start_date)
) ENGINE=InnoDB;

-- =============================================
-- PLATFORM METRICS TABLE (NUEVA - UNIFICADA)
-- =============================================
CREATE TABLE platform_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    platform VARCHAR(20) NOT NULL COMMENT 'spotify, deezer, lastfm, youtube_music, apple_music',
    metric_date DATE NOT NULL,
    
    -- Métricas generales (aplicables a todas las plataformas)
    followers INT DEFAULT 0,
    popularity INT DEFAULT 0,
    
    -- Métricas específicas por plataforma (JSON)
    platform_specific_data JSON NULL,
    
    -- Datos calculados
    growth_rate DECIMAL(10,4) DEFAULT 0,
    change_from_previous INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_platform (tracking_id, platform),
    INDEX idx_tracking_date (tracking_id, metric_date),
    INDEX idx_platform_date (platform, metric_date),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_daily_platform_metric (tracking_id, platform, metric_date)
) ENGINE=InnoDB;

-- =============================================
-- SPOTIFY METRICS TABLE (MANTENIDA PARA COMPATIBILIDAD)
-- =============================================
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
) ENGINE=InnoDB;

-- =============================================
-- DEEZER METRICS TABLE (NUEVA)
-- =============================================
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
) ENGINE=InnoDB;

-- =============================================
-- LASTFM METRICS TABLE
-- =============================================
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
) ENGINE=InnoDB;

-- =============================================
-- YOUTUBE METRICS TABLE
-- =============================================
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
) ENGINE=InnoDB;

-- =============================================
-- METRIC SNAPSHOTS TABLE
-- =============================================
CREATE TABLE metric_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    snapshot_type ENUM('initial', 'milestone', 'final', 'daily') NOT NULL,
    snapshot_date DATETIME NOT NULL,
    
    -- Datos por plataforma
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
) ENGINE=InnoDB;

-- =============================================
-- GROWTH ANALYTICS TABLE
-- =============================================
CREATE TABLE growth_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    analysis_date DATE NOT NULL,
    period_days INT NOT NULL COMMENT 'Analysis period in days',
    
    -- Análisis por plataforma
    platform VARCHAR(20) NULL COMMENT 'Plataforma específica o NULL para análisis combinado',
    
    -- Métricas de crecimiento
    follower_growth_rate DECIMAL(10,4) DEFAULT 0,
    listener_growth_rate DECIMAL(10,4) DEFAULT 0,
    engagement_score DECIMAL(10,4) DEFAULT 0,
    trend_direction ENUM('up', 'down', 'stable') DEFAULT 'stable',
    
    -- Predicciones y análisis avanzado
    predictions JSON NULL,
    analysis_data JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_tracking_date (tracking_id, analysis_date),
    INDEX idx_platform (platform),
    INDEX idx_trend (trend_direction),
    UNIQUE KEY unique_analysis (tracking_id, analysis_date, period_days, platform)
) ENGINE=InnoDB;

-- =============================================
-- ALERTS TABLE
-- =============================================
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
) ENGINE=InnoDB;

-- =============================================
-- REPORTS TABLE
-- =============================================
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracking_id INT NOT NULL,
    report_type ENUM('weekly', 'monthly', 'custom', 'event', 'platform_comparison') NOT NULL,
    title VARCHAR(200) NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    
    -- Configuración del reporte
    platforms_included JSON NULL COMMENT 'Plataformas incluidas en el reporte',
    report_data JSON NOT NULL,
    
    -- Archivo generado
    file_path VARCHAR(500) NULL COMMENT 'PDF file path',
    file_size INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tracking_id) REFERENCES artist_trackings(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, report_type),
    INDEX idx_tracking (tracking_id),
    INDEX idx_date_range (date_from, date_to)
) ENGINE=InnoDB;

-- =============================================
-- SYSTEM LOGS TABLE
-- =============================================
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_type ENUM('api_call', 'error', 'cron', 'user_action', 'platform_sync') NOT NULL,
    platform VARCHAR(20) NULL COMMENT 'Plataforma relacionada con el log',
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
) ENGINE=InnoDB;

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert sample admin user (password: admin123) - Solo si no existe
INSERT IGNORE INTO users (id, full_name, email, password, country, company, active, email_verified) 
VALUES (1, 'Administrador', 'admin@tracktraster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CO', 'TrackTraster', 1, 1);

-- =============================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================

-- Composite indexes for common queries
CREATE INDEX idx_user_country_active ON users(country, active);
CREATE INDEX idx_tracking_status_country ON artist_trackings(status, country_code);
CREATE INDEX idx_metrics_date_range ON spotify_metrics(tracking_id, metric_date, popularity);
CREATE INDEX idx_platform_metrics_compound ON platform_metrics(tracking_id, platform, metric_date);
CREATE INDEX idx_artists_platforms ON artists(total_followers_all_platforms, avg_popularity_all_platforms);

-- =============================================
-- CREATE VIEWS FOR COMMON QUERIES
-- =============================================

-- View for latest metrics per tracking (ACTUALIZADA)
DROP VIEW IF EXISTS latest_metrics;
CREATE VIEW latest_metrics AS
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
    
    -- Datos de plataformas desde artists
    a.platforms_data,
    a.total_followers_all_platforms,
    a.avg_popularity_all_platforms,
    
    -- Métricas desde tracking
    at.platform_metrics,
    
    -- Última actualización
    GREATEST(
        COALESCE(a.updated_at, '1970-01-01'),
        COALESCE(at.updated_at, '1970-01-01')
    ) as last_update,
    
    -- Días de seguimiento
    DATEDIFF(CURDATE(), at.tracking_start_date) as tracking_days,
    
    -- Días hasta evento (si existe)
    CASE 
        WHEN at.event_date IS NOT NULL THEN DATEDIFF(at.event_date, CURDATE())
        ELSE NULL 
    END as days_to_event

FROM artist_trackings at
JOIN artists a ON at.artist_id = a.id
WHERE at.status IN ('active', 'paused');

-- View for growth trends (ACTUALIZADA)
DROP VIEW IF EXISTS growth_trends;
CREATE VIEW growth_trends AS
SELECT 
    tracking_id,
    platform,
    AVG(follower_growth_rate) as avg_follower_growth,
    AVG(listener_growth_rate) as avg_listener_growth,
    AVG(engagement_score) as avg_engagement,
    COUNT(CASE WHEN trend_direction = 'up' THEN 1 END) as positive_trends,
    COUNT(CASE WHEN trend_direction = 'down' THEN 1 END) as negative_trends,
    COUNT(*) as total_analyses,
    MAX(analysis_date) as last_analysis_date
FROM growth_analytics 
WHERE analysis_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY tracking_id, platform;

-- View for platform summary (NUEVA)
CREATE VIEW platform_summary AS
SELECT 
    pm.tracking_id,
    pm.platform,
    COUNT(*) as total_records,
    MAX(pm.metric_date) as last_metric_date,
    AVG(pm.followers) as avg_followers,
    MAX(pm.followers) as max_followers,
    MIN(pm.followers) as min_followers,
    AVG(pm.growth_rate) as avg_growth_rate
FROM platform_metrics pm
WHERE pm.metric_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY pm.tracking_id, pm.platform;

-- View for active trackings summary (NUEVA)
CREATE VIEW active_trackings_summary AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.country,
    COUNT(at.id) as total_trackings,
    COUNT(CASE WHEN at.status = 'active' THEN 1 END) as active_trackings,
    COUNT(CASE WHEN at.status = 'paused' THEN 1 END) as paused_trackings,
    COUNT(CASE WHEN at.status = 'completed' THEN 1 END) as completed_trackings,
    COUNT(CASE WHEN at.event_date >= CURDATE() THEN 1 END) as upcoming_events,
    MIN(at.tracking_start_date) as first_tracking_date,
    MAX(at.updated_at) as last_activity
FROM users u
LEFT JOIN artist_trackings at ON u.id = at.user_id
WHERE u.active = 1
GROUP BY u.id, u.full_name, u.country;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- TRIGGERS PARA MANTENER CONSISTENCIA
-- =============================================

-- Trigger para actualizar métricas combinadas en artists cuando se actualiza platforms_data
DELIMITER $$

CREATE TRIGGER update_artist_combined_metrics 
BEFORE UPDATE ON artists
FOR EACH ROW
BEGIN
    -- Solo actualizar si platforms_data cambió
    IF NEW.platforms_data != OLD.platforms_data OR OLD.platforms_data IS NULL THEN
        -- Calcular total de seguidores (esto sería implementado según la estructura del JSON)
        -- Por ahora, mantener los valores existentes si no se proporcionan nuevos
        IF NEW.total_followers_all_platforms = OLD.total_followers_all_platforms THEN
            SET NEW.total_followers_all_platforms = COALESCE(NEW.total_followers_all_platforms, 0);
        END IF;
        
        IF NEW.avg_popularity_all_platforms = OLD.avg_popularity_all_platforms THEN
            SET NEW.avg_popularity_all_platforms = COALESCE(NEW.avg_popularity_all_platforms, 0);
        END IF;
    END IF;
END$$

DELIMITER ;

-- =============================================
-- COMENTARIOS FINALES
-- =============================================

/*
ESTRUCTURA ACTUALIZADA DE LA BASE DE DATOS:

1. **CONSERVACIÓN DE USUARIOS**: Los usuarios existentes se mantienen
2. **SOPORTE MULTI-PLATAFORMA**: Nuevas columnas para manejar múltiples plataformas
3. **DATOS JSON**: Uso de JSON para flexibilidad en datos de plataformas
4. **MÉTRICAS UNIFICADAS**: Nueva tabla platform_metrics para centralizar métricas
5. **VISTAS OPTIMIZADAS**: Vistas actualizadas para consultas comunes
6. **ÍNDICES MEJORADOS**: Índices optimizados para rendimiento

COLUMNAS CLAVE AGREGADAS:
- artists.platforms_data: JSON con datos de todas las plataformas
- artists.total_followers_all_platforms: Total combinado de seguidores
- artists.avg_popularity_all_platforms: Popularidad promedio
- artist_trackings.platform_metrics: Métricas actuales del seguimiento
- artist_trackings.tracking_status: Estado del seguimiento (pending/ongoing/completed)
- platform_metrics: Nueva tabla unificada para métricas

USO:
1. Ejecutar este script en MySQL
2. La base de datos existente se actualizará manteniendo usuarios
3. Todas las nuevas funcionalidades estarán disponibles
*/

-- Grant permissions (ajustar según el entorno)
-- GRANT ALL PRIVILEGES ON tracktraster_db.* TO 'tracktraster_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- FLUSH PRIVILEGES;
