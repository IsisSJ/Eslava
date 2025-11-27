<?php
session_start();
include_once('conexion.php');

// DEPURACIÓN: Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telecomercio = trim($_POST['telecomercio'] ?? ''); // Campo opcional

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
            // ✅ CONSULTA CORREGIDA - usando nombre_usuario y correo
            $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
            $check_stmt->execute([$nombre_usuario, $correo]);
            
            if ($check_stmt->fetch()) {
                $error = "El nombre de usuario o correo ya está registrado";
            } else {
                // Hash de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $rol = 'cliente'; // Rol por defecto
                
                // ✅ INSERT CORREGIDO - con los campos correctos
                $insert_stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, password, telecomercio, rol) VALUES (?, ?, ?, ?, ?)");
                
                if ($insert_stmt->execute([$nombre_usuario, $correo, $password_hash, $telecomercio, $rol])) {
                    // Redirigir al login con mensaje de éxito
                    header("Location: login.php?success=registrado");
                    exit();
                } else {
                    $error = "❌ Error al registrar usuario. Intenta nuevamente.";
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
    <title>Registro - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-container {
            max-width: 500px;
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
        <div class="register-container">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registro de Usuario</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Mostrar mensajes -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
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
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>¿Ya tienes cuenta? Inicia sesión
                        </a>
                    </div>

                    <!-- Información de la cuenta -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong><i class="fas fa-info-circle me-1"></i>Información importante:</strong><br>
                            • Tu <strong>correo</strong> se usará para enviarte el ticket de compra<br>
                            • Tu <strong>nombre de usuario</strong> será para iniciar sesión<br>
                            • Cuenta tipo: <strong>cliente</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para los ojitos -->
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

    // Validación de contraseñas en tiempo real
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