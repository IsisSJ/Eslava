<?php
// confirmar_pedido.php - VERSIÓN COMPLETAMENTE CORREGIDA
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

include_once('conexion.php');

// Validar y limpiar carrito ANTES de cualquier procesamiento
$carrito_valido = [];
$total_pedido = 0;
$items_validos = 0;

if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    // Crear una copia para iterar
    $carrito_temporal = $_SESSION['carrito'];
    
    foreach ($carrito_temporal as $id_articulo => $cantidad) {
        // Verificar que el ID sea válido y el producto exista
        if (!is_numeric($id_articulo) || $id_articulo <= 0) {
            continue; // Saltar IDs inválidos
        }
        
        // Verificar que el producto existe y tiene stock suficiente
        $sql = "SELECT id, nombre, precio, stock FROM articulos WHERE id = ? AND stock >= ? AND activo = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_articulo, $cantidad]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto && is_array($producto)) {
            // Producto válido, agregar al carrito validado
            $carrito_valido[$id_articulo] = [
                'cantidad' => intval($cantidad),
                'nombre' => $producto['nombre'],
                'precio' => floatval($producto['precio']),
                'stock' => intval($producto['stock']),
                'subtotal' => floatval($producto['precio']) * intval($cantidad)
            ];
            $total_pedido += $carrito_valido[$id_articulo]['subtotal'];
            $items_validos++;
        } else {
            // Producto no existe o sin stock, eliminarlo del carrito
            unset($_SESSION['carrito'][$id_articulo]);
        }
    }
}

// ACTUALIZAR la sesión del carrito con solo productos válidos
$_SESSION['carrito_valido'] = $carrito_valido;

// Si no hay productos válidos después de la limpieza, redirigir
if ($items_validos === 0) {
    unset($_SESSION['carrito']); // Limpiar carrito vacío
    $_SESSION['error'] = "No hay productos válidos en tu carrito. Los productos pueden estar agotados o no disponibles.";
    header('Location: carrito.php');
    exit();
}

// Calcular impuestos
$iva = $total_pedido * 0.16;
$total_con_iva = $total_pedido + $iva;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>✅ Confirmar Pedido - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .confirm-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .order-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .product-item {
            border-left: 4px solid #28a745;
            background: #f8fff9;
        }
        .summary-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
                <a href="carrito.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i>Volver al Carrito
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="confirm-container">
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header order-header text-center">
                    <h3 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Confirmar Pedido</h3>
                </div>
                
                <div class="card-body p-4">
                    <!-- Resumen del Pedido -->
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3"><i class="fas fa-boxes me-2"></i>Productos en tu pedido (<?php echo $items_validos; ?>):</h5>
                            
                            <?php if ($items_validos > 0): ?>
                                <?php foreach ($carrito_valido as $id_articulo => $item): ?>
                                    <?php if (is_array($item) && isset($item['nombre'])): ?>
                                        <div class="card product-item mb-3">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                                        <small class="text-muted">Código: #<?php echo $id_articulo; ?></small>
                                                        <br>
                                                        <small class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Disponible (<?php echo $item['stock']; ?> en stock)
                                                        </small>
                                                    </div>
                                                    <div class="col-md-2 text-center">
                                                        <strong>Cantidad:</strong>
                                                        <div class="fs-5"><?php echo $item['cantidad']; ?></div>
                                                    </div>
                                                    <div class="col-md-2 text-center">
                                                        <strong>Precio:</strong>
                                                        <div class="text-success">$<?php echo number_format($item['precio'], 2); ?></div>
                                                    </div>
                                                    <div class="col-md-2 text-center">
                                                        <strong>Subtotal:</strong>
                                                        <div class="text-success fw-bold">$<?php echo number_format($item['subtotal'], 2); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No hay productos válidos en tu carrito.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Resumen de Pago -->
                        <div class="col-md-4">
                            <div class="summary-card p-3 sticky-top" style="top: 20px;">
                                <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal (<?php echo $items_validos; ?> productos):</span>
                                    <span>$<?php echo number_format($total_pedido, 2); ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>IVA (16%):</span>
                                    <span>$<?php echo number_format($iva, 2); ?></span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-success fs-5">$<?php echo number_format($total_con_iva, 2); ?></strong>
                                </div>
                                
                                <!-- Formulario de Confirmación -->
                                <?php if ($items_validos > 0): ?>
                                    <form action="procesar_pedido.php" method="POST">
                                        <input type="hidden" name="total_pedido" value="<?php echo $total_con_iva; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-credit-card me-2"></i>Método de Pago:</label>
                                            <select class="form-select" name="metodo_pago" required>
                                                <option value="efectivo">💵 Pago en Efectivo</option>
                                                <option value="tarjeta">💳 Tarjeta de Crédito/Débito</option>
                                                <option value="transferencia">🏦 Transferencia Bancaria</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Dirección de Envío:</label>
                                            <textarea class="form-control" name="direccion_envio" rows="3" placeholder="Ingresa tu dirección completa..." required></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-phone me-2"></i>Teléfono de Contacto:</label>
                                            <input type="tel" class="form-control" name="telefono" placeholder="Tu número de teléfono" required>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="terminos" required>
                                            <label class="form-check-label" for="terminos">
                                                Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                                            </label>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-check-circle me-2"></i>Confirmar y Realizar Pedido
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No puedes proceder sin productos válidos.
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="carrito.php" class="text-decoration-none">
                                        <i class="fas fa-edit me-1"></i>Modificar Carrito
                                    </a>
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