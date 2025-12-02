<?php
// login_working.php - Login CORREGIDO
// NO usar session_start() aquí directamente

// Incluir inicialización
require_once 'init.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = "Ingresa usuario y contraseña";
    } else {
        // Incluir conexión SOLO cuando se necesite
        require_once 'conexion.php';
        
        try {
            $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login exitoso
                session_regenerate_id(true);
                
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
                $_SESSION['usuario_rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Redirigir
                header("Location: " . ($user['rol'] === 'admin' ? 'admin.php' : 'articulos.php'));
                exit();
                
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
            
        } catch (Exception $e) {
            $error = "Error del sistema. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="text-center mb-4">
                <h2 style="color: #28a745;">🌺 Flores de Chinampa</h2>
                <p class="text-muted">Inicia sesión con tus credenciales</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- IMPORTANTE: Mostrar usuario que SÍ funciona -->
            <div class="alert alert-info">
                <strong>Usa estas credenciales:</strong><br>
                Usuario: <code>admin</code><br>
                Contraseña: <code>admin123</code>
            </div>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" 
                           value="admin" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" 
                           value="admin123" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-muted small">
                    ¿Problemas para acceder?
                </p>
                <div class="d-grid gap-2">
                    <a href="session_fix.php" class="btn btn-sm btn-warning">🔧 Reparar Sesión</a>
                    <a href="check_sessions.php" class="btn btn-sm btn-info">🔍 Verificar Estado</a>
                    <a href="registro.php" class="btn btn-sm btn-outline-secondary">📝 Crear Cuenta</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>