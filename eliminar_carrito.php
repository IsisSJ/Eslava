<?php
// eliminar_carrito.php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

$id_articulo = $_GET['id'] ?? 0;

if ($id_articulo > 0 && isset($_SESSION['carrito'][$id_articulo])) {
    // Si es para reducir cantidad
    if (isset($_GET['reducir']) && $_SESSION['carrito'][$id_articulo] > 1) {
        $_SESSION['carrito'][$id_articulo]--;
    } else {
        // Eliminar completamente
        unset($_SESSION['carrito'][$id_articulo]);
    }
    
    $_SESSION['mensaje'] = "Producto actualizado en el carrito";
}

header('Location: carrito.php');
exit();
?>