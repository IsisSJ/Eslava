<?php
// enviar_ticket.php
session_start();
include_once("conexion.php");

// DEPURACIÓN: Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    $_SESSION['mensaje'] = "❌ Error: No hay productos en el carrito";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: carrito.php');
    exit();
}

// CONFIGURACIÓN DE PHPMailer
$phpmailer_path = __DIR__ . '/PHPMailer/src/';

// Verificar que los archivos existan
if (!file_exists($phpmailer_path . 'PHPMailer.php')) {
    $_SESSION['mensaje'] = "❌ Error: No se encuentra PHPMailer.php";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: confirmar_pedido.php');
    exit();
}

// Incluir PHPMailer
require $phpmailer_path . 'PHPMailer.php';
require $phpmailer_path . 'SMTP.php';
require $phpmailer_path . 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Obtener email del usuario
$usuario_email = '';
$nombre_destinatario = $_SESSION['usuario'];

$stmt = $conn->prepare("SELECT correo FROM usuarios WHERE nombre_usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    $usuario_email = $user['correo'];
} else {
    // Si no encuentra el email, usar email temporal de sesión o redirigir
    if (isset($_SESSION['email_temporal'])) {
        $usuario_email = $_SESSION['email_temporal'];
        $nombre_destinatario = $_SESSION['nombre_destinatario'] ?? $_SESSION['usuario'];
        unset($_SESSION['email_temporal']);
        unset($_SESSION['nombre_destinatario']);
    } else {
        $_SESSION['error_email'] = "No encontramos tu email registrado. Por favor ingrésalo:";
        header('Location: ingresar_email.php');
        exit();
    }
}

// CONFIGURACIÓN DE GMAIL - ¡EDITA ESTAS LÍNEAS!
$gmail_user = 'isiszenith@gmail.com';        // ← TU CORREO GMAIL
$gmail_password = 'qrpy vjll snkj zfle';        // ← CONTRASEÑA DE APLICACIÓN

// Validar configuración de Gmail
if (empty($gmail_user) || $gmail_user == 'tu_correo@gmail.com') {
    $_SESSION['mensaje'] = "❌ Error: Configura tu correo Gmail en el archivo enviar_ticket.php";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: confirmar_pedido.php');
    exit();
}

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    // CONFIGURACIÓN ALTERNATIVA PARA SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $gmail_user;
    $mail->Password = $gmail_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    // Configuraciones adicionales para problemas de conexión
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Timeout más largo
    $mail->Timeout = 30;
    
    // Debug
    $mail->SMTPDebug = 0;

    // Remitente y destinatario
    $mail->setFrom($gmail_user, 'Flores de Chinampa');
    $mail->addAddress($usuario_email, $_SESSION['usuario']);
    
    // Responder a
    $mail->addReplyTo($gmail_user, 'Flores de Chinampa');

    // Contenido del email
    $mail->isHTML(true);
    $mail->Subject = '✅ Tu comprobante de pedido - Flores de Chinampa';
    
    // Generar contenido del email
    $metodo_pago = $_GET['metodo'] ?? 'efectivo';
    $mail->Body = generarContenidoEmail($_SESSION['carrito'], $_SESSION['usuario'], $metodo_pago);
    $mail->AltBody = generarContenidoTexto($_SESSION['carrito'], $_SESSION['usuario'], $metodo_pago);

    // Enviar email
    if ($mail->send()) {
        $_SESSION['mensaje'] = "✅ ¡Ticket enviado correctamente! Revisa tu correo: " . $usuario_email;
        $_SESSION['tipo_mensaje'] = "success";
        $_SESSION['pedido_exitoso'] = [
            'pedido_id' => rand(1000, 9999),
            'total' => calcularTotal($_SESSION['carrito']),
            'metodo_pago' => $metodo_pago
        ];
        
        // Limpiar carrito después del envío exitoso
        unset($_SESSION['carrito']);
    } else {
        throw new Exception('No se pudo enviar el email');
    }
    
} catch (Exception $e) {
    $error_msg = "❌ Error al enviar email: " . $e->getMessage();
    
    // Guardar en log de errores
    error_log("Error PHPMailer: " . $e->getMessage());
    
    $_SESSION['mensaje'] = $error_msg;
    $_SESSION['tipo_mensaje'] = "error";
    
    // Pero aún así procesar el pedido
    $_SESSION['pedido_exitoso'] = [
        'pedido_id' => rand(1000, 9999),
        'total' => calcularTotal($_SESSION['carrito']),
        'metodo_pago' => $metodo_pago
    ];
}

header('Location: pedido_confirmado.php');
exit();

function calcularTotal($carrito) {
    $total = 0;
    foreach ($carrito as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total * 1.16; // Con IVA
}

function generarContenidoEmail($carrito, $cliente, $metodo_pago) {
    $total = 0;
    $productos_html = '';
    $numero_pedido = rand(1000, 9999);
    
    foreach ($carrito as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $total += $subtotal;
        
        $productos_html .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$item['nombre']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['cantidad']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>$" . number_format($item['precio'], 2) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>$" . number_format($subtotal, 2) . "</td>
        </tr>";
    }
    
    $iva = $total * 0.16;
    $total_con_iva = $total + $iva;
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { 
                font-family: 'Arial', sans-serif; 
                line-height: 1.6; 
                color: #333; 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white; 
                padding: 30px; 
                text-align: center; 
            }
            .content { 
                padding: 30px; 
            }
            .info-box { 
                background: #f8f9fa; 
                padding: 20px; 
                border-radius: 8px; 
                margin: 20px 0;
                border-left: 4px solid #28a745;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .table th {
                background: #28a745;
                color: white;
                padding: 12px;
                text-align: left;
            }
            .total-box {
                background: #e8f5e8;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .footer {
                text-align: center;
                padding: 20px;
                background: #343a40;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌺 Flores de Chinampa</h1>
                <p>¡Gracias por tu compra! 🌟</p>
                <p><strong>Pedido #$numero_pedido</strong></p>
            </div>
            
            <div class='content'>
                <div class='info-box'>
                    <h3 style='margin-top: 0; color: #28a745;'>📋 Información del Pedido</h3>
                    <p><strong>👤 Cliente:</strong> $cliente</p>
                    <p><strong>📅 Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                    <p><strong>💳 Método de Pago:</strong> " . ($metodo_pago == 'tarjeta' ? 'Tarjeta de Crédito/Débito' : 'Efectivo') . "</p>
                </div>
                
                <h3 style='color: #28a745;'>🛍️ Productos Comprados</h3>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        $productos_html
                    </tbody>
                </table>
                
                <div class='total-box'>
                    <h3 style='margin-top: 0; color: #28a745;'>💰 Resumen de Pago</h3>
                    <div style='display: flex; justify-content: space-between; margin: 10px 0;'>
                        <span>Subtotal:</span>
                        <span><strong>$" . number_format($total, 2) . "</strong></span>
                    </div>
                    <div style='display: flex; justify-content: space-between; margin: 10px 0;'>
                        <span>IVA (16%):</span>
                        <span><strong>$" . number_format($iva, 2) . "</strong></span>
                    </div>
                    <hr style='border: 1px solid #28a745;'>
                    <div style='display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold; margin: 15px 0;'>
                        <span>TOTAL:</span>
                        <span style='color: #28a745;'>$" . number_format($total_con_iva, 2) . "</span>
                    </div>
                </div>
                
                <div class='info-box'>
                    <h4 style='margin-top: 0; color: #28a745;'>🚚 Información de Entrega</h4>
                    <p>⏰ <strong>Tiempo estimado:</strong> 2-3 días hábiles</p>
                    <p>📍 <strong>Zona de entrega:</strong> CDMX y área metropolitana</p>
                    <p>📞 <strong>Para dudas:</strong> 55-1234-5678</p>
                    <p>📧 <strong>Email:</strong> hola@floresdechinampa.com</p>
                </div>
            </div>
            
            <div class='footer'>
                <p>¡Esperamos verte pronto! 🌸</p>
                <p>Flores de Chinampa - Tu tienda de confianza</p>
            </div>
        </div>
    </body>
    </html>";
}

function generarContenidoTexto($carrito, $cliente, $metodo_pago) {
    $total = 0;
    $productos_texto = "";
    $numero_pedido = rand(1000, 9999);
    
    foreach ($carrito as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $total += $subtotal;
        $productos_texto .= "• {$item['nombre']} x{$item['cantidad']} - $" . number_format($subtotal, 2) . "\n";
    }
    
    $iva = $total * 0.16;
    $total_con_iva = $total + $iva;
    
    return "FLORES DE CHINAMPA\n" .
           "================\n\n" .
           "¡GRACIAS POR TU COMPRA! 🌟\n\n" .
           "INFORMACIÓN DEL PEDIDO:\n" .
           "Pedido: #$numero_pedido\n" .
           "Cliente: $cliente\n" .
           "Fecha: " . date('d/m/Y H:i:s') . "\n" .
           "Método de Pago: " . ($metodo_pago == 'tarjeta' ? 'Tarjeta' : 'Efectivo') . "\n\n" .
           "PRODUCTOS COMPRADOS:\n" .
           "$productos_texto\n" .
           "RESUMEN DE PAGO:\n" .
           "Subtotal: $" . number_format($total, 2) . "\n" .
           "IVA (16%): $" . number_format($iva, 2) . "\n" .
           "TOTAL: $" . number_format($total_con_iva, 2) . "\n\n" .
           "INFORMACIÓN DE ENTREGA:\n" .
           "Tiempo estimado: 2-3 días hábiles\n" .
           "Zona: CDMX y área metropolitana\n" .
           "Teléfono: 55-1234-5678\n\n" .
           "¡Esperamos verte pronto! 🌸\n" .
           "Flores de Chinampa";
}
?>