<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $producto_id = intval($_GET['id']);
    
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['id'] == $producto_id) {
            unset($_SESSION['carrito'][$key]);
            break;
        }
    }
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
}

header('Location: carrito.php');
exit();
?>