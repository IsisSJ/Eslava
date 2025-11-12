<?php
session_start();

// Verificación MÍNIMA de sesión
if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include_once("conexion.php");

// Obtener user_id de la manera más flexible posible
$usuario_id = null;

// Opción 1: Desde parámetro GET (para admin editando otros usuarios)
if (isset($_GET['id']) && $_SESSION['rol'] === 'admin') {
    $usuario_id = intval($_GET['id']);
} 
// Opción 2: Desde sesión (para usuario editando su perfil)
elseif (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];
}
// Opción 3: Buscar por nombre de usuario como fallback
else {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $_SESSION['usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $usuario_id = $row['id'];
        $_SESSION['user_id'] = $usuario_id; // Guardar para futuras requests
    }
    $stmt->close();
}

// Si aún no tenemos user_id, error
if (!$usuario_id) {
    header('Location: login.php');
    exit();
}

$mensaje = "";
$es_propio_perfil = ($usuario_id == ($_SESSION['user_id'] ?? null));

// Obtener datos del usuario
$usuario = null;
$stmt = $conn->prepare("SELECT id, nombre_usuario, correo, rol, foto_perfil, fecha_registro FROM usuarios WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
}

if (!$usuario) {
    header('Location: ' . ($es_propio_perfil ? 'login.php' : 'usuarios.php'));
    exit();
}

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $correo = trim($_POST['correo']);
    
    $actualizacion_exitosa = false;
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        // Subir con foto
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, correo = ?, foto_perfil = ? WHERE id = ?");
        if ($stmt) {
            $null = NULL;
            $stmt->bind_param("ssbi", $nombre_usuario, $correo, $null, $usuario_id);
            $stmt->send_long_data(2, $foto_perfil);
            $actualizacion_exitosa = $stmt->execute();
            $stmt->close();
        }
    } else {
        // Subir sin foto
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, correo = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $nombre_usuario, $correo, $usuario_id);
            $actualizacion_exitosa = $stmt->execute();
            $stmt->close();
        }
    }
    
    if ($actualizacion_exitosa) {
        if ($es_propio_perfil) {
            $_SESSION['usuario'] = $nombre_usuario;
        }
        $mensaje = "<div class='alert alert-success'>Perfil actualizado correctamente</div>";
        // Recargar datos
        $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, rol, foto_perfil, fecha_registro FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al actualizar el perfil</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <?php echo $es_propio_perfil ? 'Editar Mi Perfil' : 'Editar Usuario'; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php echo $mensaje; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nombre de Usuario</label>
                                <input type="text" class="form-control" name="nombre_usuario" 
                                       value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="correo" 
                                       value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Foto de Perfil</label>
                                <input type="file" class="form-control" name="foto_perfil" accept="image/*">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="<?php echo $es_propio_perfil ? 'menu.php' : 'usuarios.php'; ?>" 
                                   class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>