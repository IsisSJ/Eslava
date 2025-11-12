<?php
session_start();
include_once("conexion.php");

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$producto_id = $_GET['id'] ?? 0;

if ($producto_id) {
    // Obtener datos del producto
    $sql = "SELECT nombre FROM articulos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    
    if ($producto) {
        // ✅ CAMBIO AQUÍ - USA TU DOMINIO NUEVO
        $nueva_url = "https://floreria.42web.io/ver_producto.php?id=" . $producto_id;
        
        // Generar nuevo código QR
        $nuevo_qr_code = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($nueva_url);
        
        // Actualizar en la base de datos
        $update_sql = "UPDATE articulos SET qr_code = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $nuevo_qr_code, $producto_id);
        
        if ($update_stmt->execute()) {
            echo "<h1>✅ QR Regenerado Correctamente</h1>";
            echo "<p>Producto: <strong>{$producto['nombre']}</strong></p>";
            echo "<p>Nueva URL: <code>{$nueva_url}</code></p>";
            echo "<img src='{$nuevo_qr_code}' alt='Nuevo QR'>";
            echo "<p><a href='verificar_qr.php'>← Volver al verificador</a></p>";
        } else {
            echo "<h1>❌ Error al regenerar QR</h1>";
        }
    } else {
        echo "<h1>❌ Producto no encontrado</h1>";
    }
} else {
    echo "<h1>❌ ID no especificado</h1>";
}
?>