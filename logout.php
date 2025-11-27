<?php
session_start();

// Debug: Mostrar sesión antes de destruir (opcional, quitar en producción)
error_log("Logout attempt - Session data: " . print_r($_SESSION, true));

// Limpiar todas las variables de sesión
$_SESSION = array();

// Borrar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Limpiar cache de redirección
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir al login con mensaje de éxito
header("Location: login.php?success=logout");
exit();
?>