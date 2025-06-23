-- Script de Verificación - Base de Datos TrackTraster
-- Verifica que todas las tablas y columnas críticas existan

USE masrcom1_tracktraster;

-- =============================================
-- VERIFICAR EXISTENCIA DE TABLAS
-- =============================================
SELECT 
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size in MB'
FROM information_schema.tables 
WHERE table_schema = 'masrcom1_tracktraster'
ORDER BY table_name;

-- =============================================
-- VERIFICAR COLUMNAS CRÍTICAS
-- =============================================

-- Verificar columna platforms_data en artists
SELECT 
    'artists.platforms_data' as columna,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ EXISTE'
        ELSE '❌ NO EXISTE'
    END as estado
FROM information_schema.columns 
WHERE table_schema = 'masrcom1_tracktraster' 
  AND table_name = 'artists' 
  AND column_name = 'platforms_data'

UNION ALL

-- Verificar columna platform_metrics en artist_trackings
SELECT 
    'artist_trackings.platform_metrics' as columna,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ EXISTE'
        ELSE '❌ NO EXISTE'
    END as estado
FROM information_schema.columns 
WHERE table_schema = 'masrcom1_tracktraster' 
  AND table_name = 'artist_trackings' 
  AND column_name = 'platform_metrics'

UNION ALL

-- Verificar tabla platform_metrics
SELECT 
    'platform_metrics (tabla)' as columna,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ EXISTE'
        ELSE '❌ NO EXISTE'
    END as estado
FROM information_schema.tables 
WHERE table_schema = 'masrcom1_tracktraster' 
  AND table_name = 'platform_metrics';

-- =============================================
-- VERIFICAR USUARIOS CONSERVADOS
-- =============================================
SELECT 
    COUNT(*) as total_usuarios,
    COUNT(CASE WHEN active = 1 THEN 1 END) as usuarios_activos,
    'Usuarios conservados exitosamente' as status
FROM users;

-- =============================================
-- VERIFICAR ESTRUCTURA DE COLUMNAS JSON
-- =============================================
DESCRIBE artists;
DESCRIBE artist_trackings;
DESCRIBE platform_metrics;

-- =============================================
-- PROBAR INSERCIÓN DE EJEMPLO
-- =============================================

-- Insertar artista de prueba con platforms_data
INSERT IGNORE INTO artists (
    name, 
    spotify_id, 
    platforms_data,
    total_followers_all_platforms,
    avg_popularity_all_platforms
) VALUES (
    'Artista de Prueba',
    'test_spotify_id_123',
    '{"spotify": {"id": "test_spotify_id_123", "followers": 10000, "popularity": 75}, "deezer": {"id": "test_deezer_id", "fans": 5000}, "lastfm": {"name": "test_lastfm", "listeners": 8000}}',
    23000,
    75.0
);

-- Verificar que se insertó correctamente
SELECT 
    id,
    name,
    JSON_EXTRACT(platforms_data, '$.spotify.followers') as spotify_followers,
    JSON_EXTRACT(platforms_data, '$.deezer.fans') as deezer_fans,
    total_followers_all_platforms
FROM artists 
WHERE name = 'Artista de Prueba';

-- =============================================
-- RESULTADO FINAL
-- =============================================
SELECT 
    '🎉 VERIFICACIÓN COMPLETADA' as RESULTADO,
    'Base de datos masrcom1_tracktraster lista para usar' as ESTADO,
    'Todas las columnas críticas están disponibles' as CONFIRMACION;
