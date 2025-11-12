<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

$_SESSION['carrito'] = [];
header('Location: carrito.php');
exit();
?>