<?php
$host = "localhost";
$user = "tu_usuario";
$password = "tu_contraseña";
$database = "tu_base_datos";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>