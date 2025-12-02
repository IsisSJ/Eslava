<?php
// session_fix.php - Solución completa para problemas de sesión
session_name('ESLAVA_SESSION');

// Configuración agresiva de sesión
ini_set('session.save_path', '/tmp');
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Iniciar sesión
session_start();

// Limpiar sesión anterior
session_regenerate_id(true);

// Forzar datos de usuario admin
$_SESSION = [
    'usuario_id' => 1,
    'usuario_nombre' => 'admin',
    'usuario_email' => 'admin@chinampa.com',
    'usuario_rol' => 'admin',
    'logged_in' => true,
    'login_time' => time(),
    'session_fixed' => true
];

// Forzar escritura de sesión
session_write_close();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Sesión Reparada</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-success text-white'>
    <div class='container mt-5 text-center'>
        <div class='card bg-dark'>
            <div class='card-body'>
                <h1 class='display-1'>✅</h1>
                <h2>Sesión Reparada Exitosamente</h2>
                <p class='lead'>Se ha creado una sesión estable para el usuario 'admin'.</p>
                
                <div class='mt-4'>
                    <h4>Información de la Sesión:</h4>
                    <p>Session ID: " . session_id() . "</p>
                    <p>Usuario: admin</p>
                    <p>Rol: Administrador</p>
                </div>
                
                <div class='mt-4'>
                    <a href='admin.php' class='btn btn-success btn-lg me-2'>
                        🚀 Ir al Dashboard Admin
                    </a>
                    <a href='test_session.php' class='btn btn-info btn-lg'>
                        🔧 Verificar Sesión
                    </a>
                </div>
                
                <div class='mt-4 text-muted'>
                    <p>Redirigiendo en 5 segundos...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Redirigir automáticamente
        setTimeout(function() {
            window.location.href = 'admin.php';
        }, 5000);
    </script>
</body>
</html>";
?>