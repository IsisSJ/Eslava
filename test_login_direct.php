<?php
// login_working.php - Login SIMPLE que FUNCIONA
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: " . ($_SESSION['usuario_rol'] === 'admin' ? 'admin_dashboard.php' : 'articulos.php'));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = "Ingresa usuario y contraseña";
    } else {
        // Incluir conexión
        require_once 'conexion.php';
        
        try {
            // Buscar usuario
            $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // ÉXITO - Crear sesión
                session_regenerate_id(true);
                
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
                $_SESSION['usuario_rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                
                // Redirigir inmediatamente
                if ($user['rol'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: articulos.php");
                }
                exit();
                
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
            
        } catch (Exception $e) {
            $error = "Error del sistema: " . $e->getMessage();
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
        .btn-custom {
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
                <p class="text-muted">Inicia sesión en tu cuenta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
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
                
                <button type="submit" class="btn btn-custom">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-muted small mb-2">
                    Credenciales por defecto:<br>
                    <strong>admin</strong> / admin123
                </p>
                
                <div class="mt-3">
                    <a href="debug_login.php" class="btn btn-sm btn-info">🔧 Debug</a>
                    <a href="emergency_access.php" class="btn btn-sm btn-warning">🚨 Emergencia</a>
                    <a href="registro.php" class="btn btn-sm btn-outline-secondary">📝 Registrarse</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>