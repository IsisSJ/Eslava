<?php
// config_session.php - Configuración optimizada para Render
if (session_status() === PHP_SESSION_NONE) {
    // Configuración para Render.com
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());
    ini_set('session.cookie_secure', 0); // 0 si no tienes HTTPS
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 86400); // 24 horas
    
    session_start();
    
    // Regenerar ID de sesión periódicamente para seguridad
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>