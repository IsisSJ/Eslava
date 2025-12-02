<?php
// email_config.php
function enviarEmail($destinatario, $asunto, $mensaje, $tipo = 'html') {
    $headers = "MIME-Version: 1.0\r\n";
    
    if ($tipo === 'html') {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mensaje = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🌺 Flores de Chinampa</h2>
                </div>
                <div class="content">
                    ' . $mensaje . '
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Flores de Chinampa. Todos los derechos reservados.</p>
                    <p>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>';
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    }
    
    $headers .= "From: Flores de Chinampa <noreply@floresdechinampa.com>\r\n";
    $headers .= "Reply-To: contacto@floresdechinampa.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($destinatario, $asunto, $mensaje, $headers);
}

// Funciones específicas de notificación
function notificarRegistro($usuario, $email) {
    $asunto = "🎉 ¡Bienvenido a Flores de Chinampa!";
    $mensaje = "
        <h3>¡Hola " . htmlspecialchars($usuario) . "!</h3>
        <p>Te damos la bienvenida a <strong>Flores de Chinampa</strong>.</p>
        <p>Tu cuenta ha sido creada exitosamente. Ahora puedes:</p>
        <ul>
            <li>Explorar nuestro catálogo de flores</li>
            <li>Realizar compras seguras</li>
            <li>Seguir el estado de tus pedidos</li>
            <li>Dejar reseñas de tus compras</li>
        </ul>
        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        <p>¡Gracias por unirte a nuestra comunidad!</p>
        <br>
        <a href='https://eslava-3.onrender.com' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
            Ir a la Tienda
        </a>
    ";
    
    return enviarEmail($email, $asunto, $mensaje, 'html');
}

function notificarPedido($usuario, $email, $pedido_id, $total) {
    $asunto = "✅ Pedido #$pedido_id Confirmado";
    $mensaje = "
        <h3>¡Hola " . htmlspecialchars($usuario) . "!</h3>
        <p>Tu pedido <strong>#$pedido_id</strong> ha sido confirmado.</p>
        <div style='background: white; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; margin: 20px 0;'>
            <h4 style='color: #28a745;'>Resumen del Pedido</h4>
            <p><strong>Número de Pedido:</strong> #$pedido_id</p>
            <p><strong>Total:</strong> $" . number_format($total, 2) . "</p>
            <p><strong>Estado:</strong> <span style='color: #ffc107; font-weight: bold;'>Pendiente</span></p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</p>
        </div>
        <p>Puedes seguir el estado de tu pedido en tu cuenta.</p>
        <p>Te notificaremos cuando tu pedido sea enviado.</p>
        <br>
        <a href='https://eslava-3.onrender.com/mis_pedidos.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
            Ver Mis Pedidos
        </a>
    ";
    
    return enviarEmail($email, $asunto, $mensaje, 'html');
}

function notificarEstadoPedido($usuario, $email, $pedido_id, $estado_nuevo) {
    $estados = [
        'procesando' => ['color' => '#17a2b8', 'icon' => '🔄', 'text' => 'en procesamiento'],
        'enviado' => ['color' => '#007bff', 'icon' => '🚚', 'text' => 'enviado'],
        'entregado' => ['color' => '#28a745', 'icon' => '✅', 'text' => 'entregado'],
        'cancelado' => ['color' => '#dc3545', 'icon' => '❌', 'text' => 'cancelado']
    ];
    
    $estado_info = $estados[$estado_nuevo] ?? ['color' => '#6c757d', 'icon' => '📋', 'text' => $estado_nuevo];
    
    $asunto = "{$estado_info['icon']} Estado Actualizado - Pedido #$pedido_id";
    $mensaje = "
        <h3>¡Hola " . htmlspecialchars($usuario) . "!</h3>
        <p>El estado de tu pedido <strong>#$pedido_id</strong> ha sido actualizado.</p>
        <div style='background: white; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; margin: 20px 0; text-align: center;'>
            <div style='font-size: 48px; margin-bottom: 10px;'>{$estado_info['icon']}</div>
            <h4 style='color: {$estado_info['color']};'>Pedido {$estado_info['text']}</h4>
            <p>Estado: <strong style='color: {$estado_info['color']};'>" . ucfirst($estado_nuevo) . "</strong></p>
            <p>Fecha: " . date('d/m/Y H:i') . "</p>
        </div>
        <p>Para más detalles, revisa tu pedido en nuestra plataforma.</p>
        <br>
        <a href='https://eslava-3.onrender.com/detalle_pedido.php?id=$pedido_id' style='background: {$estado_info['color']}; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
            Ver Detalle del Pedido
        </a>
    ";
    
    return enviarEmail($email, $asunto, $mensaje, 'html');
}
?>