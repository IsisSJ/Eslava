<?php
// carrito.php - VERSIÓN CORREGIDA
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

include_once('conexion.php');

// Limpiar carrito de productos que ya no existen
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id_articulo => $cantidad) {
        $sql = "SELECT id FROM articulos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_articulo]);
        
        if ($stmt->rowCount() === 0) {
            // El producto ya no existe, eliminarlo del carrito
            unset($_SESSION['carrito'][$id_articulo]);
        }
    }
    
    // Si el carrito quedó vacío después de limpiar, mostrarlo
    if (empty($_SESSION['carrito'])) {
        unset($_SESSION['carrito']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🛒 Carrito de Compras - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .cart-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .cart-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-seedling me-2"></i>Flores de Chinampa
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario']); ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-store me-1"></i>Seguir Comprando
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="cart-container">
            <div class="card shadow">
                <div class="card-header cart-header text-center">
                    <h3 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Tu Carrito de Compras</h3>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['mensaje']); ?>
                    <?php endif; ?>
                    
                    <?php if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Tu carrito está vacío</h4>
                            <p class="text-muted">Agrega algunos productos maravillosos a tu carrito</p>
                            <a href="index.php" class="btn btn-success btn-lg">
                                <i class="fas fa-store me-2"></i>Explorar Productos
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio Unitario</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    $items_validos = 0;
                                    
                                    foreach ($_SESSION['carrito'] as $id_articulo => $cantidad):
                                        // Obtener información del producto con manejo de errores
                                        $sql = "SELECT id, nombre, precio, stock FROM articulos WHERE id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute([$id_articulo]);
                                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                                        
                                        // Solo mostrar productos que existen
                                        if ($producto):
                                            $items_validos++;
                                            $subtotal = $producto['precio'] * $cantidad;
                                            $total += $subtotal;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted">Código: #<?php echo $producto['id']; ?></small>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-box me-1"></i>Stock disponible: <?php echo $producto['stock']; ?>
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            <strong>$<?php echo number_format($producto['precio'], 2); ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-6"><?php echo $cantidad; ?></span>
                                                <div class="ms-2">
                                                    <a href="agregar_carrito.php?id_articulo=<?php echo $id_articulo; ?>&cantidad=1" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <?php if ($cantidad > 1): ?>
                                                        <a href="eliminar_carrito.php?id=<?php echo $id_articulo; ?>&reducir=1" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-minus"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <strong class="text-success">$<?php echo number_format($subtotal, 2); ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <a href="eliminar_carrito.php?id=<?php echo $id_articulo; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar este producto del carrito?')">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    
                                    // Si no hay productos válidos, mostrar carrito vacío
                                    if ($items_validos === 0): 
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                                <p>Los productos en tu carrito ya no están disponibles</p>
                                                <a href="index.php" class="btn btn-success">Ver Productos Disponibles</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if ($items_validos > 0): ?>
                                <tfoot class="table-group-divider">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th colspan="2" class="text-success">
                                            <h4 class="mb-0">$<?php echo number_format($total, 2); ?></h4>
                                        </th>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <?php if ($items_validos > 0): ?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Seguir Comprando
                                </a>
                                <a href="vaciar_carrito.php" class="btn btn-outline-warning ms-2"
                                   onclick="return confirm('¿Estás seguro de vaciar todo el carrito?')">
                                    <i class="fas fa-broom me-2"></i>Vaciar Carrito
                                </a>
                            </div>
                            <div>
                                <a href="confirmar_pedido.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Confirmar Pedido
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>