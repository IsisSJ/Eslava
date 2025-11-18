<?php
// login.php - VERSIÓN CORREGIDA (BUSCAR 'admin' EN LUGAR DE 'administrador')

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('conexion.php');

$error_login = '';

// PROCESAR LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    
    try {
        $sql = "SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ? OR correo = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Login exitoso
                $_SESSION['usuario'] = $user['nombre_usuario'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['user_id'] = $user['id'];
                
                // ✅ CORRECCIÓN: Cambiar 'administrador' por 'admin'
                if ($user['rol'] === 'admin') {  // ← AQUÍ ESTABA EL ERROR
                    header('Location: gestion_articulos.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error_login = "Contraseña incorrecta";
            }
        } else {
            $error_login = "Usuario no encontrado";
        }
    } catch (PDOException $e) {
        $error_login = "Error de base de datos: " . $e->getMessage();
    }
}

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    // ✅ CORRECCIÓN: Cambiar 'administrador' por 'admin'
    if ($_SESSION['rol'] === 'admin') {  // ← AQUÍ TAMBIÉN
        header('Location: gestion_articulos.php');
    } else {
        header('Location: index.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-seedling me-2"></i>Iniciar Sesión</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($error_login)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_login); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">
                                <i class="fas fa-user me-2"></i>Usuario o Email
                            </label>
                            <input type="text" class="form-control" id="usuario" name="usuario" 
                                   value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" 
                                   placeholder="Ingresa tu usuario o email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Ingresa tu contraseña" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Entrar
                        </button>
                    </form>

                    <!-- Información de prueba ACTUALIZADA -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong><i class="fas fa-info-circle me-1"></i>Usuarios de prueba:</strong><br>
                            • <strong>Admin:</strong> Usuario: admi | Contraseña: password<br>
                            • <strong>Cliente:</strong> Usuario: Perro | Contraseña: password<br>
                            • <strong>Cliente:</strong> Usuario: Carpio | Contraseña: password
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('password-icon');
        
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
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>