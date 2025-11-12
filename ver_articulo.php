<?php
session_start();
include_once("conexion.php");

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM articulos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $articulo = $result->fetch_assoc();
    
    if (!$articulo) {
        header('Location: gestion_articulos.php');
        exit();
    }
} else {
    header('Location: gestion_articulos.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Artículo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success">
                <i class="fas fa-eye me-2"></i>Detalles del Artículo
            </h2>
            <a href="gestion_articulos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="text-success"><?php echo htmlspecialchars($articulo['nombre']); ?></h3>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-tag me-2"></i>Precio:</strong></p>
                                <h4 class="text-success">$<?php echo number_format($articulo['precio'], 2); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-boxes me-2"></i>Stock:</strong></p>
                                <h4><span class="badge bg-<?php echo $articulo['stock'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $articulo['stock']; ?> unidades
                                </span></h4>
                            </div>
                        </div>
                        
                        <?php if (!empty($articulo['descripcion'])): ?>
                        <div class="mt-4">
                            <p><strong><i class="fas fa-file-alt me-2"></i>Descripción:</strong></p>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($articulo['descripcion'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <p><strong><i class="fas fa-calendar me-2"></i>Fecha de creación:</strong></p>
                            <p class="text-muted"><?php echo $articulo['fecha_creacion']; ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($articulo['imagen'])): ?>
                    <div class="col-md-4 text-center">
                        <div class="border rounded p-3">
                            <p><strong>Imagen del producto:</strong></p>
                            <img src="images/<?php echo htmlspecialchars($articulo['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($articulo['nombre']); ?>" 
                                 class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Editar Artículo
            </a>
            <a href="gestion_articulos.php" class="btn btn-secondary">
                <i class="fas fa-list me-2"></i>Volver a la Lista
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>