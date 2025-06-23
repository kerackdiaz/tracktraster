# 🗄️ ACTUALIZACIÓN DE BASE DE DATOS - TrackTraster

## 🎯 Problema Resuelto
**Error:** `Unknown column 'platforms_data' in 'INSERT INTO'`

**Causa:** La base de datos existente no tenía las columnas necesarias para manejar datos de múltiples plataformas musicales.

## 📋 Archivos Disponibles

### 1. `schema_servidor.sql` ⭐ **RECOMENDADO**
- ✅ **Configurado para:** `masrcom1_tracktraster`
- ✅ **Conserva usuarios existentes**
- ✅ **Manejo de errores optimizado**
- ✅ **Seguro para servidores compartidos**

### 2. `schema.sql` 
- ✅ **Versión general actualizada**
- ⚠️ **Requiere cambio manual del nombre de DB**

### 3. `verificar_db.sql`
- 🔍 **Script de verificación post-instalación**
- ✅ **Confirma que todo funcione correctamente**

## 🚀 Instrucciones de Instalación

### Paso 1: Ejecutar Schema Principal
```sql
-- En phpMyAdmin o MySQL CLI del servidor:
-- Ejecutar: database/schema_servidor.sql
```

### Paso 2: Verificar Instalación
```sql
-- Ejecutar: database/verificar_db.sql
-- Debe mostrar ✅ para todas las columnas críticas
```

### Paso 3: Confirmar Funcionamiento
- Ir a: `https://tracktraster.3mas1r.com/artists/search`
- Buscar un artista
- Intentar crear un nuevo seguimiento
- **NO debe aparecer el error de `platforms_data`**

## 🗂️ Nuevas Columnas Críticas

### Tabla `artists`
```sql
platforms_data JSON NULL              -- Datos de todas las plataformas
total_followers_all_platforms BIGINT  -- Total combinado de seguidores  
avg_popularity_all_platforms DECIMAL  -- Popularidad promedio
```

### Tabla `artist_trackings`
```sql
platform_metrics JSON NULL           -- Métricas actuales del seguimiento
platforms_to_track JSON NULL         -- Plataformas seleccionadas
tracking_status ENUM(...)            -- Estado del seguimiento
```

### Nueva Tabla `platform_metrics`
```sql
-- Tabla unificada para métricas de todas las plataformas
tracking_id, platform, metric_date, followers, popularity...
```

## 🎯 Características Agregadas

### ✅ **Soporte Multi-Plataforma**
- Spotify, Deezer, Last.fm, YouTube Music, Apple Music
- Datos JSON flexibles para cada plataforma
- Métricas combinadas automáticas

### ✅ **Conservación de Datos**
- **Todos los usuarios existentes se mantienen**
- Backup automático durante actualización
- Sin pérdida de información

### ✅ **Optimización de Rendimiento**
- Índices optimizados para consultas comunes
- Vistas precalculadas para dashboards
- Estructura escalable

### ✅ **Nuevas Funcionalidades**
- Seguimiento por evento con fechas
- Alertas por plataforma específica
- Reportes comparativos entre plataformas
- Analytics de crecimiento por plataforma

## 🔍 Verificación de Éxito

Después de ejecutar el schema, deberías ver:

```sql
-- En verificar_db.sql:
✅ artists.platforms_data - EXISTE
✅ artist_trackings.platform_metrics - EXISTE  
✅ platform_metrics (tabla) - EXISTE
```

## 🚨 Respaldo de Seguridad

El script automáticamente:
1. 🔄 Hace backup de usuarios existentes
2. 🗑️ Elimina tablas obsoletas
3. 🆕 Crea estructura actualizada
4. 📥 Restaura usuarios desde backup
5. ✅ Confirma integridad de datos

## 📞 Soporte

Si encuentras algún error:
1. Revisar logs de MySQL en cPanel
2. Verificar permisos de usuario de DB
3. Confirmar que la DB se llama `masrcom1_tracktraster`
4. Ejecutar `verificar_db.sql` para diagnóstico

---

**🎉 Resultado:** Base de datos completamente actualizada y compatible con todas las funcionalidades multi-plataforma de TrackTraster.

**⏱️ Tiempo estimado:** 2-5 minutos de ejecución
