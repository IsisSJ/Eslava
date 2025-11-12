<?php
// verificar_setup.php
echo "<h1>🔍 Verificación de Configuración PHPMailer</h1>";

// Verificar archivos de PHPMailer
$phpmailer_path = __DIR__ . '/PHPMailer/src/';
$archivos = [
    'PHPMailer.php' => $phpmailer_path . 'PHPMailer.php',
    'SMTP.php' => $phpmailer_path . 'SMTP.php',
    'Exception.php' => $phpmailer_path . 'Exception.php'
];

foreach ($archivos as $nombre => $ruta) {
    if (file_exists($ruta)) {
        echo "<p style='color: green;'>✅ $nombre - ENCONTRADO</p>";
    } else {
        echo "<p style='color: red;'>❌ $nombre - NO ENCONTRADO en: " . $ruta . "</p>";
    }
}

// Verificar si la carpeta PHPMailer existe
if (is_dir('PHPMailer')) {
    echo "<p style='color: green;'>✅ Carpeta PHPMailer existe</p>";
    
    // Mostrar contenido
    echo "<h3>Contenido de PHPMailer:</h3>";
    $contenido = scandir('PHPMailer');
    echo "<pre>";
    print_r($contenido);
    echo "</pre>";
    
    echo "<h3>Contenido de PHPMailer/src:</h3>";
    if (is_dir('PHPMailer/src')) {
        $contenido_src = scandir('PHPMailer/src');
        echo "<pre>";
        print_r($contenido_src);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ La carpeta src no existe</p>";
    }
} else {
    echo "<p style='color: red;'>❌ La carpeta PHPMailer NO existe</p>";
    echo "<p><strong>Solución:</strong> Descarga PHPMailer desde: ";
    echo "<a href='https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' target='_blank'>https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip</a></p>";
    echo "<p>Extrae y renombra la carpeta a 'PHPMailer'</p>";
}

echo "<hr>";
echo "<p><a href='enviar_ticket.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Probar Envío de Ticket</a></p>";
?>