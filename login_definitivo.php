<?php
// login_definitivo.php - Login QUE SÍ FUNCIONA
// SIN configuraciones complejas, SIN includes innecesarios

// Iniciar sesión SIMPLE
session_start();

// Verificar si ya está logueado
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
        // Conexión DIRECTA sin archivos externos
        try {
            $host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
            $dbname = 'bc8i4pda2kn2fqs150qm';
            $username = 'uo5qglcqiyhjhqot';
            $dbpassword = 'wSlvgtI1vH86LAydhriK';
            $port = '3306';
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $conn = new PDO($dsn, $username, $dbpassword);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Buscar usuario EXACTO
            $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    // ✅ LOGIN EXITOSO
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
                    $_SESSION['usuario_rol'] = $user['rol'];
                    $_SESSION['logged_in'] = true;
                    
                    // DEBUG: Mostrar en consola
                    error_log("✅ LOGIN EXITOSO - Usuario: " . $user['nombre_usuario']);
                    
                    // Redirigir INMEDIATAMENTE
                    if ($user['rol'] === 'admin') {
                        header("Location: admin_simple.php");
                    } else {
                        header("Location: articulos.php");
                    }
                    exit();
                    
                } else {
                    $error = "Contraseña incorrecta";
                    error_log("❌ Contraseña incorrecta para: " . $usuario);
                }
            } else {
                $error = "Usuario no encontrado: " . htmlspecialchars($usuario);
                error_log("❌ Usuario no encontrado: " . $usuario);
            }
            
        } catch (Exception $e) {
            $error = "Error de conexión: " . $e->getMessage();
            error_log("❌ Error BD: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Definitivo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-entrar {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            color: white;
            font-size: 16px;
        }
        .credentials-box {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h2 style="color: #28a745;">🌺 Flores de Chinampa</h2>
                <p class="text-muted">Login Definitivo</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Error:</strong> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="credentials-box">
                <h5>✅ Credenciales Confirmadas:</h5>
                <p class="mb-1"><strong>Usuario:</strong> <code>admin</code></p>
                <p class="mb-0"><strong>Contraseña:</strong> <code>admin123</code></p>
                <small class="text-muted">(Verificado en el debug)</small>
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
                
                <button type="submit" class="btn btn-entrar">
                    🔓 Iniciar Sesión Definitiva
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-muted small mb-2">
                    Si aún tienes problemas:
                </p>
                <div class="d-grid gap-2">
                    <a href="admin_access_force.php" class="btn btn-warning btn-sm">
                        🚨 Acceso Forzado Inmediato
                    </a>
                    <a href="debug_login_complete.php" class="btn btn-info btn-sm">
                        🔍 Ver Debug Completo
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Debug en consola
        console.log("✅ Login definitivo cargado");
        console.log("Usuario: admin");
        console.log("Contraseña: admin123");
    </script>
</body>
</html>