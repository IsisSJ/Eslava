<?php
// agregar_carrito.php - VERIFICAR QUE EXISTA Y FUNCIONE
session_start();
include_once('conexion.php');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_articulo = $_POST['id_articulo'] ?? '';
    $cantidad = $_POST['cantidad'] ?? 1;
    
    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    // Agregar producto al carrito
    if (isset($_SESSION['carrito'][$id_articulo])) {
        $_SESSION['carrito'][$id_articulo] += $cantidad;
    } else {
        $_SESSION['carrito'][$id_articulo] = $cantidad;
    }
    
    // Redirigir al carrito
    header('Location: carrito.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>