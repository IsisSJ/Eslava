<?php
// reset_password_public.php - Resetear contraseña SIN necesidad de estar logueado
session_start();

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    die("ID de usuario inválido");
}

include_once('conexion.php');

$mensaje = '';
$nueva_password = '';
$usuario_info = '';

// Obtener info del usuario primero
try {
    $stmt = $conn->prepare("SELECT nombre_usuario, correo, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        die("Usuario no encontrado");
    }
    
    $usuario_info = "Usuario: <strong>" . htmlspecialchars($usuario['nombre_usuario']) . "</strong> (" . htmlspecialchars($usuario['correo']) . ")";
    
} catch (Exception $e) {
    die("Error al obtener información del usuario: " . $e->getMessage());
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
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-card {
            max-width: 500px;
            margin: 0 auto;
            border: 2px solid #28a745;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card reset-card shadow">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0">🔄 Resetear Contraseña</h4>
            </div>
            <div class="card-body">
                <div class="warning-box">
                    <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Acceso de Emergencia</h5>
                    <p class="mb-0">Esta página permite resetear contraseñas sin necesidad de iniciar sesión. Úsala solo si no puedes acceder al sistema.</p>
                </div>
                
                <div class="mb-3">
                    <h5><?php echo $usuario_info; ?></h5>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo strpos($mensaje, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $mensaje; ?>
                        <?php if ($nueva_password && strpos($mensaje, '✅') !== false): ?>
                            <br><br>
                            <div class="alert alert-info">
                                <h6>📋 Credenciales actualizadas:</h6>
                                <p class="mb-1"><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                                <p class="mb-1"><strong>Nueva contraseña:</strong> <code><?php echo htmlspecialchars($nueva_password); ?></code></p>
                                <p class="mb-0"><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="text" name="password" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['password'] ?? 'admin123'); ?>"
                               required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sugerencias rápidas:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php 
                            $sugerencias = ['admin123', 'chinampa123', 'flores2024', 'password123', 'clave123'];
                            foreach ($sugerencias as $sug): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                    onclick="document.getElementsByName('password')[0].value='<?php echo $sug; ?>'">
                                <?php echo $sug; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-key me-2"></i>Resetear Contraseña
                        </button>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Ir al Login
                        </a>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <h6>¿Necesitas resetear otro usuario?</h6>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <a href="reset_password_public.php?id=1" class="btn btn-sm btn-outline-primary">Admin (ID: 1)</a>
                        <a href="reset_password_public.php?id=2" class="btn btn-sm btn-outline-primary">Kioann (ID: 2)</a>
                        <a href="reset_password_public.php?id=7" class="btn btn-sm btn-outline-primary">admi (ID: 7)</a>
                    </div>
                    <p class="small text-muted mt-2">Ver todos los usuarios: <a href="list_users.php">list_users.php</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>