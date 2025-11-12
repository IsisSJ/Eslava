<?php
session_start();
include_once("conexion.php");

// DEPURACIÓN: Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Procesar mensajes
$mensaje = '';
$tipo_mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Procesar datos del formulario si se envió
$datos_formulario = $_SESSION['datos_formulario'] ?? [];
unset($_SESSION['datos_formulario']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar datos
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telecomercio = trim($_POST['telecomercio'] ?? '');
    
    // Validaciones
    $errores = [];

    // Validar nombre de usuario
    if (empty($nombre_usuario)) {
        $errores[] = "El nombre de usuario es requerido";
    } elseif (strlen($nombre_usuario) < 3) {
        $errores[] = "El nombre de usuario debe tener al menos 3 caracteres";
    }

    // Validar correo
    if (empty($correo)) {
        $errores[] = "El correo electrónico es requerido";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }

    // Validar contraseña
    if (empty($password)) {
        $errores[] = "La contraseña es requerida";
    } elseif (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }

    // Validar confirmación de contraseña
    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }

    // Verificar si el usuario o email ya existen
    if (empty($errores)) {
        $check_sql = "SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if ($check_stmt) {
            $check_stmt->bind_param("ss", $nombre_usuario, $correo);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $errores[] = "El nombre de usuario o correo electrónico ya están registrados";
            }
            $check_stmt->close();
        } else {
            $errores[] = "Error en la consulta de verificación: " . $conn->error;
        }
    }

    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Rol por defecto
        $rol = 'cliente';
        
        // Preparar la consulta de inserción
        $sql = "INSERT INTO usuarios (nombre_usuario, correo, password, telecomercio, rol) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssss", $nombre_usuario, $correo, $password_hash, $telecomercio, $rol);
            
            if ($stmt->execute()) {
                // Registro exitoso
                $_SESSION['mensaje'] = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
                $_SESSION['tipo_mensaje'] = "success";
                header('Location: login.php');
                exit();
            } else {
                $errores[] = "Error al registrar el usuario: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errores[] = "Error en la consulta SQL: " . $conn->error;
        }
    }

    // Si hay errores, guardarlos para mostrar en el formulario
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_formulario'] = [
            'nombre_usuario' => $nombre_usuario,
            'correo' => $correo,
            'telecomercio' => $telecomercio
        ];
        header('Location: registro.php');
        exit();
    }
}

// Obtener errores de sesión
$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['errores']);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .logo-section {
            text-align: center;
            padding: 30px 0 20px 0;
        }
        .logo-icon {
            font-size: 3.5rem;
            color: #28a745;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }
        .password-container {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="card register-card">
                <div class="logo-section">
                    <i class="fas fa-seedling logo-icon"></i>
                    <h3 class="text-success fw-bold">Flores de Chinampa</h3>
                    <p class="text-muted">Crea tu cuenta</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Mostrar mensajes -->
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Mostrar errores -->
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="registro.php">
                        <!-- Nombre de Usuario -->
                        <div class="mb-3">
                            <label for="nombre_usuario" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Nombre de Usuario
                            </label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" 
                                   value="<?php echo htmlspecialchars($datos_formulario['nombre_usuario'] ?? ''); ?>" 
                                   placeholder="Ingresa tu nombre de usuario" required>
                            <div class="form-text">Mínimo 3 caracteres</div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="mb-3">
                            <label for="correo" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="correo" name="correo"
                                   value="<?php echo htmlspecialchars($datos_formulario['correo'] ?? ''); ?>" 
                                   placeholder="tu@correo.com" required>
                        </div>

                        <!-- Teléfono (Opcional) -->
                        <div class="mb-3">
                            <label for="telecomercio" class="form-label fw-semibold">
                                <i class="fas fa-phone me-2"></i>Teléfono (Opcional)
                            </label>
                            <input type="tel" class="form-control" id="telecomercio" name="telecomercio"
                                   value="<?php echo htmlspecialchars($datos_formulario['telecomercio'] ?? ''); ?>"
                                   placeholder="Ej: 555-123-4567">
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Ingresa tu contraseña" minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                            </label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" placeholder="Confirma tu contraseña" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm-password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-register text-white w-100 py-2 fw-semibold">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </button>
                    </form>

                    <!-- Enlace de login -->
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            ¿Ya tienes cuenta? 
                            <a href="login.php" class="text-success fw-semibold text-decoration-none">
                                Inicia sesión aquí
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Mostrar/ocultar contraseña
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

        // Validación en tiempo real
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