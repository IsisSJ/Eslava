<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Descarga PHPMailer via Composer

class Mailer {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Configuración del servidor
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'tu_correo@gmail.com'; // Tu correo Gmail
        $this->mail->Password = 'tu_contraseña_app'; // Contraseña de aplicación
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        // Configuración del remitente
        $this->mail->setFrom('tu_correo@gmail.com', 'Flores de Chinampa');
        $this->mail->isHTML(true);
    }
    
    public function enviarTicket($destinatario, $nombre_cliente, $pedido_data, $pdf_path = null) {
        try {
            $this->mail->addAddress($destinatario, $nombre_cliente);
            $this->mail->Subject = 'Tu comprobante de pedido - Flores de Chinampa';
            
            // Cuerpo del email en HTML
            $this->mail->Body = $this->crearCuerpoEmail($pedido_data);
            $this->mail->AltBody = $this->crearCuerpoTexto($pedido_data);
            
            // Adjuntar PDF si existe
            if ($pdf_path && file_exists($pdf_path)) {
                $this->mail->addAttachment($pdf_path, 'comprobante_pedido.pdf');
            }
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    private function crearCuerpoEmail($pedido_data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .producto { border-bottom: 1px solid #ddd; padding: 10px 0; }
                .total { background: #f8f9fa; padding: 15px; font-weight: bold; }
                .footer { background: #343a40; color: white; padding: 15px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🌺 Flores de Chinampa</h1>
                <p>¡Gracias por tu compra!</p>
            </div>
            
            <div class="content">
                <h2>Detalles de tu Pedido</h2>
                <p><strong>Número de Pedido:</strong> #<?php echo $pedido_data['pedido_id']; ?></p>
                <p><strong>Fecha:</strong> <?php echo $pedido_data['fecha']; ?></p>
                <p><strong>Cliente:</strong> <?php echo $pedido_data['cliente']; ?></p>
                <p><strong>Método de Pago:</strong> <?php echo $pedido_data['metodo_pago']; ?></p>
                
                <h3>Productos:</h3>
                <?php foreach ($pedido_data['productos'] as $producto): ?>
                <div class="producto">
                    <strong><?php echo $producto['nombre']; ?></strong><br>
                    Cantidad: <?php echo $producto['cantidad']; ?> | 
                    Precio: $<?php echo number_format($producto['precio'], 2); ?> | 
                    Subtotal: $<?php echo number_format($producto['subtotal'], 2); ?>
                </div>
                <?php endforeach; ?>
                
                <div class="total">
                    <p>Subtotal: $<?php echo number_format($pedido_data['subtotal'], 2); ?></p>
                    <p>IVA (16%): $<?php echo number_format($pedido_data['iva'], 2); ?></p>
                    <p style="font-size: 1.2em;">TOTAL: $<?php echo number_format($pedido_data['total'], 2); ?></p>
                </div>
                
                <p><strong>Información de entrega:</strong></p>
                <p>Tiempo estimado: 2-3 días hábiles<br>
                Zona de entrega: CDMX y área metropolitana</p>
            </div>
            
            <div class="footer">
                <p>📞 Contacto: 55-1234-5678 | 📧 hola@floresdechinampa.com</p>
                <p>¡Esperamos verte pronto!</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private function crearCuerpoTexto($pedido_data) {
        $texto = "FLORES DE CHINAMPA\n";
        $texto .= "================\n\n";
        $texto .= "¡Gracias por tu compra!\n\n";
        $texto .= "Detalles del Pedido:\n";
        $texto .= "Número: #" . $pedido_data['pedido_id'] . "\n";
        $texto .= "Fecha: " . $pedido_data['fecha'] . "\n";
        $texto .= "Cliente: " . $pedido_data['cliente'] . "\n";
        $texto .= "Método de Pago: " . $pedido_data['metodo_pago'] . "\n\n";
        
        $texto .= "Productos:\n";
        foreach ($pedido_data['productos'] as $producto) {
            $texto .= "- " . $producto['nombre'] . " x" . $producto['cantidad'] . " - $" . number_format($producto['subtotal'], 2) . "\n";
        }
        
        $texto .= "\n";
        $texto .= "Subtotal: $" . number_format($pedido_data['subtotal'], 2) . "\n";
        $texto .= "IVA: $" . number_format($pedido_data['iva'], 2) . "\n";
        $texto .= "TOTAL: $" . number_format($pedido_data['total'], 2) . "\n\n";
        
        $texto .= "Tiempo de entrega: 2-3 días hábiles\n";
        $texto .= "Zona: CDMX y área metropolitana\n\n";
        
        $texto .= "Contacto: 55-1234-5678\n";
        $texto .= "Email: hola@floresdechinampa.com";
        
        return $texto;
    }
}
?>