<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté autenticado como cliente
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión como cliente']);
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_GET['id'])) {
    $producto_id = intval($_GET['id']);
    
    // Obtener información del producto
    $stmt = $conn->prepare("SELECT * FROM articulos WHERE id = ? AND stock > 0");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($producto = $result->fetch_assoc()) {
        // Verificar stock disponible
        $cantidad_en_carrito = 0;
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['id'] == $producto_id) {
                $cantidad_en_carrito = $item['cantidad'];
                break;
            }
        }
        
        if (($cantidad_en_carrito + 1) <= $producto['stock']) {
            // Verificar si el producto ya está en el carrito
            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id'] == $producto_id) {
                    $item['cantidad'] += 1;
                    $encontrado = true;
                    break;
                }
            }
            
            // Si no está, agregarlo
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'cantidad' => 1,
                    'imagen' => $producto['imagen'],
                    'stock' => $producto['stock']
                ];
            }
            
            // Calcular total de items en carrito
            $total_items = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $total_items += $item['cantidad'];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => $producto['nombre'] . ' agregado al carrito',
                'carrito_count' => $total_items
            ]);
            
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Producto no disponible']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de producto no especificado']);
}
?>