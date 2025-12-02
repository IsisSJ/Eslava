<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    
    // Manejo de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tipo_archivo = mime_content_type($_FILES['imagen']['tmp_name']);
        
        if (in_array($tipo_archivo, $tipos_permitidos)) {
            // Leer imagen como datos binarios
            $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
            
            // Opcional: Redimensionar imagen
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) { // 2MB
                $error = "La imagen es demasiado grande (máximo 2MB)";
            }
        } else {
            $error = "Formato de imagen no válido. Use JPEG, PNG, GIF o WebP.";
        }
    }
    
    if (empty($nombre) || $precio <= 0) {
        $error = "Nombre y precio son obligatorios";
    }
    
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("INSERT INTO articulos (nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen]);
            
            $_SESSION['mensaje'] = "✅ Artículo creado exitosamente";
            header("Location: admin_dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al crear artículo: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Artículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header_admin.php'); ?>
    
    <div class="container mt-4">
        <h2>➕ Nuevo Artículo</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Nombre del Producto *</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Precio ($) *</label>
                    <input type="number" name="precio" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Stock</label>
                    <input type="number" name="stock" class="form-control" min="0" value="0">
                </div>
            </div>
            
            <div class="mb-3">
                <label>Imagen del Producto</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
                <small class="text-muted">Formatos: JPEG, PNG, GIF, WebP (max 2MB)</small>
            </div>
            
            <button type="submit" class="btn btn-success">Crear Artículo</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>