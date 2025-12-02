<?php
// subir_imagen.php - Subir imagen para producto existente
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login_definitivo.php");
    exit();
}

require_once 'conexion.php';

$producto_id = intval($_GET['id'] ?? 0);
$mensaje = '';
$error = '';

// Obtener información del producto
$producto = null;
if ($producto_id > 0) {
    $stmt = $conn->prepare("SELECT id, nombre FROM articulos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
}

if (!$producto) {
    die("Producto no encontrado");
}

// Procesar subida de imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Validar tamaño (máximo 5MB)
        if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
            $error = "La imagen es demasiado grande (máximo 5MB)";
        } else {
            // Validar tipo
            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $tipo_archivo = mime_content_type($_FILES['imagen']['tmp_name']);
            
            if (in_array($tipo_archivo, $tipos_permitidos)) {
                // Leer imagen
                $imagen_data = file_get_contents($_FILES['imagen']['tmp_name']);
                
                // Actualizar en BD
                $stmt = $conn->prepare("UPDATE articulos SET imagen = ? WHERE id = ?");
                if ($stmt->execute([$imagen_data, $producto_id])) {
                    $mensaje = "✅ Imagen actualizada correctamente";
                } else {
                    $error = "❌ Error al guardar la imagen";
                }
            } else {
                $error = "❌ Formato no válido. Use JPEG, PNG, GIF o WebP";
            }
        }
    } else {
        $error = "❌ No se seleccionó ninguna imagen o hubo un error en la subida";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Imagen - <?php echo htmlspecialchars($producto['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('header_admin.php'); ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-image me-2"></i>
                            Subir Imagen para: <?php echo htmlspecialchars($producto['nombre']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-success"><?php echo $mensaje; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Seleccionar imagen</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*" required>
                                <div class="form-text">
                                    Formatos: JPEG, PNG, GIF, WebP (máximo 5MB)
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload me-2"></i>Subir Imagen
                                </button>
                                <a href="admin.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Vista Previa</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        // Mostrar imagen actual si existe
                        $stmt = $conn->prepare("SELECT imagen FROM articulos WHERE id = ?");
                        $stmt->execute([$producto_id]);
                        $imagen_data = $stmt->fetchColumn();
                        
                        if (!empty($imagen_data)) {
                            $base64 = base64_encode($imagen_data);
                            echo "<img src='data:image/jpeg;base64,$base64' 
                                  class='img-fluid rounded' 
                                  alt='Vista previa'
                                  style='max-height: 300px;'>";
                            echo "<p class='mt-2 text-success'><i class='fas fa-check-circle me-1'></i>El producto ya tiene imagen</p>";
                        } else {
                            echo "<div class='text-center text-muted py-5'>
                                    <i class='fas fa-image fa-4x mb-3'></i>
                                    <p>Este producto aún no tiene imagen</p>
                                </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>