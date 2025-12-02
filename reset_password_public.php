<?php
// reset_password_public.php - Resetear contraseña SIN necesidad de estar logueado
include_once('conexion.php');

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    $user_id = 1; // Por defecto resetear admin
}

$mensaje = '';
$nueva_password = '';
$usuario_info = '';

// Obtener info del usuario
try {
    $stmt = $conn->prepare("SELECT nombre_usuario, correo, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        die("Usuario no encontrado");
    }
    
    $usuario_info = "Usuario: <strong>" . htmlspecialchars($usuario['nombre_usuario']) . "</strong> (" . htmlspecialchars($usuario['correo']) . ")";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

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
            
            $mensaje = "✅ Contraseña actualizada exitosamente!";
            
        } catch (Exception $e) {
            $mensaje = "❌ Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Resetear Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h4 class="mb-0">🔄 Resetear Contraseña - Acceso Público</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Nota:</strong> Esta página permite resetear contraseñas sin necesidad de iniciar sesión.
                </div>
                
                <h5><?php echo $usuario_info; ?></h5>
                
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo strpos($mensaje, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $mensaje; ?>
                        <?php if ($nueva_password && strpos($mensaje, '✅') !== false): ?>
                            <hr>
                            <h6>📋 Nuevas Credenciales:</h6>
                            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                            <p><strong>Contraseña:</strong> <code><?php echo htmlspecialchars($nueva_password); ?></code></p>
                            <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Nueva Contraseña</label>
                        <input type="text" name="password" class="form-control" 
                               value="admin123" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Sugerencias:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementsByName('password')[0].value='admin123'">admin123</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementsByName('password')[0].value='chinampa123'">chinampa123</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementsByName('password')[0].value='flores2024'">flores2024</button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Resetear Contraseña</button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p><strong>Resetear otros usuarios:</strong></p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="?id=1" class="btn btn-sm btn-primary">Admin (ID 1)</a>
                        <a href="?id=2" class="btn btn-sm btn-primary">Kioann (ID 2)</a>
                        <a href="?id=7" class="btn btn-sm btn-primary">admi (ID 7)</a>
                    </div>
                    <p class="mt-2"><a href="debug_login.php">Ver todos los usuarios</a></p>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="login_working.php" class="btn btn-outline-primary">Ir al Login</a>
            </div>
        </div>
    </div>
</body>
</html>