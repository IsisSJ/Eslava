<?php
include_once("conexion.php");

// Asignar rol de administrador al usuario con ID 1 (o el que quieras)
$stmt = $conn->prepare("UPDATE usuarios SET rol = 'admin' WHERE id = 1");
if ($stmt->execute()) {
    echo "✅ Usuario actualizado a administrador";
} else {
    echo "❌ Error: " . $stmt->error;
}
$stmt->close();

echo '<br><a href="login.php">Ir al login</a>';
?>