<?php
include_once("conexion.php");

echo "<h1>Verificación de Usuarios en la Base de Datos</h1>";

// Verificar estructura de la tabla
$result = $conn->query("DESCRIBE usuarios");
echo "<h3>Estructura de la tabla 'usuarios':</h3>";
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

// Mostrar usuarios existentes
echo "<h3>Usuarios registrados:</h3>";
$users = $conn->query("SELECT id, nombre_usuario, correo, rol, password FROM usuarios");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Password Hash</th></tr>";
while ($user = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . $user['nombre_usuario'] . "</td>";
    echo "<td>" . $user['correo'] . "</td>";
    echo "<td>" . $user['rol'] . "</td>";
    echo "<td>" . substr($user['password'], 0, 20) . "...</td>";
    echo "</tr>";
}
echo "</table>";

echo '<p><a href="index.php">Volver al Login</a></p>';
?>