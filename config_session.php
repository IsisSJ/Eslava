<?php
// config_session.php - Configuración de sesiones para Render/Clever Cloud
// Este archivo debe incluirse ANTES de cualquier uso de sesiones

// Solo configurar si no hay sesión activa
if (session_status() === PHP_SESSION_NONE) {
    
    // Detectar entorno
    $is_render = getenv('RENDER') ? true : false;
    $is_clever_cloud = getenv('MYSQL_ADDON_HOST') ? true : false;
    
    if ($is_render || $is_clever_cloud) {
        // Configuración óptima para entornos cloud (Render/Clever Cloud)
        ini_set('session.save_path', '/tmp');              // Usar /tmp para sesiones
        ini_set('session.name', 'FLORES_SESSION');         // Nombre personalizado
        ini_set('session.cookie_lifetime', 86400);         // 24 horas
        ini_set('session.gc_maxlifetime', 86400);          // 24 horas
        ini_set('session.cookie_secure', '1');             // Solo HTTPS
        ini_set('session.cookie_httponly', '1');           // Solo HTTP, no JS
        ini_set('session.cookie_samesite', 'Lax');         // Política SameSite
        ini_set('session.use_strict_mode', '1');           // Modo estricto
        ini_set('session.use_only_cookies', '1');          // Solo cookies, no URLs
        ini_set('session.use_trans_sid', '0');             // No usar SID en URLs
        ini_set('session.cookie_path', '/');               // Ruta raíz
    }
    
    // Configuración común
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Establecer zona horaria
    date_default_timezone_set('America/Mexico_City');
    
    // Iniciar sesión con el nombre configurado
    if ($is_render || $is_clever_cloud) {
        session_name('FLORES_SESSION');
    }
    
    // Iniciar la sesión
    session_start();
    
    // Log de sesión (solo en desarrollo)
    if (($is_render || $is_clever_cloud) && isset($_GET['debug_session'])) {
        error_log("=== SESSION STARTED ===");
        error_log("- Session ID: " . session_id());
        error_log("- Session Name: " . session_name());
        error_log("- Save Path: " . ini_get('session.save_path'));
    }
    
} else {
    // Sesión ya está iniciada, solo asegurar nombre
    if (getenv('RENDER') && session_name() !== 'FLORES_SESSION') {
        // Si estamos en Render pero la sesión tiene nombre diferente
        session_write_close();
        session_name('FLORES_SESSION');
        session_start();
    }
}
?>