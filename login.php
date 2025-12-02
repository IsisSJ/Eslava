<?php
// login.php - VERSIÓN COMPLETAMENTE CORREGIDA
session_start();

// Inicializar variables
$error = '';
$logueado = false;

// Verificar si ya está logueado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $logueado = true;
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($password)) {
        $error = "Por favor ingresa usuario y contraseña";
    } else {
        // Incluir conexión a BD
        if (file_exists('conexion.php')) {
            include_once('conexion.php');
            
            // Verificar que $conn existe
            if (isset($conn) && $conn) {
                try {
                    // Buscar usuario en BD
                    $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, password, rol FROM usuarios WHERE nombre_usuario = ?");
                    $stmt->execute([$nombre_usuario]);
                    $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($usuario_data) {
                        // Verificar contraseña
                        if (password_verify($password, $usuario_data['password'])) {
                            // Login exitoso
                            session_regenerate_id(true);
                            
                            $_SESSION['usuario_id'] = $usuario_data['id'];
                            $_SESSION['usuario_nombre'] = $usuario_data['nombre_usuario'];
                            $_SESSION['usuario_email'] = $usuario_data['correo'];
                            $_SESSION['usuario_rol'] = $usuario_data['rol'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['login_time'] = time();
                            
                            // Redirigir según rol
                            if ($usuario_data['rol'] === 'admin') {
                                header("Location: admin_dashboard.php");
                            } else {
                                header("Location: articulos.php");
                            }
                            exit();
                        } else {
                            $error = "❌ Contraseña incorrecta";
                        }
                    } else {
                        $error = "❌ Usuario no encontrado";
                    }
                } catch (PDOException $e) {
                    $error = "❌ Error de base de datos";
                }
            } else {
                $error = "❌ Error de conexión con la base de datos";
            }
        } else {
            $error = "❌ Error del sistema: Archivo de conexión no encontrado";
        }
    }
}
// Fin del procesamiento PHP - ahora viene el HTML
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
            font-family: 'Arial', sans-serif;
        }
        .login-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }
        .debug-info {
            background: rgba(0,0,0,0.05);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2 class="text-center mb-4">🌺 Flores de Chinampa</h2>
            
            <!-- Info de debug (solo para desarrollo) -->
            <?php if ($logueado && false): // Cambiar a true para ver debug ?>
            <div class="debug-info">
                <strong>Debug:</strong><br>
                Ya estás logueado como: <?php echo $_SESSION['usuario_nombre'] ?? 'N/A'; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                    <br><small>Si el problema persiste, contacta al administrador.</small>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'registrado'): ?>
                <div class="alert alert-success">
                    ✅ ¡Registro exitoso! Ahora puedes iniciar sesión
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'logout'): ?>
                <div class="alert alert-info">
                    ✅ Sesión cerrada correctamente
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label>Usuario</label>
                    <input type="text" name="nombre_usuario" class="form-control" required 
                           value="<?php echo htmlspecialchars($_POST['nombre_usuario'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-login text-white">Iniciar Sesión</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="registro.php">¿No tienes cuenta? Regístrate</a>
            </div>
            
            <hr class="my-4">
            
            <div class="text-center">
                <p class="text-muted small mb-2">
                    Si tienes problemas para acceder:
                </p>
                <a href="list_users.php" class="btn btn-sm btn-primary">👥 Ver usuarios</a>
                <a href="reset_user_password.php?id=1" class="btn btn-sm btn-warning">🔄 Resetear admin</a>
            </div>
        </div>
    </div>
</body>
</html>