<?php
// login.php - VERSIÓN CORREGIDA PARA RENDER + CLEVER CLOUD
// =========================================================

// 1. INCLUIR CONFIGURACIÓN DE SESIONES PRIMERO (ESENCIAL para Render)
require_once 'config_session.php';

// 2. Verificar si ya está logueado (AHORA con sesiones funcionando)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirigir según el rol del usuario
    if (isset($_SESSION['usuario_rol'])) {
        if ($_SESSION['usuario_rol'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: cliente_dashboard.php");
            exit();
        }
    }
}

// 3. Inicializar variables
$error = '';
$success_message = '';

// 4. Mostrar mensajes de éxito
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'registrado':
            $success_message = "✅ ¡Registro exitoso! Ahora puedes iniciar sesión";
            break;
        case 'logout':
            $success_message = "✅ Sesión cerrada correctamente";
            break;
        case 'password_reset':
            $success_message = "✅ Contraseña actualizada correctamente";
            break;
    }
}

// 5. Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre_usuario) || empty($password)) {
        $error = "❌ Por favor ingresa usuario y contraseña";
    } else {
        // Incluir conexión a BD
        if (file_exists('conexion.php')) {
            require_once 'conexion.php';
            
            try {
                // Buscar usuario en la base de datos
                // IMPORTANTE: Asegúrate que la tabla se llama 'usuarios'
                $stmt = $conn->prepare("
                    SELECT id, nombre_usuario, correo, password, rol
                    FROM usuarios 
                    WHERE nombre_usuario = ? 
                    LIMIT 1
                ");
                $stmt->execute([$nombre_usuario]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($usuario) {
                    // Verificar contraseña
                    if (password_verify($password, $usuario['password'])) {
                        // ✅ LOGIN EXITOSO
                        
                        // Establecer datos de sesión
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                        $_SESSION['usuario_email'] = $usuario['correo'] ?? '';
                        $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'cliente';
                        $_SESSION['logged_in'] = true;
                        $_SESSION['login_time'] = time();
                        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                        
                        // Registrar login en bitácora si existe
                        if (file_exists('actualizar_bitacora.php')) {
                            include 'actualizar_bitacora.php';
                            actualizarBitacora($usuario['id'], 'login', 'Inicio de sesión exitoso');
                        }
                        
                        // Redirigir según rol
                        if ($_SESSION['usuario_rol'] === 'admin') {
                            header("Location: admin_dashboard.php");
                        } else {
                            header("Location: cliente_dashboard.php");
                        }
                        exit();
                        
                    } else {
                        $error = "❌ Contraseña incorrecta";
                    }
                } else {
                    $error = "❌ Usuario no encontrado";
                    
                    // Para debug: mostrar usuarios existentes
                    if (isset($_GET['debug'])) {
                        $stmt = $conn->query("SELECT nombre_usuario, rol FROM usuarios LIMIT 10");
                        $usuarios = $stmt->fetchAll();
                        $error .= "<br><small>Usuarios disponibles: " . implode(', ', array_column($usuarios, 'nombre_usuario')) . "</small>";
                    }
                }
                
            } catch (PDOException $e) {
                $error = "❌ Error de base de datos: " . $e->getMessage();
                
                // Para debug: información adicional
                if (isset($_GET['debug'])) {
                    $error .= "<br><small>Consulta SQL falló</small>";
                }
            }
        } else {
            $error = "❌ Error del sistema: Archivo de conexión no encontrado";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌺 Login - Flores de Chinampa</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #20c997;
            --accent-color: #6f42c1;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            margin: 20px auto;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .brand-header i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .brand-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .brand-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        
        .divider span {
            padding: 0 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .debug-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid var(--accent-color);
            font-size: 0.85rem;
        }
        
        .session-status {
            background: #e7f5ff;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #339af0;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 25px;
                margin: 10px;
            }
            
            .brand-header i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Encabezado de la marca -->
            <div class="brand-header">
                <i class="fas fa-spa"></i>
                <h1>Flores de Chinampa</h1>
                <p>Inicia sesión en tu cuenta</p>
            </div>
            
            <!-- Mensaje de éxito -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Mensaje de error -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Estado de sesión (para debug) -->
            <?php if (isset($_GET['debug'])): ?>
                <div class="session-status">
                    <strong>Debug de Sesión:</strong><br>
                    Sesión ID: <?php echo session_id(); ?><br>
                    Estado: <?php echo isset($_SESSION['logged_in']) ? 'Logueado' : 'No logueado'; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de login -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-4">
                    <label for="nombre_usuario" class="form-label">
                        <i class="fas fa-user me-2"></i>Nombre de Usuario
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nombre_usuario" 
                           name="nombre_usuario" 
                           value="<?php echo htmlspecialchars($_POST['nombre_usuario'] ?? ''); ?>"
                           placeholder="Ingresa tu usuario"
                           required
                           autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Contraseña
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Ingresa tu contraseña"
                           required>
                    <div class="form-text">
                        <a href="recuperar_password.php" class="text-decoration-none">
                            <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Recordar mi sesión
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login mb-4">
                    <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                </button>
                
                <div class="divider">
                    <span>¿No tienes cuenta?</span>
                </div>
                
                <div class="text-center">
                    <a href="registro.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-user-plus me-2"></i> Crear Nueva Cuenta
                    </a>
                </div>
            </form>
            
            <!-- Enlaces de debug y utilidades -->
            <?php if (isset($_GET['debug']) || true): // Cambiar a false para producción ?>
                <hr class="my-4">
                <div class="text-center">
                    <p class="text-muted small mb-2">
                        <i class="fas fa-tools me-1"></i> Herramientas de desarrollo:
                    </p>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="list_users.php" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i> Ver usuarios
                        </a>
                        <a href="test_conexion.php" class="btn btn-outline-info">
                            <i class="fas fa-database"></i> Test BD
                        </a>
                        <a href="debug_session.php" class="btn btn-outline-warning">
                            <i class="fas fa-bug"></i> Debug sesión
                        </a>
                        <a href="crear_admin.php" class="btn btn-outline-danger">
                            <i class="fas fa-user-shield"></i> Crear admin
                        </a>
                    </div>
                </div>
                
                <!-- Info de entorno -->
                <div class="debug-info mt-3">
                    <strong>Información del entorno:</strong><br>
                    PHP: <?php echo phpversion(); ?><br>
                    Servidor: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?><br>
                    Entorno: <?php echo getenv('RENDER') ? 'Render.com' : (getenv('MYSQL_ADDON_HOST') ? 'Clever Cloud' : 'Local'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus en el campo de usuario
        document.getElementById('nombre_usuario').focus();
        
        // Mostrar/ocultar contraseña
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.createElement('button');
            togglePassword.type = 'button';
            togglePassword.className = 'btn btn-sm btn-outline-secondary position-absolute';
            togglePassword.style.right = '10px';
            togglePassword.style.top = '50%';
            togglePassword.style.transform = 'translateY(-50%)';
            togglePassword.innerHTML = '<i class="fas fa-eye"></i>';
            
            const passwordField = document.getElementById('password');
            const passwordContainer = passwordField.parentNode;
            passwordContainer.style.position = 'relative';
            passwordContainer.appendChild(togglePassword);
            
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        });
    </script>
</body>
</html>