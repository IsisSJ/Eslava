<?php
// test_db.php - Para probar conexión
include_once('conexion.php');

try {
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Conexión exitosa! Test: " . $result['test'];
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>