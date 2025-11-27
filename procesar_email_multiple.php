<?php
session_start();
include_once("conexion.php");

// VERIFICAR SESIÓN DEL LOGIN (modificar esta parte)
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito']) || !isset($_POST['emails'])) {
    header('Location: carrito.php');
    exit();
}

$emails = $_POST['emails'];
$nombres = $_POST['nombres'] ?? [];
$mensaje_personal = $_POST['mensaje_personal'] ?? '';

// Validar y limpiar emails
$destinatarios_validos = [];
foreach ($emails as $index => $email) {
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $nombre = $nombres[$index] ?? $_SESSION['usuario_nombre']; // ← Cambiado aquí
        $destinatarios_validos[$email] = $nombre;
    }
}

if (empty($destinatarios_validos)) {
    $_SESSION['error_email'] = "❌ Por favor ingresa al menos un email válido";
    header('Location: ingresar_email.php');
    exit();
}

// Guardar en sesión
$_SESSION['destinatarios_multiples'] = $destinatarios_validos;
$_SESSION['mensaje_personal'] = $mensaje_personal;

// Redirigir a enviar_ticket_multiple.php
$metodo_pago = $_GET['metodo'] ?? 'efectivo';
header("Location: enviar_ticket_multiple.php?metodo=$metodo_pago");
exit();
?>