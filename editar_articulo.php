<?php
session_start();
include_once("conexion.php");

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$articulo = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM articulos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $articulo = $result->fetch_assoc();
    
    if (!$articulo) {
        $_SESSION['mensaje'] = "Artículo no encontrado";
        header('Location: gestion_articulos.php');
        exit();
    }
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $descripcion = $_POST['descripcion'];
    
    // Manejar imagen
    $imagen = $articulo['imagen']; // Mantener imagen actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
    }
    
    $stmt = $conn->prepare("UPDATE articulos SET nombre = ?, precio = ?, stock = ?, descripcion = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("sdisbi", $nombre, $precio, $stock, $descripcion, $imagen, $id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Artículo actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header('Location: gestion_articulos.php');
        exit();
    } else {
        $error = "Error al actualizar el artículo: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success">
                <i class="fas fa-edit me-2"></i>Editar Artículo
            </h2>
            <a href="gestion_articulos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Artículo</label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($articulo['nombre']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3"><?php echo htmlspecialchars($articulo['descripcion']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Precio</label>
                                        <input type="number" step="0.01" class="form-control" name="precio" 
                                               value="<?php echo $articulo['precio']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stock</label>
                                        <input type="number" class="form-control" name="stock" 
                                               value="<?php echo $articulo['stock']; ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Imagen del Producto</label>
                                <?php if (!empty($articulo['imagen'])): ?>
                                    <div class="text-center mb-3">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($articulo['imagen']); ?>" 
                                             alt="Imagen actual" class="img-fluid rounded" style="max-height: 200px;">
                                        <p class="text-muted mt-2">Imagen actual</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="imagen" accept="image/*">
                                <div class="form-text">Selecciona una nueva imagen (opcional)</div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Actualizar Artículo
                    </button>
                    <a href="gestion_articulos.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>