<?php
// init.php - Inicialización centralizada para TODO el sitio
// Este archivo se incluye en TODAS las páginas que necesiten sesión o BD

// ============================================
// 1. CONFIGURACIÓN DE SESIONES (si no está activa)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Configurar solo si estamos en producción (Render)
    if (getenv('RENDER')) {
        ini_set('session.save_path', '/tmp');
        ini_set('session.name', 'FLORES_SESSION');
        ini_set('session.cookie_lifetime', 86400);
        ini_set('session.gc_maxlifetime', 86400);
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
    }
    
    // Iniciar sesión
    session_start();
}

// ============================================
// 2. CONFIGURACIÓN GLOBAL
// ============================================
date_default_timezone_set('America/Mexico_City');
mb_internal_encoding('UTF-8');

// ============================================
// 3. MANEJO DE ERRORES (solo desarrollo)
// ============================================
if (getenv('RENDER') && isset($_GET['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// 4. DEFINIR CONSTANTES (opcional)
// ============================================
define('SITE_NAME', 'Flores de Chinampa');
define('SITE_URL', getenv('RENDER') ? 'https://eslava-3.onrender.com' : 'http://localhost');

// ============================================
// 5. LA CONEXIÓN A BD SE HACE POR SEPARADO
// cuando se necesite, con: require_once 'conexion.php'
// ============================================
?>