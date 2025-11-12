<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

echo "<h1>🔍 Verificador de URLs para QR</h1>";

// Obtener todos los artículos
$sql = "SELECT id, nombre, qr_code FROM articulos WHERE qr_code IS NOT NULL";
$result = $conn->query($sql);

echo "<h3>Artículos con QR generado:</h3>";

if ($result->num_rows > 0) {
    echo "<div style='overflow-x: auto;'>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<thead><tr style='background-color: #f8f9fa;'><th style='padding: 10px; border: 1px solid #ddd;'>ID</th><th style='padding: 10px; border: 1px solid #ddd;'>Producto</th><th style='padding: 10px; border: 1px solid #ddd;'>URL del QR</th><th style='padding: 10px; border: 1px solid #ddd;'>¿Accesible?</th><th style='padding: 10px; border: 1px solid #ddd;'>Acción</th></tr></thead>";
    echo "<tbody>";
    
    while ($articulo = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$articulo['id']}</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'><strong>{$articulo['nombre']}</strong></td>";
        
        // Extraer la URL del código QR
        preg_match('/data=([^&]+)/', $articulo['qr_code'], $matches);
        $decoded_url = isset($matches[1]) ? urldecode($matches[1]) : 'No se pudo decodificar';
        
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        echo "<small style='word-break: break-all;'>" . htmlspecialchars($decoded_url) . "</small>";
        echo "</td>";
        
        // Verificar si la URL es accesible (MEJORADO)
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        $accesible = verificarAccesibilidadURL($decoded_url);
        if ($accesible) {
            echo "<span style='color: green;'>✅ Accesible</span>";
        } else {
            echo "<span style='color: red;'>❌ No accesible</span>";
        }
        echo "</td>";
        
        // Acción para regenerar QR si no es accesible
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        if (!$accesible) {
            echo "<a href='generar_qr_fijo.php?id={$articulo['id']}' style='color: blue; text-decoration: none;'>🔄 Regenerar</a>";
        } else {
            echo "<span style='color: gray;'>✓ OK</span>";
        }
        echo "</td>";
        
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<p>No hay artículos con QR generado.</p>";
}

// FUNCIÓN MEJORADA para verificar accesibilidad
function verificarAccesibilidadURL($url) {
    // Limpiar y verificar la URL
    $url_limpia = trim($url);
    
    // Si es una URL local, convertirla a la URL de InfinityFree
    if (strpos($url_limpia, 'localhost') !== false || strpos($url_limpia, '192.168.') !== false) {
        // Extraer el id de la URL local
        preg_match('/id=(\d+)/', $url_limpia, $matches);
        $producto_id = isset($matches[1]) ? $matches[1] : '';
        
        if ($producto_id) {
            $url_limpia = "https://eslava-floreria.rf.gd/ver_producto.php?id=" . $producto_id;
        }
    }
    
    // Verificar usando cURL o file_get_contents
    $cabeceras = @get_headers($url_limpia);
    if ($cabeceras && strpos($cabeceras[0], '200')) {
        return true;
    }
    
    return false;
}

// Mostrar información del servidor MEJORADA
echo "<h3>Información del servidor InfinityFree:</h3>";
echo "<ul>";
echo "<li><strong>Dominio:</strong> https://eslava-floreria.rf.gd</li>";
echo "<li><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li><strong>PHP Self:</strong> " . $_SERVER['PHP_SELF'] . "</li>";
echo "<li><strong>Directorio:</strong> " . dirname($_SERVER['PHP_SELF']) . "</li>";
echo "<li><strong>Protocolo:</strong> " . (isset($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP') . "</li>";
echo "</ul>";

// Generar URL de prueba MEJORADA
$dominio_infinityfree = "https://eslava-floreria.rf.gd";
$test_qr_url = $dominio_infinityfree . "/ver_producto.php?id=1";

echo "<h3>✅ URL de prueba CORRECTA para QR:</h3>";
echo "<code style='background: #f4f4f4; padding: 10px; display: block;'>" . htmlspecialchars($test_qr_url) . "</code>";

// Botón para probar todas las URLs
echo "<h3>Acciones rápidas:</h3>";
echo "<p><a href='probar_todos_qr.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🧪 Probar todos los QR</a></p>";

echo "<hr>";
echo "<p><a href='gestion_articulos.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>← Volver a Gestión</a></p>";
?>