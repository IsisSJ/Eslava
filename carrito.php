<?php
// carrito.php - Carrito de compras completo
require_once 'config_session.php';
require_once 'conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['accion'])) {
    procesarAccionCarrito();
    header("Location: carrito.php");
    exit();
}

function procesarAccionCarrito() {
    if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];
        $id_producto = intval($_GET['id'] ?? 0);
        
        switch ($accion) {
            case 'agregar':
                $cantidad = intval($_GET['cantidad'] ?? 1);
                agregarAlCarrito($id_producto, $cantidad);
                break;
                
            case 'eliminar':
                eliminarDelCarrito($id_producto);
                break;
                
            case 'vaciar':
                vaciarCarrito();
                break;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
        actualizarCarrito();
    }
}

function agregarAlCarrito($id_producto, $cantidad) {
    if ($id_producto > 0 && $cantidad > 0) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        }
        $_SESSION['mensaje'] = "✅ Producto agregado al carrito";
    }
}

function eliminarDelCarrito($id_producto) {
    if (isset($_SESSION['carrito'][$id_producto])) {
        unset($_SESSION['carrito'][$id_producto]);
        $_SESSION['mensaje'] = "✅ Producto eliminado del carrito";
    }
}

function vaciarCarrito() {
    $_SESSION['carrito'] = [];
    $_SESSION['mensaje'] = "✅ Carrito vaciado";
}

function actualizarCarrito() {
    if (isset($_POST['cantidad']) && is_array($_POST['cantidad'])) {
        foreach ($_POST['cantidad'] as $id => $cantidad) {
            $id = intval($id);
            $cantidad = intval($cantidad);
            
            if ($cantidad > 0) {
                $_SESSION['carrito'][$id] = $cantidad;
            } else {
                unset($_SESSION['carrito'][$id]);
            }
        }
        $_SESSION['mensaje'] = "✅ Carrito actualizado";
    }
}

// Obtener detalles de productos en el carrito
$productos_carrito = [];
$total = 0;
$total_items = 0;

if (!empty($_SESSION['carrito'])) {
    $ids = array_keys($_SESSION['carrito']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    try {
        $stmt = $conn->prepare("
            SELECT id, nombre, precio, stock, imagen, descripcion 
            FROM articulos 
            WHERE id IN ($placeholders) AND estado = 'activo'
        ");
        $stmt->execute($ids);
        $productos = $stmt->fetchAll();
        
        foreach ($productos as $producto) {
            $id = $producto['id'];
            $cantidad = $_SESSION['carrito'][$id];
            $subtotal = $producto['precio'] * $cantidad;
            
            $productos_carrito[] = [
                'id' => $id,
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
                'stock' => $producto['stock'],
                'imagen' => $producto['imagen'] ?? 'default.jpg',
                'descripcion' => $producto['descripcion']
            ];
            
            $total += $subtotal;
            $total_items += $cantidad;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al cargar productos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="bi bi-cart3"></i> Tu Carrito</h3>
                    </div>
                    
                    <?php if (empty($productos_carrito)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <h4 class="mt-3">Tu carrito está vacío</h4>
                            <p class="text-muted">Agrega productos para comenzar a comprar</p>
                            <a href="articulos.php" class="btn btn-success btn-lg">
                                <i class="bi bi-bag-plus"></i> Ver Productos
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="100">Producto</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th width="150">Cantidad</th>
                                            <th>Subtotal</th>
                                            <th width="100">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos_carrito as $producto): ?>
                                        <tr>
                                            <td>
                                                <img src="uploads/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                     class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                                <small class="text-muted"><?php echo substr($producto['descripcion'], 0, 50); ?>...</small>
                                            </td>
                                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                            <td>
                                                <form method="POST" class="d-flex">
                                                    <input type="hidden" name="actualizar" value="1">
                                                    <input type="number" 
                                                           name="cantidad[<?php echo $producto['id']; ?>]"
                                                           value="<?php echo $producto['cantidad']; ?>"
                                                           min="1" 
                                                           max="<?php echo min($producto['stock'], 99); ?>"
                                                           class="form-control form-control-sm"
                                                           style="width: 70px;">
                                                    <button type="submit" class="btn btn-sm btn-outline-success ms-2">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                </form>
                                                <small class="text-muted">Stock: <?php echo $producto['stock']; ?></small>
                                            </td>
                                            <td class="fw-bold">$<?php echo number_format($producto['subtotal'], 2); ?></td>
                                            <td>
                                                <a href="carrito.php?accion=eliminar&id=<?php echo $producto['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Eliminar este producto del carrito?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($productos_carrito)): ?>
                <div class="mt-3">
                    <a href="articulos.php" class="btn btn-outline-success">
                        <i class="bi bi-arrow-left"></i> Seguir Comprando
                    </a>
                    <a href="carrito.php?accion=vaciar" 
                       class="btn btn-outline-danger float-end"
                       onclick="return confirm('¿Vaciar todo el carrito?')">
                        <i class="bi bi-cart-x"></i> Vaciar Carrito
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($productos_carrito)): ?>
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-receipt"></i> Resumen del Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Productos (<?php echo $total_items; ?>):</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío:</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Impuestos (10%):</span>
                            <span>$<?php echo number_format($total * 0.10, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total * 1.10, 2); ?></span>
                        </div>
                        
                        <div class="mt-4">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <a href="checkout.php" class="btn btn-success btn-lg w-100 py-3">
                                    <i class="bi bi-lock-fill"></i> Proceder al Pago
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <p class="mb-2"><i class="bi bi-exclamation-triangle"></i> Debes iniciar sesión para finalizar la compra</p>
                                    <div class="d-grid gap-2">
                                        <a href="login.php?redirect=carrito.php" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                        </a>
                                        <a href="registro.php" class="btn btn-outline-primary">
                                            <i class="bi bi-person-plus"></i> Crear Cuenta
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Compra 100% segura
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mi Tienda Online</h5>
                    <p class="mb-0">Los mejores productos al mejor precio</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">
                        <i class="bi bi-whatsapp"></i> +1 234 567 8900<br>
                        <i class="bi bi-envelope"></i> info@mitienda.com
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-actualizar cantidad al cambiar
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</body>
</html>