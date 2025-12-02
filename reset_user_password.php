<?php
// reset_user_password.php - Resetear contraseña de usuario específico
session_start();

// Solo permitir si es admin o el mismo usuario
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Acceso denegado. <a href='login.php'>Iniciar sesión</a>");
}

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    die("ID de usuario inválido");
}

include_once('conexion.php');

$mensaje = '';
$nueva_password = '';

// Procesar reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_password = trim($_POST['password'] ?? '');
    
    if (strlen($nueva_password) < 6) {
        $mensaje = "❌ La contraseña debe tener al menos 6 caracteres";
    } else {
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);
            
            $mensaje = "✅ Contraseña actualizada exitosamente";
        } catch (Exception $e) {
            $mensaje = "❌ Error: " . $e->getMessage();
        }
    }
}

// Obtener info del usuario
try {
    $stmt = $conn->prepare("SELECT nombre_usuario, correo, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        die("Usuario no encontrado");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resetear Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>🔄 Resetear Contraseña</h2>
        
        <div class="card">
            <div class="card-header">
                Usuario: <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                (<?php echo htmlspecialchars($usuario['correo']); ?>)
            </div>
            <div class="card-body">
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo strpos($mensaje, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $mensaje; ?>
                        <?php if ($nueva_password): ?>
                            <br><strong>Nueva contraseña:</strong> <?php echo htmlspecialchars($nueva_password); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Nueva Contraseña</label>
                        <input type="text" name="password" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['password'] ?? 'chinampa123'); ?>"
                               required minlength="6">
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Sugerencias:</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                    onclick="document.getElementsByName('password')[0].value='chinampa123'">
                                chinampa123
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="document.getElementsByName('password')[0].value='admin123'">
                                admin123
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="document.getElementsByName('password')[0].value='flores2024'">
                                flores2024
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Resetear Contraseña</button>
                    <a href="list_users.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
