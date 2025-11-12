<?php
session_start();
include_once("conexion.php");

if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito']) || !isset($_POST['email'])) {
    header('Location: carrito.php');
    exit();
}

$email_destino = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$nombre_destinatario = trim($_POST['nombre_destinatario'] ?? '');

if (!$email_destino) {
    $_SESSION['error_email'] = "❌ Por favor ingresa un email válido";
    header('Location: ingresar_email.php');
    exit();
}

// Guardar el email y nombre en sesión
$_SESSION['email_temporal'] = $email_destino;
$_SESSION['nombre_destinatario'] = $nombre_destinatario ?: $_SESSION['usuario'];

// Redirigir a enviar_ticket.php
$metodo_pago = $_GET['metodo'] ?? 'efectivo';
header("Location: enviar_ticket.php?metodo=$metodo_pago");
exit();
?>