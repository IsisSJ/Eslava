<?php
// articulos.php - Catálogo con imágenes
require_once 'config_session.php';

// Verificar sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_definitivo.php");
    exit();
}

require_once 'conexion.php';

// Obtener artículos con stock
$stmt = $conn->prepare("SELECT * FROM articulos WHERE stock > 0 ORDER BY nombre");
$stmt->execute();
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-image-container {
            height: 200px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        .product-image {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <?php 
    if ($_SESSION['usuario_rol'] === 'admin') {
        include('header_admin.php');
    } else {
        include('header_cliente.php');
    }
    ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-store me-2"></i>Catálogo de Productos</h1>
            <div>
                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="nuevo_articulo.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Nuevo Producto
                    </a>
                <?php endif; ?>
                <a href="carrito.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-1"></i>Ver Carrito
                </a>
            </div>
        </div>
        
        <?php if (empty($articulos)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <h4>No hay productos disponibles</h4>
                <p class="mb-0">Pronto tendremos nuevo stock de flores.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($articulos as $articulo): ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <!-- Imagen del producto -->
                        <div class="product-image-container">
                            <?php
                            if (!empty($articulo['imagen'])) {
                                $base64 = base64_encode($articulo['imagen']);
                                echo "<img src='data:image/jpeg;base64,$base64' 
                                      class='product-image'
                                      alt='" . htmlspecialchars($articulo['nombre']) . "'>";
                            } else {
                                echo "<div class='text-center text-muted'>
                                        <i class='fas fa-image fa-4x'></i>
                                        <p class='mt-2'>Sin imagen</p>
                                      </div>";
                            }
                            ?>
                            
                            <!-- Badge de stock -->
                            <span class="stock-badge badge bg-<?php echo $articulo['stock'] > 10 ? 'success' : 'warning'; ?>">
                                <?php echo $articulo['stock']; ?> disponibles
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($articulo['nombre']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo strlen($articulo['descripcion']) > 100 ? 
                                    substr($articulo['descripcion'], 0, 100) . '...' : 
                                    $articulo['descripcion']; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-success mb-0">
                                    $<?php echo number_format($articulo['precio'], 2); ?>
                                </h4>
                                
                                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                                    <div class="btn-group">
                                        <a href="subir_imagen.php?id=<?php echo $articulo['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Subir imagen">
                                            <i class="fas fa-image"></i>
                                        </a>
                                        <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Formulario para agregar al carrito -->
                                    <form method="GET" action="carrito.php" class="d-inline">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                                        <div class="input-group" style="width: 150px;">
                                            <input type="number" name="cantidad" class="form-control form-control-sm" 
                                                   value="1" min="1" max="<?php echo $articulo['stock']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    Mostrando <?php echo count($articulos); ?> producto(s)
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>