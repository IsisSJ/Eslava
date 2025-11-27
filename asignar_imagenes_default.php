<?php
// asignar_imagenes_default.php - EJECUTAR UNA VEZ
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conexion.php');

echo "<h3>🖼️ Asignando imágenes por defecto</h3>";

// Mapeo de imágenes por nombre de producto
$imagenes_por_nombre = [
    'cempasuchil' => 'imagenes/cempasuchil.jpg',
    'nochebuena' => 'imagenes/nochebuena.jpg',
    'rosa' => 'imagenes/rosa.jpeg',
    'dalia' => 'imagenes/dalia.jpg',
    'orquídea' => 'imagenes/orquidea.jpeg',
    'magnolia' => 'imagenes/magnolia.jpeg',
    'peyote' => 'imagenes/peyote.jpeg',
    'gbx' => 'imagenes/hierba.jpeg',
    'muicle' => 'imagenes/muicle.jpeg',
    'maguey' => 'imagenes/maguey.jpeg'
];

try {
    // Obtener todos los artículos
    $sql = "SELECT id, nombre, imagen FROM articulos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $actualizados = 0;
    
    foreach ($articulos as $articulo) {
        $nuevo_imagen = null;
        
        // Buscar imagen por nombre del producto
        $nombre_lower = strtolower($articulo['nombre']);
        foreach ($imagenes_por_nombre as $palabra => $imagen_path) {
            if (strpos($nombre_lower, $palabra) !== false) {
                $nuevo_imagen = $imagen_path;
                break;
            }
        }
        
        // Si no se encontró, usar imagen genérica
        if (!$nuevo_imagen) {
            $nuevo_imagen = 'imagenes/xoch-10-1024x700.jpeg';
        }
        
        // Actualizar solo si la imagen actual está vacía o es diferente
        if (empty($articulo['imagen']) || $articulo['imagen'] !== $nuevo_imagen) {
            $update_sql = "UPDATE articulos SET imagen = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([$nuevo_imagen, $articulo['id']]);
            
            echo "<p>✅ {$articulo['nombre']} → {$nuevo_imagen}</p>";
            $actualizados++;
        } else {
            echo "<p>⏭️ {$articulo['nombre']} (ya tiene imagen)</p>";
        }
    }
    
    echo "<h4>🎯 Total actualizados: {$actualizados} productos</h4>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>