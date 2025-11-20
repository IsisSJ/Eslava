<?php
// limpiar_carrito.php - LIMPIAR CARRO COMPLETAMENTE
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Limpiar carrito
unset($_SESSION['carrito']);

// Redirigir al carrito
header('Location: carrito.php');
exit();
?>