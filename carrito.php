<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Acciones del carrito
if (isset($_GET['accion'])) {
    $id_producto = intval($_GET['id'] ?? 0);
    
    switch ($_GET['accion']) {
        case 'agregar':
            $cantidad = intval($_GET['cantidad'] ?? 1);
            if (isset($_SESSION['carrito'][$id_producto])) {
                $_SESSION['carrito'][$id_producto] += $cantidad;
            } else {
                $_SESSION['carrito'][$id_producto] = $cantidad;
            }
            $_SESSION['mensaje'] = "✅ Producto agregado al carrito";
            break;
            
        case 'eliminar':
            if (isset($_SESSION['carrito'][$id_producto])) {
                unset($_SESSION['carrito'][$id_producto]);
            }
            break;
            
        case 'actualizar':
            $cantidad = intval($_POST['cantidad'][$id_producto] ?? 0);
            if ($cantidad > 0) {
                $_SESSION['carrito'][$id_producto] = $cantidad;
            } else {
                unset($_SESSION['carrito'][$id_producto]);
            }
            break;
            
        case 'vaciar':
            $_SESSION['carrito'] = [];
            break;
    }
    
    header("Location: carrito.php");
    exit();
}

include_once("conexion.php");

// Obtener detalles de productos en el carrito
$productos_carrito = [];
$total = 0;

if (!empty($_SESSION['carrito'])) {
    $ids = array_keys($_SESSION['carrito']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $conn->prepare("SELECT id, nombre, precio, stock FROM articulos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $productos = $stmt->fetchAll();
    
    foreach ($productos as $producto) {
        $cantidad = $_SESSION['carrito'][$producto['id']];
        $subtotal = $producto['precio'] * $cantidad;
        $total += $subtotal;
        
        $productos_carrito[] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
            'stock' => $producto['stock']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2>🛒 Carrito de Compras</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($productos_carrito)): ?>
            <div class="alert alert-info">
                <h5>Tu carrito está vacío</h5>
                <p>Agrega algunos productos desde nuestro catálogo.</p>
                <a href="articulos.php" class="btn btn-primary">Ver Catálogo</a>
            </div>
        <?php else: ?>
            <form method="POST" action="carrito.php?accion=actualizar">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio Unitario</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_carrito as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <input type="number" 
                                           name="cantidad[<?php echo $producto['id']; ?>]" 
                                           value="<?php echo $producto['cantidad']; ?>" 
                                           min="1" 
                                           max="<?php echo $producto['stock']; ?>"
                                           class="form-control" 
                                           style="width: 80px;">
                                </td>
                                <td>$<?php echo number_format($producto['subtotal'], 2); ?></td>
                                <td>
                                    <a href="carrito.php?accion=eliminar&id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('¿Eliminar producto del carrito?')">
                                        ❌ Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4>Total: $<?php echo number_format($total, 2); ?></h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-warning">🔄 Actualizar Cantidades</button>
                        <a href="carrito.php?accion=vaciar" class="btn btn-danger"
                           onclick="return confirm('¿Vaciar todo el carrito?')">
                            🗑️ Vaciar Carrito
                        </a>
                        <a href="checkout.php" class="btn btn-success">✅ Proceder al Pago</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>