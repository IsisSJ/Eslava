<?php
// test_rapido.php - DIAGNÓSTICO RÁPIDO
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conexion.php');

echo "<h3>🚀 DIAGNÓSTICO RÁPIDO</h3>";

// 1. Verificar sesión
echo "<h4>🔐 Sesión:</h4>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar artículos
try {
    $sql = "SELECT id, nombre, precio, imagen, stock FROM articulos WHERE stock > 0 LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📦 Artículos disponibles:</h4>";
    if (empty($articulos)) {
        echo "<p style='color:red'>❌ No hay artículos</p>";
    } else {
        foreach ($articulos as $art) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin:5px;'>";
            echo "<strong>{$art['nombre']}</strong> - \${$art['precio']}";
            echo "<br>Imagen: " . ($art['imagen'] ?: 'No tiene');
            if ($art['imagen']) {
                echo "<br><img src='{$art['imagen']}' style='max-width:100px;' onerror='this.style.display=\"none\"'>";
            }
            echo "</div>";
        }
    }
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Error BD: " . $e->getMessage() . "</p>";
}

// 3. Verificar carrito
echo "<h4>🛒 Carrito en sesión:</h4>";
if (isset($_SESSION['carrito'])) {
    echo "<pre>";
    print_r($_SESSION['carrito']);
    echo "</pre>";
} else {
    echo "<p>Carrito vacío</p>";
}
?>