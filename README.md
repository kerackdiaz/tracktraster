# TrackTraster 🎵

## Plataforma de Análisis y Seguimiento de Artistas Musicales

TrackTraster es una plataforma web que permite a los usuarios seguir y analizar artistas musicales de múltiples plataformas streaming, con un sistema de administración simplificado y gestión de credenciales mediante variables de entorno.

### ✨ Características Principales

- **Multi-plataforma**: Integración con Spotify, Deezer y Last.fm
- **Seguimiento de artistas**: Permite a los usuarios hacer seguimiento de sus artistas favoritos
- **Panel de administración**: Monitoreo de APIs y pruebas automáticas de conectividad
- **Configuración segura**: Credenciales gestionadas via variables de entorno (.env)
- **Responsive**: Interfaz adaptada para dispositivos móviles y desktop

### 🚀 Tecnologías

- **Backend**: PHP 7.4+ (Framework personalizado tipo MVC)
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Base de datos**: MySQL/MariaDB
- **APIs**: Spotify Web API, Deezer API, Last.fm API
- **Autenticación**: Sistema de sesiones personalizado

### 📋 Requisitos del Servidor

- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.2+
- mod_rewrite habilitado
- Extensiones PHP: PDO, MySQL, cURL, JSON

### ⚙️ Instalación

1. **Subir archivos** al servidor web
2. **Configurar base de datos**:
   ```sql
   CREATE DATABASE tracktraster_db;
   ```
3. **Importar** estructura de base de datos (incluida en la instalación)
4. **Configurar .env**:
   ```bash
   cp .env.example .env
   # Editar .env con tus credenciales
   ```
5. **Configurar permisos**:
   ```bash
   chmod 755 application/
   chmod 600 .env
   ```

### 🔧 Configuración de Variables de Entorno

Editar el archivo `.env` con tus configuraciones:

```bash
# Base de datos
DB_HOSTNAME=localhost
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
DB_DATABASE=tracktraster_db

# URL de la aplicación
APP_BASE_URL=https://tudominio.com/

# APIs Musicales
SPOTIFY_CLIENT_ID=tu_spotify_client_id
SPOTIFY_CLIENT_SECRET=tu_spotify_client_secret
SPOTIFY_ENABLED=true

LASTFM_API_KEY=tu_lastfm_api_key
LASTFM_API_SECRET=tu_lastfm_secret
LASTFM_ENABLED=true

DEEZER_ENABLED=true
```

### 🎯 APIs Soportadas

#### ✅ Spotify
- **Estado**: Completamente funcional
- **Características**: Búsqueda de artistas, datos de popularidad, imágenes
- **Configuración**: Requiere Client ID y Client Secret
- **Obtener credenciales**: [Spotify Developer Dashboard](https://developer.spotify.com/dashboard)

#### ✅ Deezer  
- **Estado**: Completamente funcional
- **Características**: Búsqueda de artistas, datos de popularidad, álbumes
- **Configuración**: No requiere credenciales (API pública)

#### ✅ Last.fm
- **Estado**: Completamente funcional  
- **Características**: Búsqueda de artistas, biografías, estadísticas de escucha
- **Configuración**: Requiere API Key y Secret
- **Obtener credenciales**: [Last.fm API](https://www.last.fm/api/account/create)

#### ❌ SoundCloud
- **Estado**: Deshabilitado
- **Motivo**: API limitada y restricciones de acceso

#### ❌ YouTube Music
- **Estado**: Deshabilitado
- **Motivo**: Errores en la API (parámetros no válidos)

### 🏗️ Estructura del Proyecto

```
tracktraster/
├── index.php                    # Punto de entrada
├── .htaccess                   # Reglas de reescritura
├── .env                        # Variables de entorno (no incluir en repo)
├── .env.example               # Plantilla de configuración
├── application/
│   ├── config/               # Configuración
│   ├── controllers/          # Controladores MVC
│   ├── core/                 # Clases base del framework
│   ├── libraries/            # Librerías personalizadas
│   │   ├── platforms/       # APIs de plataformas musicales
│   │   └── MusicPlatformManager.php
│   ├── models/              # Modelos de datos
│   └── views/               # Vistas y templates
└── assets/                   # Recursos estáticos
    ├── css/
    ├── js/
    └── images/
```

### 🔐 Panel de Administración

Accede al panel de administración en: `/admin`

**Funcionalidades**:
- ✅ Monitoreo del estado de las APIs
- ✅ Pruebas automáticas de conectividad
- ✅ Información del sistema
- ✅ Estadísticas de uso

**Usuario administrador**: Configurar en el código o usar `admin@tracktraster.com`

### 🐛 Resolución de Problemas

#### Error de conexión a base de datos
- Verificar credenciales en `.env`
- Verificar que la base de datos existe
- Verificar permisos del usuario

#### APIs no funcionan
- Verificar credenciales en `.env`
- Verificar que las APIs están habilitadas
- Probar desde el panel de administración: `/admin/api_status`

#### Páginas no cargan (Error 404)
- Verificar que mod_rewrite esté habilitado
- Verificar configuración de `.htaccess`
- Verificar APP_BASE_URL en `.env`

### 📞 Soporte

Para reportar bugs o solicitar características:
- Verificar la configuración del servidor
- Revisar logs de errores
- Comprobar el panel de administración

### 📄 Licencia

Proyecto privado - Todos los derechos reservados

### 🔄 Versión

**v2.0** - Sistema simplificado con gestión de credenciales via .env
- Eliminada configuración web de APIs
- Panel de administración de solo lectura
- Configuración manual via archivo .env
- 3 plataformas musicales estables (Spotify, Deezer, Last.fm) - Analítica Musical Inteligente

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
