<?php
// procesar_pedido.php - ARCHIVO BÁSICO
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

include_once('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $direccion_envio = $_POST['direccion_envio'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_usuario = $_SESSION['user_id'];
    
    try {
        // 1. Crear el pedido
        $sql_pedido = "INSERT INTO pedidos (id_usuario, fecha_pedido, estado, metodo_pago, direccion_envio, telefono) 
                      VALUES (?, NOW(), 'pendiente', ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->execute([$id_usuario, $metodo_pago, $direccion_envio, $telefono]);
        $id_pedido = $conn->lastInsertId();
        
        // 2. Agregar los detalles del pedido
        foreach ($_SESSION['carrito'] as $id_articulo => $cantidad) {
            // Obtener precio actual del producto
            $sql_producto = "SELECT precio FROM articulos WHERE id = ?";
            $stmt_producto = $conn->prepare($sql_producto);
            $stmt_producto->execute([$id_articulo]);
            $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
            
            if ($producto) {
                $sql_detalle = "INSERT INTO detalles_pedido (id_pedido, id_articulo, cantidad, precio_unitario) 
                               VALUES (?, ?, ?, ?)";
                $stmt_detalle = $conn->prepare($sql_detalle);
                $stmt_detalle->execute([$id_pedido, $id_articulo, $cantidad, $producto['precio']]);
                
                // Actualizar stock
                $sql_stock = "UPDATE articulos SET stock = stock - ? WHERE id = ?";
                $stmt_stock = $conn->prepare($sql_stock);
                $stmt_stock->execute([$cantidad, $id_articulo]);
            }
        }
        
        // 3. Limpiar carrito
        unset($_SESSION['carrito']);
        
        // 4. Redirigir a confirmación
        $_SESSION['pedido_exitoso'] = $id_pedido;
        header('Location: pedido_confirmado.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al procesar el pedido: " . $e->getMessage();
        header('Location: confirmar_pedido.php');
        exit();
    }
} else {
    header('Location: carrito.php');
    exit();
}
?>