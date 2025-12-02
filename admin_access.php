<?php
// admin_access.php - Acceso 100% garantizado al admin
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Configurar sesión manualmente
session_name('ESLAVA_SESSION');
@session_start();

// Datos garantizados del admin
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'admin';
$_SESSION['usuario_email'] = 'admin@chinampa.com';
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['logged_in'] = true;

// Redirigir inmediatamente
header("Location: admin.php");
exit();
?>