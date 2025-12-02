<?php
// registro_cliente.php - Registro SIMPLE para clientes
require_once 'conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validaciones
    if (empty($nombre_usuario) || empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico no válido";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        try {
            // Verificar si el usuario o correo ya existen
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
            $stmt->execute([$nombre_usuario, $correo]);
            
            if ($stmt->fetch()) {
                $error = "El nombre de usuario o correo ya está registrado";
            } else {
                // Crear usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, password, telecomercio, rol) VALUES (?, ?, ?, ?, 'cliente')");
                
                if ($stmt->execute([$nombre_usuario, $correo, $password_hash, $telefono])) {
                    $success = "✅ ¡Registro exitoso! Ahora puedes iniciar sesión";
                    // Limpiar campos
                    $nombre_usuario = $correo = $telefono = '';
                } else {
                    $error = "Error al registrar. Intenta nuevamente.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
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
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registro de Cliente</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre de Usuario *</label>
                                <input type="text" name="nombre_usuario" class="form-control" 
                                       value="<?php echo htmlspecialchars($nombre_usuario); ?>" 
                                       required maxlength="50">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico *</label>
                                <input type="email" name="correo" class="form-control" 
                                       value="<?php echo htmlspecialchars($correo); ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Teléfono (opcional)</label>
                                <input type="tel" name="telefono" class="form-control" 
                                       value="<?php echo htmlspecialchars($telefono); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control" 
                                       required minlength="6">
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirmar Contraseña *</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       required minlength="6">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Registrarse
                                </button>
                                <a href="login_definitivo.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Ya tengo cuenta
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>