<?php
// ============================================================
// Configuración global de la aplicación
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'capturadora');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Rutas base
define('BASE_PATH', dirname(__DIR__, 2));  // raíz del proyecto
define('APP_PATH',  dirname(__DIR__));     // /app

// URL base (sin barra final)
define('BASE_URL', '/Capturadora_VTV-2.0');

// Directorio base de videos (Unidad de red mapeada o ruta UNC)
define('VIDEO_BASE_DIR', 'O:/'); // o '//192.168.22.130/Capturas_VTV/' si O: falla en PHP

// Zona horaria
date_default_timezone_set('America/Caracas');
