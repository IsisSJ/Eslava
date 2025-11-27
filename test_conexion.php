<?php
include_once 'conexion.php';

if ($conn) {
    echo "✅ Conexión a BD exitosa";
    
    // Probar consulta
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "<br>✅ Total usuarios: " . $result['total'];
    } catch (PDOException $e) {
        echo "<br>❌ Error en consulta: " . $e->getMessage();
    }
} else {
    echo "❌ Conexión a BD fallida";
}
?>