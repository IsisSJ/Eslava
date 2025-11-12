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
    header('Location: carrito.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $direccion = $_POST['direccion'];
    $metodo_pago = $_POST['metodo_pago'];
    $notas = $_POST['notas'] ?? '';
    $usuario_id = $_SESSION['user_id'];
    $total = 0;

    // Calcular total
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    $total_con_iva = $total * 1.16;

    // Insertar pedido en la base de datos
    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, direccion_entrega, metodo_pago, notas, total, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("isssd", $usuario_id, $direccion, $metodo_pago, $notas, $total_con_iva);
    
    if ($stmt->execute()) {
        $pedido_id = $stmt->insert_id;
        
        // Insertar detalles del pedido
        foreach ($_SESSION['carrito'] as $item) {
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt_detalle->bind_param("iiid", $pedido_id, $item['id'], $item['cantidad'], $item['precio']);
            $stmt_detalle->execute();
            
            // Actualizar stock
            $conn->query("UPDATE articulos SET stock = stock - {$item['cantidad']} WHERE id = {$item['id']}");
        }
        
        // Vaciar carrito
        $_SESSION['carrito'] = [];
        $_SESSION['mensaje_pedido'] = "¡Pedido realizado con éxito! Número de pedido: #" . $pedido_id;
        header("Location: mis_pedidos.php");
        exit();
    } else {
        $_SESSION['error_pedido'] = "Error al procesar el pedido. Intente nuevamente.";
        header("Location: carrito.php");
        exit();
    }
}
?>