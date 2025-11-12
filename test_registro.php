<?php
// test_registro.php
include_once("conexion.php");

echo "<h1>🔍 Test de Registro</h1>";

// Test de conexión
echo "<h3>1. Test de Conexión a BD:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
}

// Test de estructura de tabla
echo "<h3>2. Estructura de la tabla 'usuarios':</h3>";
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error al obtener estructura de tabla</p>";
}

// Test de inserción
echo "<h3>3. Test de Inserción:</h3>";
$test_user = "test_user_" . time();
$test_email = "test_" . time() . "@test.com";
$test_password = password_hash("test123", PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_usuario, correo, password, telecomercio, rol) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $telecomercio = "555-123-4567";
    $rol = "cliente";
    $stmt->bind_param("sssss", $test_user, $test_email, $test_password, $telecomercio, $rol);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Inserción exitosa - Usuario: $test_user</p>";
        
        // Limpiar el usuario de prueba
        $conn->query("DELETE FROM usuarios WHERE nombre_usuario = '$test_user'");
    } else {
        echo "<p style='color: red;'>❌ Error en inserción: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>❌ Error preparando statement: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<p><a href='registro.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📝 Probar Registro</a></p>";
?>