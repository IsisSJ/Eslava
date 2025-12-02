<?php
include_once 'config_session.php';
include_once("conexion.php");

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

echo "<h1>🧪 Probando todos los QR</h1>";

$sql = "SELECT id, nombre, qr_code FROM articulos WHERE qr_code IS NOT NULL";
$result = $conn->query($sql);

$correctos = 0;
$incorrectos = 0;

if ($result->num_rows > 0) {
    while ($articulo = $result->fetch_assoc()) {
        preg_match('/data=([^&]+)/', $articulo['qr_code'], $matches);
        $url = isset($matches[1]) ? urldecode($matches[1]) : '';
        
        // Convertir a URL de InfinityFree si es local
        if (strpos($url, 'localhost') !== false) {
            preg_match('/id=(\d+)/', $url, $id_match);
            $nueva_url = "https://eslava-floreria.rf.gd/ver_producto.php?id=" . ($id_match[1] ?? '');
            
            echo "<p>🔄 <strong>{$articulo['nombre']}</strong>: ";
            echo "Convertido: <code>{$nueva_url}</code></p>";
        } else {
            echo "<p>✓ <strong>{$articulo['nombre']}</strong>: ";
            echo "<code>{$url}</code></p>";
        }
        
        $correctos++;
    }
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Correctos: {$correctos}</p>";
echo "<p>❌ Incorrectos: {$incorrectos}</p>";
echo "<p><a href='verificar_qr.php'>← Volver al verificador</a></p>";
?>