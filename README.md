# 🎵 TrackTraster

**Plataforma de Análisis y Seguimiento de Artistas Musicales**

TrackTraster es una plataforma web optimizada para el seguimiento y análisis de artistas musicales de múltiples plataformas streaming, con un sistema de administración simplificado y gestión segura de credenciales.

## ✨ Características Principales

- **🎯 Multi-plataforma**: Integración con Spotify, Deezer y Last.fm
- **📊 Seguimiento de artistas**: Sistema completo de tracking y análisis
- **🛡️ Panel de administración**: Monitoreo de APIs y pruebas automáticas  
- **🔒 Configuración segura**: Credenciales gestionadas via variables de entorno
- **📱 Responsive**: Interfaz adaptada para todos los dispositivos
- **⚡ Optimizado**: Solo 0.5MB, 67 archivos esenciales

## 🚀 Tecnologías

- **Backend**: PHP 7.4+ (Framework MVC personalizado)
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Base de datos**: MySQL/MariaDB
- **APIs**: Spotify Web API, Deezer API, Last.fm API
- **Seguridad**: Headers de seguridad, protección de archivos, CSP

## 📋 Requisitos

- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- Apache con mod_rewrite
- Extensiones PHP: PDO, MySQL, cURL, JSON

## ⚙️ Instalación Rápida

1. **Clonar repositorio**:
```bash
git clone https://github.com/kerackdiaz/tracktraster.git
cd tracktraster
```

2. **Configurar variables de entorno**:
```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

3. **Configurar base de datos**:
```sql
CREATE DATABASE tracktraster_db;
-- Importar estructura incluida
```

4. **Configurar permisos**:
```bash
chmod 755 application/
chmod 600 .env
```

## 🎯 APIs Soportadas

### ✅ Activas y Funcionales
- **Spotify** - Búsqueda de artistas, datos de popularidad, imágenes
- **Deezer** - Búsqueda de artistas, álbumes, datos de popularidad  
- **Last.fm** - Biografías, estadísticas, datos de escucha

### ⚙️ Configuración
Editar `.env` con tus credenciales:
```bash
# Spotify (requerido)
SPOTIFY_CLIENT_ID=tu_client_id
SPOTIFY_CLIENT_SECRET=tu_client_secret
SPOTIFY_ENABLED=true

# Last.fm (requerido)  
LASTFM_API_KEY=tu_api_key
LASTFM_API_SECRET=tu_secret
LASTFM_ENABLED=true

# Deezer (sin configuración)
DEEZER_ENABLED=true
```

## 🏗️ Estructura del Proyecto

```
tracktraster/
├── index.php              # Punto de entrada
├── .htaccess              # Configuración Apache
├── .env                   # Variables de entorno
├── application/
│   ├── config/           # Configuración
│   ├── controllers/      # Controladores MVC
│   ├── core/             # Framework base
│   ├── libraries/        # Librerías + APIs
│   ├── models/           # Modelos de datos  
│   └── views/            # Vistas y templates
└── assets/               # Recursos estáticos
```

## 🔐 Panel de Administración

Acceso: `/admin` con usuario administrador

**Funcionalidades**:
- ✅ Estado en tiempo real de las APIs
- ✅ Pruebas automáticas de conectividad  
- ✅ Información del sistema
- ✅ Monitoreo y estadísticas

## 🔒 Seguridad

- **Variables de entorno** protegidas (.env inaccesible)
- **Headers de seguridad** completos
- **Content Security Policy** aplicada
- **Protección de directorios** sensibles
- **Archivos sensibles** bloqueados

## 📊 Estado del Proyecto

- **Versión**: 2.0 (Sistema simplificado)
- **Tamaño**: 0.5 MB optimizado
- **Archivos**: 67 esenciales únicamente
- **APIs funcionales**: 3 plataformas estables
- **Estado**: ✅ Listo para producción

## 🚀 Despliegue

### Servidor Compartido
1. Subir archivos via FTP/cPanel
2. Configurar `.env` con datos de producción
3. Importar base de datos
4. Verificar permisos (755/644)
5. ¡Listo!

### Variables de Producción
```bash
# Base de datos
DB_HOSTNAME=localhost
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
DB_DATABASE=tu_bd

# URL de producción
APP_BASE_URL=https://tudominio.com/

# APIs (configurar credenciales reales)
SPOTIFY_CLIENT_ID=credencial_real
LASTFM_API_KEY=credencial_real
```

## 📞 Soporte

Para issues y mejoras, usar el sistema de Issues de GitHub.

## 📄 Licencia

Proyecto privado - Todos los derechos reservados

---

**🎉 TrackTraster v2.0** - Sistema optimizado y listo para producción- Analítica Musical Inteligente

Una aplicación web moderna para trackear el crecimiento de artistas musicales en países específicos de LATAM, especialmente útil para promotores de eventos y managers musicales.

## 🚀 Características

- **Sistema de Autenticación Completo**: Login/registro con función "recordar credenciales" que realmente funciona
- **Dashboard Moderno**: Interfaz intuitiva y responsive
- **Soporte Multi-usuario**: Cada usuario puede gestionar sus propios seguimientos
- **Enfoque LATAM**: Optimizado para países de Latinoamérica
- **Arquitectura Escalable**: Preparado para integrar APIs de Spotify, YouTube, Last.fm
- **Reportes Visuales**: Sistema preparado para generar analíticas tipo Google Analytics

## 🛠️ Stack Tecnológico

- **Backend**: CodeIgniter (PHP custom framework)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Base de Datos**: MySQL
- **UI Framework**: Bootstrap 5
- **Iconos**: Font Awesome 6
- **Gráficos**: Chart.js (preparado)
- **Servidor de Desarrollo**: XAMPP

## 📋 Requisitos Previos

- XAMPP instalado y ejecutándose
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno

## 🚀 Instalación

### 1. Clonar el proyecto
```bash
# Copiar archivos a la carpeta htdocs de XAMPP
# Ubicación: C:\xampp\htdocs\tracktraster\
```

### 2. Configurar la base de datos
1. Asegúrate de que MySQL esté ejecutándose en XAMPP
2. Abre tu navegador y ve a: `http://localhost/tracktraster/database/install.php?key=tracktraster_setup_2025`
3. Sigue las instrucciones del instalador

### 3. Configurar la aplicación
1. Edita `application/config/config.php` con tus datos:
   - Configura la URL base si es necesario
   - Agrega tus credenciales de Spotify API cuando las tengas

## 🔑 Credenciales por Defecto

Después de la instalación, puedes acceder con:
- **Email**: admin@tracktraster.com
- **Password**: admin123

> ⚠️ **Importante**: Cambia estas credenciales después de tu primer login.

## 📁 Estructura del Proyecto

```
tracktraster/
├── application/           # Aplicación principal
│   ├── config/           # Configuraciones
│   ├── controllers/      # Controladores (Auth, Dashboard)
│   ├── core/             # Clases base (Database, Router, Session)
│   └── views/            # Vistas (HTML/PHP)
├── assets/               # Recursos estáticos
│   ├── css/              # Estilos CSS
│   └── js/               # JavaScript
├── database/             # Scripts de base de datos
│   ├── schema.sql        # Esquema completo
│   └── install.php       # Instalador web
├── .htaccess             # Configuración Apache
└── index.php             # Punto de entrada
```

## 🎯 Funcionalidades Implementadas

### ✅ Sistema de Autenticación
- [x] Login funcional con validación
- [x] Registro de usuarios con validación completa
- [x] "Recordar credenciales" (funciona por 30 días)
- [x] Logout seguro
- [x] Protección CSRF
- [x] Sesiones seguras
- [x] Tokens de recordatorio en base de datos

### ✅ Interfaz de Usuario
- [x] Design moderno y responsive
- [x] Dashboard con sidebar navegable
- [x] Formularios con validación en tiempo real
- [x] Alertas y notificaciones
- [x] Mobile-friendly

### ✅ Arquitectura Backend
- [x] Framework MVC personalizado
- [x] Conexión a base de datos con singleton pattern
- [x] Router con URLs amigables
- [x] Manejo de sesiones robusto
- [x] Validación y sanitización de datos

## 🔄 Próximas Funcionalidades

### 🚧 En Desarrollo
- [ ] Búsqueda y registro de artistas
- [ ] Sistema de seguimientos
- [ ] Integración con Spotify API
- [ ] Dashboard con métricas reales
- [ ] Generación de reportes

### 📊 APIs a Integrar
- [ ] **Spotify Web API**: Popularidad, followers, top tracks por país
- [ ] **YouTube Data API**: Suscriptores, vistas por región
- [ ] **Last.fm API**: Scrobbles y charts regionales

### 📈 Analíticas Planificadas
- [ ] Snapshots iniciales de métricas
- [ ] Tracking automático diario/semanal
- [ ] Análisis de crecimiento antes/después de eventos
- [ ] Alertas por cambios significativos
- [ ] Reportes en PDF exportables

## 🌎 Países Soportados (LATAM)

- Argentina, Bolivia, Brasil, Chile, Colombia
- Costa Rica, Cuba, República Dominicana, Ecuador
- El Salvador, Guatemala, Honduras, México
- Nicaragua, Panamá, Paraguay, Perú
- Puerto Rico, Uruguay, Venezuela

## 🔧 Desarrollo

### Convenciones de Código
- PSR-4 para autoloading
- Nombres de clases en PascalCase
- Nombres de métodos en camelCase
- Comentarios en español
- Validación en frontend y backend

### Base de Datos
- Motor: InnoDB
- Charset: utf8mb4_unicode_ci
- Índices optimizados para consultas frecuentes
- Relaciones con claves foráneas
- Vistas para consultas complejas

## 🔒 Seguridad

- Protección CSRF en todos los formularios
- Preparación de consultas SQL (prepared statements)
- Validación y sanitización de inputs
- Headers de seguridad configurados
- Sesiones con configuración segura
- Cookies HTTPOnly para tokens de recordatorio

## 📱 Responsive Design

- Sidebar colapsable en móviles
- Grids adaptables con Bootstrap 5
- Touch-friendly en dispositivos móviles
- Optimizado para tablets y desktop

## 🚀 Despliegue

### Servidor Compartido
- Compatible con cPanel
- Git integration ready
- .htaccess configurado para Apache
- URLs amigables sin index.php

### Configuración en Producción
1. Cambiar credenciales de base de datos
2. Configurar HTTPS
3. Actualizar headers de seguridad
4. Configurar backups automáticos

## 📧 Soporte

Para soporte técnico o consultas sobre el desarrollo:
- Revisa la documentación en el código
- Verifica logs en `application/logs/`
- Consulta errores PHP en XAMPP

## 📄 Licencia

Este proyecto está desarrollado para uso comercial específico. Todos los derechos reservados.

---

**TrackTraster** © 2025 - Analítica Musical Inteligente para LATAM
