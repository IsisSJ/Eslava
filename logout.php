<?php
// logout.php - Cerrar sesión COMPLETAMENTE
session_start();

// Destruir TODA la sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al login definitivo
header("Location: login_definitivo.php?msg=logout");
exit();
?>