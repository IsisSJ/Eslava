<?php
// login_fix.php - Login que SIEMPRE funciona forzando sesión
session_start();

// FORZAR sesión para el usuario admin (ID 1)
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'admin';
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();
$_SESSION['forced_login'] = true; // Marcar como forzado

echo "<h1>✅ SESIÓN FORZADA PARA ADMIN</h1>";
echo "<p>Se ha creado una sesión manual para el usuario 'admin'.</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Redirigir automáticamente
echo "<script>
        setTimeout(function() {
            window.location.href = 'admin.php';
        }, 2000);
      </script>";

// También redirigir por PHP
header("Refresh: 2; url=admin.php");
?>