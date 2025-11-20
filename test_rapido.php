<?php
// test_rapido.php CORREGIDO
error_reporting(E_ALL);
ini_set('display_errors', 1);

// INICIAR SESIÓN PRIMERO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('conexion.php');

echo "<h3>🚀 DIAGNÓSTICO RÁPIDO CORREGIDO</h3>";

// 1. Verificar sesión
echo "<h4>🔐 Sesión:</h4>";
if (empty($_SESSION)) {
    echo "<p style='color:orange'>⚠️ Sesión vacía - ¿Estás logueado?</p>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// 2. Verificar artículos CON MEJOR MANEJO DE IMÁGENES
try {
    $sql = "SELECT id, nombre, precio, imagen, stock, LENGTH(imagen) as img_len FROM articulos WHERE stock > 0 LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📦 Artículos disponibles (Total: " . count($articulos) . "):</h4>";
    
    if (empty($articulos)) {
        echo "<p style='color:red'>❌ No hay artículos</p>";
    } else {
        foreach ($articulos as $art) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin:5px; background:#f9f9f9;'>";
            echo "<strong>{$art['nombre']}</strong> - \${$art['precio']} - Stock: {$art['stock']}";
            echo "<br>ID: {$art['id']}";
            echo "<br>Longitud imagen: {$art['img_len']} caracteres";
            
            // Mostrar información de la imagen de forma segura
            $imagen = $art['imagen'];
            if (empty($imagen) || $imagen === 'NULL') {
                echo "<br>📷 Imagen: <span style='color:red'>VACÍA O NULL</span>";
            } elseif (strlen($imagen) > 255) {
                echo "<br>📷 Imagen: <span style='color:orange'>TEXTO MUY LARGO (posible corrupto)</span>";
                echo "<br><small>Primeros 50 chars: " . htmlspecialchars(substr($imagen, 0, 50)) . "...</small>";
            } else {
                echo "<br>📷 Imagen: " . htmlspecialchars($imagen);
                // Intentar mostrar imagen solo si parece una ruta válida
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imagen)) {
                    echo "<br><img src='" . htmlspecialchars($imagen) . "' style='max-width:100px; max-height:100px; border:1px solid #ddd;' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\"'>";
                    echo "<span style='display:none; color:red;'>❌ Imagen no carga</span>";
                }
            }
            echo "</div>";
        }
    }
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Error BD: " . $e->getMessage() . "</p>";
}

// 3. Verificar carrito
echo "<h4>🛒 Carrito en sesión:</h4>";
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    echo "<pre>";
    print_r($_SESSION['carrito']);
    echo "</pre>";
} else {
    echo "<p>Carrito vacío o no inicializado</p>";
}

// 4. Información del servidor
echo "<h4>🖥️ Información del servidor:</h4>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
?>