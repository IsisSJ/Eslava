<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['carrito'])) {
    header("Location: articulos.php");
    exit();
}

include_once("conexion.php");

// Procesar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Crear pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, ?, 'pendiente')");
        $total = 0;
        
        // En checkout.php, después de crear el pedido:
        include_once('email_config.php');
        notificarPedido($_SESSION['usuario_nombre'], $_SESSION['usuario_email'], $pedido_id, $total);

        // Calcular total
        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            $stmt_producto = $conn->prepare("SELECT precio FROM articulos WHERE id = ?");
            $stmt_producto->execute([$producto_id]);
            $producto = $stmt_producto->fetch();
            $total += $producto['precio'] * $cantidad;
        }
        
        $stmt->execute([$_SESSION['usuario_id'], $total]);
        $pedido_id = $conn->lastInsertId();
        
        // 2. Agregar items del pedido y actualizar stock
        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            // Obtener producto
            $stmt_producto = $conn->prepare("SELECT precio, stock FROM articulos WHERE id = ?");
            $stmt_producto->execute([$producto_id]);
            $producto = $stmt_producto->fetch();
            
            // Agregar item
            $stmt_item = $conn->prepare("INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$pedido_id, $producto_id, $cantidad, $producto['precio']]);
            
            // Actualizar stock
            $nuevo_stock = $producto['stock'] - $cantidad;
            $stmt_update = $conn->prepare("UPDATE articulos SET stock = ? WHERE id = ?");
            $stmt_update->execute([$nuevo_stock, $producto_id]);
        }
        
        // 3. Vaciar carrito y mostrar confirmación
        $_SESSION['carrito'] = [];
        $_SESSION['mensaje'] = "✅ Pedido #$pedido_id realizado con éxito. Total: $" . number_format($total, 2);
        header("Location: mis_pedidos.php");
        exit();
        
    } catch (PDOException $e) {
        $error = "Error al procesar la compra: " . $e->getMessage();
    }
}

// Obtener resumen del carrito
$productos = [];
$total = 0;

$ids = array_keys($_SESSION['carrito']);
if (!empty($ids)) {
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, nombre, precio FROM articulos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    
    while ($producto = $stmt->fetch()) {
        $cantidad = $_SESSION['carrito'][$producto['id']];
        $productos[] = [
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $producto['precio'] * $cantidad
        ];
        $total += $producto['precio'] * $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2>✅ Finalizar Compra</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Resumen del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo $producto['cantidad']; ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>$<?php echo number_format($producto['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Total de la Compra</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="text-success">$<?php echo number_format($total, 2); ?></h4>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Método de Pago</label>
                                <select class="form-select" name="metodo_pago" required>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Dirección de Envío</label>
                                <textarea class="form-control" name="direccion" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                💳 Confirmar y Pagar
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="carrito.php" class="btn btn-secondary">← Volver al Carrito</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>