# 🔍 Guía de Diagnósticos TrackTraster

## Acceso desde la Versión Online

### 1. Diagnóstico Completo del Sistema
**URL:** `https://tracktraster.3mas1r.com/analytics/systemDiagnostic?debug_key=diagnostic_2025`

**¿Qué hace?**
- Muestra el estado de todos los trackings activos
- Verifica la conectividad de la base de datos
- Lista métricas disponibles por tracking
- Identifica trackings sin datos
- Muestra logs recientes del sistema

**¿Cuándo usarlo?**
- Cuando un tracking nuevo no muestre analytics
- Para verificar el estado general del sistema
- Para diagnosticar problemas de conectividad
- Como check-up rutinario del sistema

### 2. Poblar Métricas Faltantes
**URL:** `https://tracktraster.3mas1r.com/analytics/populateMetrics?populate_key=populate_metrics_2025`

**¿Qué hace?**
- Busca trackings sin métricas
- Crea métricas iniciales para cada tracking
- Genera datos de muestra realistas para nuevos trackings
- Inicializa el sistema de métricas

**¿Cuándo usarlo?**
- Después de crear un nuevo tracking
- Cuando analytics muestre "sin datos"
- Para inicializar trackings importados
- Para reparar métricas corruptas

### 3. Actualizar Métricas Diarias
**URL:** `https://tracktraster.3mas1r.com/analytics/updateDailyMetrics?key=tracktraster_cron_2025`

**¿Qué hace?**
- Ejecuta la actualización manual de métricas
- Simula el trabajo del cron job
- Actualiza estados de tracking según fechas
- Procesa métricas pendientes

**¿Cuándo usarlo?**
- Si el cron automático no está funcionando
- Para forzar una actualización inmediata
- Para probar el sistema de métricas
- En caso de problemas con el servidor de cron

## Solución de Problemas Comunes

### Problema: "Nuevo tracking no muestra datos en analytics"
1. Ve a: `/analytics/systemDiagnostic?debug_key=diagnostic_2025`
2. Busca tu tracking en la tabla
3. Si dice "Sin métricas", ve a: `/analytics/populateMetrics?populate_key=populate_metrics_2025`
4. Regresa a analytics - deberías ver datos ahora

### Problema: "Analytics muestra siempre los mismos datos"
1. Ejecuta: `/analytics/updateDailyMetrics?key=tracktraster_cron_2025`
2. Verifica que el cron automático esté funcionando
3. Revisa los logs en el diagnóstico del sistema

### Problema: "Error de base de datos"
1. Ve al diagnóstico del sistema
2. Revisa la sección "Estado de la Base de Datos"
3. Verifica la configuración en `.env`
4. Contacta al administrador del servidor si persiste

## Seguridad

- **Claves de acceso:** Los endpoints usan claves específicas para prevenir acceso no autorizado
- **Solo para admin:** Estos endpoints están diseñados solo para administradores
- **Logs:** Todas las acciones se registran en los logs del sistema
- **IP tracking:** Considera restringir el acceso por IP si es necesario

## Automatización

### Cron Job Recomendado
Agrega esto al crontab del servidor para actualizaciones automáticas:
```bash
# Actualizar métricas cada hora
0 * * * * wget -q -O /dev/null "https://tracktraster.3mas1r.com/analytics/updateDailyMetrics?key=tracktraster_cron_2025"

# Diagnóstico diario (opcional)
0 6 * * * wget -q -O /dev/null "https://tracktraster.3mas1r.com/analytics/systemDiagnostic?debug_key=diagnostic_2025" > /path/to/logs/daily_diagnostic.log
```

## Monitoreo

### Indicadores de Salud del Sistema
- **Verde ✅:** Tracking con métricas activas
- **Amarillo ⚠️:** Tracking sin métricas (necesita poblar)
- **Rojo ❌:** Error de sistema o conectividad

### Métricas a Monitorear
- Número de trackings activos
- Trackings sin métricas
- Errores en logs
- Tiempo de respuesta de endpoints

## Contacto de Soporte

Si necesitas ayuda adicional:
1. Revisa este documento
2. Ejecuta el diagnóstico del sistema
3. Revisa los logs generados
4. Documenta el problema específico y los pasos reproducibles
