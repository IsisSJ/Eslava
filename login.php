<?php
session_start();
include_once('conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

// ✅ CORREGIDO: Primero procesar el POST, luego verificar sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($password)) {
        $error = "Por favor ingresa usuario y contraseña";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, password, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$nombre_usuario]);
            $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_data) {
                if (password_verify($password, $usuario_data['password'])) {
                    // Login exitoso - Establecer variables de sesión COMPATIBLES
                    $_SESSION['usuario_id'] = $usuario_data['id'];
                    $_SESSION['usuario_nombre'] = $usuario_data['nombre_usuario'];
                    $_SESSION['usuario'] = $usuario_data['nombre_usuario']; // Compatibilidad con header
                    $_SESSION['usuario_email'] = $usuario_data['correo'];
                    $_SESSION['usuario_rol'] = $usuario_data['rol'];
                    $_SESSION['rol'] = $usuario_data['rol']; // Compatibilidad con header
                    $_SESSION['logged_in'] = true;

                    // Debug: Mostrar sesión (quitar en producción)
                    error_log("Login exitoso - Usuario: " . $usuario_data['nombre_usuario'] . " - Rol: " . $usuario_data['rol']);

                    // Redirigir según el rol
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
            $error = "❌ Error de base de datos: " . $e->getMessage();
        }
    }
}

// ✅ CORREGIDO: Verificar sesión DESPUÉS de procesar POST
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    // Si ya está logueado, redirigir según el rol
    if ($_SESSION['usuario_rol'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: articulos.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
        }
        .password-container {
            position: relative;
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1e9e8a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .session-debug {
            background: rgba(0,0,0,0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card shadow-lg">
                <div class="card-header text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h4>
                    <p class="mb-0 mt-2">Flores de Chinampa</p>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Debug de sesión (opcional, quitar en producción) -->
                    <?php if (false): // Cambiar a true para debug ?>
                    <div class="session-debug text-white">
                        <strong>Debug Sesión:</strong><br>
                        <?php 
                        echo "logged_in: " . ($_SESSION['logged_in'] ?? 'false') . "<br>";
                        echo "usuario_nombre: " . ($_SESSION['usuario_nombre'] ?? 'no-set') . "<br>";
                        echo "usuario_rol: " . ($_SESSION['usuario_rol'] ?? 'no-set');
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 'registrado'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            ✅ ¡Registro exitoso! Ahora puedes iniciar sesión
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 'logout'): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            ✅ Sesión cerrada correctamente
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="nombre_usuario" class="form-label">
                                <i class="fas fa-user me-2"></i>Usuario
                            </label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" 
                                   value="<?php echo htmlspecialchars($_POST['nombre_usuario'] ?? ''); ?>" 
                                   placeholder="Ingresa tu usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Ingresa tu contraseña" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordar sesión</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="registro.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>¿No tienes cuenta? Regístrate aquí
                        </a>
                    </div>

                    <!-- Información de prueba -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong><i class="fas fa-info-circle me-1"></i>Cuentas de prueba:</strong><br>
                            • Admin: usuario: <strong>admin</strong> / contraseña: <strong>admin123</strong><br>
                            • Cliente: usuario: <strong>cliente</strong> / contraseña: <strong>cliente123</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const passwordIcon = document.getElementById(fieldId + '-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        }
    }

    // Auto-focus en el campo de usuario
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nombre_usuario').focus();
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>