# Logs Directory

Este directorio contiene los logs del sistema TrackTraster.

## Archivos de log:

- `cron.log` - Logs de tareas programadas (cron jobs)
- `cron_errors.log` - Errores específicos de cron jobs
- `analytics.log` - Logs del servicio de analytics
- `lifecycle.log` - Logs del servicio de lifecycle de trackings

## Configuración recomendada:

Configurar rotación de logs para evitar que crezcan demasiado:

```bash
# Agregar a logrotate.d
/path/to/tracktraster/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

## Monitoreo:

Para monitorear errores en tiempo real:
```bash
tail -f logs/cron_errors.log
```
