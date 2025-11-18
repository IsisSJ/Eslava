<?php
// test_articulos.php - ARCHIVO TEMPORAL
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conexion.php');

echo "<h3>🔍 Verificando tabla articulos</h3>";

try {
    // Ver estructura
    $stmt = $conn->query("DESCRIBE articulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📊 Estructura de tabla 'articulos':</h4>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
    }
    echo "</table>";
    
    // Ver datos
    $stmt = $conn->query("SELECT id, nombre, descripcion, precio, imagen, stock FROM articulos LIMIT 5");
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📦 Artículos en la base de datos:</h4>";
    if (empty($articulos)) {
        echo "<p style='color:red'>❌ No hay artículos en la tabla</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Imagen</th><th>Stock</th></tr>";
        foreach ($articulos as $art) {
            echo "<tr>";
            echo "<td>{$art['id']}</td>";
            echo "<td>{$art['nombre']}</td>";
            echo "<td>\${$art['precio']}</td>";
            echo "<td>" . ($art['imagen'] ?: 'Sin imagen') . "</td>";
            echo "<td>{$art['stock']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>