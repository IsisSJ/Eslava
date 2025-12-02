<?php
// logout.php - Versión agresiva
session_start();

// Destruir sesión completamente
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');

// Eliminar todas las cookies
foreach ($_COOKIE as $key => $value) {
    setcookie($key, '', time() - 3600, '/');
}

// Redirigir con parámetro para evitar caché
header("Location: login.php?logout=1&nocache=" . time());
exit();
?>