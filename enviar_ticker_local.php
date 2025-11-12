<?php
// enviar_ticket_local.php - Versión que usa el servidor de correo local
session_start();
include_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}

// Obtener email del usuario
$usuario_email = '';
$stmt = $conn->prepare("SELECT correo FROM usuarios WHERE nombre_usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    $usuario_email = $user['correo'];
} else {
    if (isset($_SESSION['email_temporal'])) {
        $usuario_email = $_SESSION['email_temporal'];
        unset($_SESSION['email_temporal']);
    } else {
        $_SESSION['error_email'] = "No encontramos tu email registrado. Por favor ingrésalo:";
        header('Location: ingresar_email.php');
        exit();
    }
}

// Generar contenido del email
$metodo_pago = $_GET['metodo'] ?? 'efectivo';
$asunto = "✅ Tu comprobante de pedido - Flores de Chinampa";
$mensaje_html = generarContenidoEmail($_SESSION['carrito'], $_SESSION['usuario'], $metodo_pago);
$mensaje_texto = generarContenidoTexto($_SESSION['carrito'], $_SESSION['usuario'], $metodo_pago);

// Configurar cabeceras para email HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Flores de Chinampa <no-reply@floresdechinampa.com>" . "\r\n";
$headers .= "Reply-To: no-reply@floresdechinampa.com" . "\r\n";

// Intentar enviar el email usando la función mail() nativa
if (mail($usuario_email, $asunto, $mensaje_html, $headers)) {
    $_SESSION['mensaje'] = "✅ ¡Ticket enviado correctamente a: " . $usuario_email;
    $_SESSION['tipo_mensaje'] = "success";
} else {
    $_SESSION['mensaje'] = "⚠️ El pedido fue procesado, pero no se pudo enviar el email. Revisa tu carpeta de pedidos.";
    $_SESSION['tipo_mensaje'] = "warning";
}

// Procesar pedido exitoso
$_SESSION['pedido_exitoso'] = [
    'pedido_id' => rand(1000, 9999),
    'total' => calcularTotal($_SESSION['carrito']),
    'metodo_pago' => $metodo_pago
];

// Limpiar carrito
unset($_SESSION['carrito']);

header('Location: pedido_confirmado.php');
exit();

// ... (las funciones calcularTotal, generarContenidoEmail y generarContenidoTexto son las mismas)
?>