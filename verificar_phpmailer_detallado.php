<?php
// verificar_phpmailer_detallado.php
echo "<h1>🔍 Verificación Detallada de PHPMailer</h1>";

// Ruta base
$base_path = __DIR__;

echo "<h3>Ruta del proyecto: $base_path</h3>";

// Verificar estructura completa
$estructura = [
    'PHPMailer/' => 'Carpeta principal',
    'PHPMailer/src/' => 'Carpeta src',
    'PHPMailer/src/PHPMailer.php' => 'Archivo principal',
    'PHPMailer/src/SMTP.php' => 'Archivo SMTP', 
    'PHPMailer/src/Exception.php' => 'Archivo Exception'
];

foreach ($estructura as $ruta => $descripcion) {
    $ruta_completa = $base_path . '/' . $ruta;
    
    if (file_exists($ruta_completa)) {
        $tipo = is_dir($ruta_completa) ? '📁 CARPETA' : '📄 ARCHIVO';
        $tamaño = is_file($ruta_completa) ? ' (' . filesize($ruta_completa) . ' bytes)' : '';
        echo "<p style='color: green;'>✅ $tipo - $descripcion: $ruta $tamaño</p>";
    } else {
        echo "<p style='color: red;'>❌ FALTA - $descripcion: $ruta</p>";
    }
}

// Mostrar árbol de directorios
echo "<h3>🌳 Árbol de directorios:</h3>";
function mostrarArbol($directorio, $nivel = 0) {
    $html = "";
    $sangria = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);
    
    if (is_dir($directorio)) {
        $elementos = scandir($directorio);
        
        foreach ($elementos as $elemento) {
            if ($elemento == '.' || $elemento == '..') continue;
            
            $ruta_completa = $directorio . '/' . $elemento;
            
            if (is_dir($ruta_completa)) {
                $html .= "<p>$sangria📁 <strong>$elemento/</strong></p>";
                $html .= mostrarArbol($ruta_completa, $nivel + 1);
            } else {
                $tamaño = filesize($ruta_completa);
                $html .= "<p>$sangria📄 $elemento ($tamaño bytes)</p>";
            }
        }
    }
    
    return $html;
}

echo mostrarArbol($base_path . '/PHPMailer');

// Soluciones si hay problemas
echo "<h3>🔧 Si hay errores, sigue estos pasos:</h3>";
echo "<ol>";
echo "<li><strong>Elimina la carpeta PHPMailer actual</strong> (si existe)</li>";
echo "<li><strong>Descarga</strong> desde: <a href='https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' target='_blank'>https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip</a></li>";
echo "<li><strong>Extrae</strong> el ZIP</li>";
echo "<li><strong>Renombra</strong> 'PHPMailer-master' a 'PHPMailer'</li>";
echo "<li><strong>Mueve</strong> la carpeta a: C:\\xampp\\htdocs\\Eslava\\</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='enviar_ticket.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📧 Probar Envío de Ticket</a></p>";
echo "<p><a href='verificar_setup.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔄 Verificar Nuevamente</a></p>";
?>