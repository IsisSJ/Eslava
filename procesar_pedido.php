<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté autenticado como cliente
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

// Verificar que el carrito no esté vacío
if (empty($_SESSION['carrito'])) {
    $_SESSION['error'] = "El carrito está vacío";
    header('Location: carrito.php');
    exit();
}

// Verificar stock antes de procesar
foreach ($_SESSION['carrito'] as $item) {
    $stmt = $conn->prepare("SELECT stock, nombre FROM articulos WHERE id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    
    if (!$producto || $producto['stock'] < $item['cantidad']) {
        $_SESSION['error'] = "No hay suficiente stock para: " . $producto['nombre'];
        header('Location: carrito.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $direccion = trim($_POST['direccion']);
    $metodo_pago = $_POST['metodo_pago'];
    $notas = trim($_POST['notas'] ?? '');
    $usuario_id = $_SESSION['user_id'];
    
    // Validaciones básicas
    if (empty($direccion) || empty($metodo_pago)) {
        $_SESSION['error'] = "Por favor completa todos los campos requeridos";
        header('Location: carrito.php');
        exit();
    }

    // Calcular total
    $subtotal = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar pedido en la base de datos
        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, direccion_entrega, metodo_pago, notas, subtotal, iva, total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')");
        $stmt->bind_param("isssddd", $usuario_id, $direccion, $metodo_pago, $notas, $subtotal, $iva, $total);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear el pedido: " . $stmt->error);
        }
        
        $pedido_id = $conn->insert_id;
        
        // Insertar detalles del pedido y actualizar stock
        foreach ($_SESSION['carrito'] as $item) {
            // Insertar detalle del pedido
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $subtotal_item = $item['precio'] * $item['cantidad'];
            $stmt_detalle->bind_param("iiidd", $pedido_id, $item['id'], $item['cantidad'], $item['precio'], $subtotal_item);
            
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al agregar detalle del pedido: " . $stmt_detalle->error);
            }
            
            // Actualizar stock
            $stmt_stock = $conn->prepare("UPDATE articulos SET stock = stock - ? WHERE id = ?");
            $stmt_stock->bind_param("ii", $item['cantidad'], $item['id']);
            
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al actualizar stock: " . $stmt_stock->error);
            }
            
            // Verificar que el stock no sea negativo
            $stmt_verify = $conn->prepare("SELECT stock FROM articulos WHERE id = ?");
            $stmt_verify->bind_param("i", $item['id']);
            $stmt_verify->execute();
            $result_verify = $stmt_verify->get_result();
            $stock_actual = $result_verify->fetch_assoc()['stock'];
            
            if ($stock_actual < 0) {
                throw new Exception("Stock insuficiente para: " . $item['nombre']);
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        // Vaciar carrito y guardar mensaje de éxito
        $_SESSION['carrito'] = [];
        $_SESSION['pedido_exitoso'] = [
            'pedido_id' => $pedido_id,
            'total' => $total,
            'metodo_pago' => $metodo_pago
        ];
        
        header("Location: confirmacion_pedido.php");
        exit();
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $_SESSION['error'] = "Error al procesar el pedido: " . $e->getMessage();
        header("Location: carrito.php");
        exit();
    }
} else {
    // Si no es POST, redirigir al carrito
    header('Location: carrito.php');
    exit();
}
?>