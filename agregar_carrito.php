<?php
require_once 'config_session.php';
include_once('conexion.php');

// Verificar que el usuario esté logueado y sea cliente
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_articulo = intval($_POST['id_articulo'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    if ($id_articulo > 0) {
        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        
        // Agregar o actualizar producto en el carrito
        if (isset($_SESSION['carrito'][$id_articulo])) {
            $_SESSION['carrito'][$id_articulo] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_articulo] = $cantidad;
        }
        
        // Mensaje de éxito
        $_SESSION['mensaje'] = "Producto agregado al carrito correctamente";
        
        // Redirigir al carrito
        header('Location: carrito.php');
        exit();
    }
}

// Si no es POST o hay error, redirigir al index
header('Location: index.php');
exit();
?>