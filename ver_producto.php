<?php
session_start();
include_once("conexion.php");

// DEPURACIÓN: Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("❌ Error: No se proporcionó ID de producto");
}

$articulo_id = intval($_GET['id']);

if ($articulo_id <= 0) {
    die("❌ Error: ID de producto inválido");
}

// Incrementar contador de visitas
$update_visitas = $conn->prepare("UPDATE articulos SET visitas = visitas + 1 WHERE id = ?");
$update_visitas->bind_param("i", $articulo_id);
$update_visitas->execute();
$update_visitas->close();

// Obtener información del artículo
$stmt = $conn->prepare("SELECT id, nombre, precio, stock, descripcion, imagen_path, visitas FROM articulos WHERE id = ?");
$stmt->bind_param("i", $articulo_id);
$stmt->execute();
$result = $stmt->get_result();
$articulo = $result->fetch_assoc();

if (!$articulo) {
    die("❌ Error: Producto no encontrado");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($articulo['nombre']); ?> - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .product-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            border-radius: 10px;
            max-height: 400px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card product-card">
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Imagen del producto -->
                            <div class="col-md-6 text-center">
                                <?php if (!empty($articulo['imagen_path']) && file_exists($articulo['imagen_path'])): ?>
                                    <img src="<?php echo $articulo['imagen_path']; ?>" 
                                         alt="<?php echo htmlspecialchars($articulo['nombre']); ?>" 
                                         class="img-fluid product-image">
                                <?php else: ?>
                                    <div class="text-center py-5 bg-light rounded">
                                        <i class="fas fa-image fa-5x text-muted mb-3"></i>
                                        <p class="text-muted">Imagen no disponible</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Información del producto -->
                            <div class="col-md-6">
                                <nav aria-label="breadcrumb" class="mb-3">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                                        <li class="breadcrumb-item"><a href="productos.php">Productos</a></li>
                                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($articulo['nombre']); ?></li>
                                    </ol>
                                </nav>
                                
                                <h1 class="display-5 fw-bold text-success"><?php echo htmlspecialchars($articulo['nombre']); ?></h1>
                                
                                <div class="mb-3">
                                    <h2 class="text-primary">$<?php echo number_format($articulo['precio'], 2); ?></h2>
                                </div>
                                
                                <div class="mb-4">
                                    <span class="badge <?php echo $articulo['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?> fs-6">
                                        <?php echo $articulo['stock'] > 0 ? '✅ En stock' : '❌ Agotado'; ?>
                                    </span>
                                    <span class="badge bg-info fs-6">
                                        <i class="fas fa-eye me-1"></i><?php echo $articulo['visitas']; ?> visitas
                                    </span>
                                </div>
                                
                                <?php if (!empty($articulo['descripcion'])): ?>
                                    <div class="mb-4">
                                        <h5>Descripción:</h5>
                                        <p class="fs-5"><?php echo nl2br(htmlspecialchars($articulo['descripcion'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="productos.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>Volver
                                    </a>
                                    <?php if ($articulo['stock'] > 0): ?>
                                        <a href="carrito.php?agregar=<?php echo $articulo['id']; ?>" class="btn btn-success btn-lg flex-fill">
                                            <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-lg flex-fill" disabled>
                                            <i class="fas fa-times me-2"></i>Agotado
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Información adicional -->
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6><i class="fas fa-info-circle me-2"></i>Información importante:</h6>
                                    <ul class="mb-0">
                                        <li>Envío gratis en compras mayores a $500</li>
                                        <li>Tiempo de entrega: 2-3 días hábiles</li>
                                        <li>Solo entregas en CDMX y área metropolitana</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>