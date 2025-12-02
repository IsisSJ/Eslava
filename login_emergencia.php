<?php
// login_emergencia.php - Login temporal sin BD
session_start();

// Datos de usuarios de emergencia (solo para desarrollo)
$usuarios_emergencia = [
    'admin' => [
        'password' => 'admin123', // En producción, usar hash
        'rol' => 'admin',
        'email' => 'admin@chinampa.com'
    ],
    'cliente' => [
        'password' => 'cliente123',
        'rol' => 'cliente', 
        'email' => 'cliente@ejemplo.com'
    ]
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (isset($usuarios_emergencia[$usuario]) && 
        $usuarios_emergencia[$usuario]['password'] === $password) {
        
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_nombre'] = $usuario;
        $_SESSION['usuario_email'] = $usuarios_emergencia[$usuario]['email'];
        $_SESSION['usuario_rol'] = $usuarios_emergencia[$usuario]['rol'];
        $_SESSION['logged_in'] = true;
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Emergencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">⚠️ Login de Emergencia</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Base de datos temporalmente no disponible</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                Usuarios disponibles:<br>
                                • <strong>admin</strong> / admin123<br>
                                • <strong>cliente</strong> / cliente123
                            </small>
                        </div>
                        
                        <hr>
                        <div class="text-center">
                            <a href="clever_credentials_test.php" class="btn btn-sm btn-info">
                                🔧 Verificar conexión BD
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>