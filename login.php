<?php
// login.php - VERSIÓN CON MANEJO DE ERRORES MEJORADO
session_start();

// Debug
error_log("=== INICIANDO LOGIN.PHP ===");

$error = '';
$success = '';

// Verificar si ya está logueado (PERO NO REDIRIGIR - solo para mostrar estado)
$logueado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Procesando formulario POST");
    
    // Verificar que tenemos los datos necesarios
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($nombre_usuario) || empty($password)) {
        $error = "Por favor ingresa usuario y contraseña";
        error_log("Error: Campos vacíos");
    } else {
        try {
            // Cargar conexión a BD
            error_log("Intentando cargar conexion.php");
            
            if (!file_exists('conexion.php')) {
                throw new Exception("Archivo de conexión no encontrado");
            }
            
            include_once('conexion.php');
            
            // Verificar que $conn existe
            if (!isset($conn) || !$conn) {
                throw new Exception("Conexión a BD no disponible");
            }
            
            error_log("Conexión a BD cargada, ejecutando consulta...");
            
            // Preparar y ejecutar consulta
            $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, password, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$nombre_usuario]);
            $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_data) {
                if (password_verify($password, $usuario_data['password'])) {
                    // LOGIN EXITOSO
                    session_regenerate_id(true);
                    
                    $_SESSION['usuario_id'] = $usuario_data['id'];
                    $_SESSION['usuario_nombre'] = $usuario_data['nombre_usuario'];
                    $_SESSION['usuario_email'] = $usuario_data['correo'];
                    $_SESSION['usuario_rol'] = $usuario_data['rol'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    
                    error_log("✅ Login exitoso para: " . $usuario_data['nombre_usuario']);
                    
                    // Redirigir según rol
                    if ($usuario_data['rol'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: articulos.php");
                    }
                    exit();
                } else {
                    $error = "❌ Contraseña incorrecta";
                    error_log("Contraseña incorrecta para: " . $nombre_usuario);
                }
            } else {
                $error = "❌ Usuario no encontrado";
                error_log("Usuario no encontrado: " . $nombre_usuario);
            }
        } catch (PDOException $e) {
            $error = "❌ Error de base de datos: " . $e->getMessage();
            error_log("Error PDO: " . $e->getMessage());
        } catch (Exception $e) {
            $error = "❌ Error del sistema: " . $e->getMessage();
            error_log("Error general: " . $e->getMessage());
        }
    }
}

// NO redirigir automáticamente - siempre mostrar formulario
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
            <?php if (false): // Cambiar a true para ver debug ?>
            <div class="debug-info">
                <strong>Debug:</strong><br>
                Logueado: <?php echo $logueado ? 'Sí' : 'No'; ?><br>
                Session ID: <?php echo session_id(); ?>
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
                <a href="test_conexion_simple.php" class="btn btn-sm btn-info">🔧 Test Conexión BD</a>
                <a href="reset_all.php" class="btn btn-sm btn-warning">🔄 Limpiar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>