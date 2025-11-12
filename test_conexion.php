<?php
$servername = "localhost";
$username = "root";
$password = "";

// Conectar sin base de datos
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "✅ Conexión al servidor MySQL exitosa<br>";

// Listar bases de datos existentes
$result = $conn->query("SHOW DATABASES");
echo "Bases de datos disponibles:<br>";
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "<br>";
}

$conn->close();
?>