<?php
// test_articulos_detallado.php - ARCHIVO TEMPORAL
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conexion.php');

echo "<h3>🔍 DIAGNÓSTICO DETALLADO ARTÍCULOS</h3>";

try {
    // Ver artículos con detalles de imágenes
    $sql = "SELECT id, nombre, precio, imagen, stock, LENGTH(imagen) as img_length 
            FROM articulos 
            WHERE stock > 0 
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📦 Artículos en stock:</h4>";
    
    if (empty($articulos)) {
        echo "<p style='color:red'>❌ No hay artículos en stock</p>";
    } else {
        echo "<table border='1' style='width:100%; font-size:12px;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Imagen</th><th>Long. Img</th><th>Stock</th><th>¿Imagen existe?</th></tr>";
        
        foreach ($articulos as $art) {
            $imagen_path = $art['imagen'];
            $imagen_existe = !empty($imagen_path) && file_exists($imagen_path);
            
            echo "<tr>";
            echo "<td>{$art['id']}</td>";
            echo "<td>{$art['nombre']}</td>";
            echo "<td>\${$art['precio']}</td>";
            echo "<td>" . (empty($imagen_path) ? 'NULL' : $imagen_path) . "</td>";
            echo "<td>{$art['img_length']}</td>";
            echo "<td>{$art['stock']}</td>";
            echo "<td style='color:" . ($imagen_existe ? 'green' : 'red') . "'>" . 
                 ($imagen_existe ? '✅ EXISTE' : '❌ NO EXISTE') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>