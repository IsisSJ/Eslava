<?php
session_start();
include_once('conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telecomercio = trim($_POST['telecomercio'] ?? '');

    // Validaciones
    if (empty($nombre_usuario) || empty($correo) || empty($password)) {
        $error = "Los campos usuario, correo y contraseña son obligatorios";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo no es válido";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        try {
            // Verificar que la conexión existe
            if (!$conn) {
                $error = "❌ Error de conexión a la base de datos";
            } else {
                $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
                $check_stmt->execute([$nombre_usuario, $correo]);
                
                if ($check_stmt->fetch()) {
                    $error = "El nombre de usuario o correo ya está registrado";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $rol = 'cliente';
                    
                    $insert_stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, password, telecomercio, rol) VALUES (?, ?, ?, ?, ?)");
                    
                    if ($insert_stmt->execute([$nombre_usuario, $correo, $password_hash, $telecomercio, $rol])) {
                        header("Location: login.php?success=registrado");
                        exit();
                    } else {
                        $error = "❌ Error al registrar usuario. Intenta nuevamente.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "❌ Error de base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Flores de Chinampa</title>
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
        .register-container {
            max-width: 500px;
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
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .info-box {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="card shadow-lg">
                <div class="card-header text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registro de Usuario</h4>
                    <p class="mb-0 mt-2">Crea tu cuenta en Flores de Chinampa</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 'registrado'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            ✅ ¡Registro exitoso! Ahora puedes iniciar sesión
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="registro.php">
                        <div class="mb-3">
                            <label for="nombre_usuario" class="form-label">
                                <i class="fas fa-user me-2"></i>Nombre de Usuario
                            </label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" 
                                   value="<?php echo htmlspecialchars($nombre_usuario ?? ''); ?>" 
                                   placeholder="Ingresa tu nombre de usuario" required>
                            <div class="form-text">Este será tu nombre para iniciar sesión</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="correo" name="correo" 
                                   value="<?php echo htmlspecialchars($correo ?? ''); ?>" 
                                   placeholder="tu@email.com" required>
                            <div class="form-text">Aquí recibirás el ticket de tus compras</div>
                        </div>

                        <div class="mb-3">
                            <label for="telecomercio" class="form-label">
                                <i class="fas fa-phone me-2"></i>Teléfono (Opcional)
                            </label>
                            <input type="tel" class="form-control" id="telecomercio" name="telecomercio" 
                                   value="<?php echo htmlspecialchars($telecomercio ?? ''); ?>" 
                                   placeholder="+52 123 456 7890">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Mínimo 6 caracteres" required minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Repite tu contraseña" required minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>¿Ya tienes cuenta? Inicia sesión
                        </a>
                    </div>

                    <!-- Información de la cuenta -->
                    <div class="mt-4 p-3 info-box rounded">
                        <small class="text-muted">
                            <strong><i class="fas fa-info-circle me-1"></i>Información importante:</strong><br>
                            • Tu <strong>correo</strong> se usará para enviarte el ticket de compra<br>
                            • Tu <strong>nombre de usuario</strong> será para iniciar sesión<br>
                            • Cuenta tipo: <strong>cliente</strong> - Podrás ver y comprar productos
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

    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePasswords() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>