<?php
// session_fix.php - Forzar sesión para admin
// NO usar session_start() directamente

require_once 'init.php';

// Limpiar sesión anterior
session_regenerate_id(true);

// Forzar datos de admin (ID 1)
$_SESSION = [
    'usuario_id' => 1,
    'usuario_nombre' => 'admin',
    'usuario_email' => 'admin@chinampa.com',
    'usuario_rol' => 'admin',
    'logged_in' => true,
    'login_time' => time(),
    'session_fixed' => true
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Sesión Reparada</title>
</head>
<body style='background:#28a745;color:white;text-align:center;padding:50px;'>
    <h1>✅ Sesión Reparada</h1>
    <p>Redirigiendo al panel de administración...</p>
    <script>
        setTimeout(function() {
            window.location.href = 'admin.php';
        }, 2000);
    </script>
</body>
</html>";
?>