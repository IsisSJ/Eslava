<?php
// verificar_imagenes.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conexion.php');

echo "<h3>🔍 Verificación de imágenes después de la corrección</h3>";

try {
    $sql = "SELECT id, nombre, imagen, LENGTH(imagen) as img_length FROM articulos ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='width:100%; font-size:12px;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Imagen</th><th>Longitud</th><th>¿Carga?</th></tr>";
    
    foreach ($articulos as $art) {
        $carga_ok = !empty($art['imagen']) && file_exists($art['imagen']);
        
        echo "<tr>";
        echo "<td>{$art['id']}</td>";
        echo "<td>{$art['nombre']}</td>";
        echo "<td>" . ($art['imagen'] ?: '<span style="color:red">NULL</span>') . "</td>";
        echo "<td>{$art['img_length']}</td>";
        echo "<td style='color:" . ($carga_ok ? 'green' : 'red') . "'>";
        echo $carga_ok ? '✅ SÍ' : '❌ NO';
        echo "</td>";
        echo "</tr>";
        
        // Mostrar imagen si existe
        if ($carga_ok) {
            echo "<tr><td colspan='5' style='text-align: center;'>";
            echo "<img src='{$art['imagen']}' style='max-width:100px; max-height:100px; border:1px solid #ddd;'";
            echo " onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"inline\"'>";
            echo "<span style='display:none; color:orange;'>⚠️ No se pudo cargar</span>";
            echo "</td></tr>";
        }
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Verificar qué imágenes existen físicamente
echo "<h3>📁 Imágenes en servidor:</h3>";
$imagenes_disponibles = glob('imagenes/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
if ($imagenes_disponibles) {
    foreach ($imagenes_disponibles as $imagen) {
        echo "<div style='display: inline-block; margin: 10px; text-align: center;'>";
        echo "<img src='$imagen' style='max-width: 80px; max-height: 80px; border: 1px solid #ccc;'>";
        echo "<br><small>" . basename($imagen) . "</small>";
        echo "</div>";
    }
} else {
    echo "<p style='color:red'>❌ No se encontraron imágenes en la carpeta 'imagenes'</p>";
}
?>