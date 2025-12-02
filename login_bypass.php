<?php
// login_bypass.php - Acceso directo sin verificación
session_start();

// Limpiar sesión anterior
session_destroy();
session_start();

// Datos del usuario admin (del debug)
$_SESSION = [
    'usuario_id' => 1,
    'usuario_nombre' => 'admin',
    'usuario_email' => 'admin@chinampa.com',
    'usuario_rol' => 'admin',
    'logged_in' => true,
    'login_time' => time(),
    'bypass' => true
];

// Escribir la sesión inmediatamente
session_write_close();

// Redirigir
header("Location: admin.php");
exit();
?>