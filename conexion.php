<?php
// conexion.php - Versión segura
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "flores_chinampa";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb;'>
        <h3>Error de conexión a la base de datos</h3>
        <p><strong>Mensaje:</strong> " . $conn->connect_error . "</p>
        <p><strong>Verifica:</strong></p>
        <ul>
            <li>Que XAMPP esté ejecutándose</li>
            <li>Que MySQL esté activo</li>
            <li>Que la base de datos 'flores_chinampa' exista</li>
        </ul>
    </div>");
}

// Establecer charset
if (!$conn->set_charset("utf8mb4")) {
    die("Error cargando el conjunto de caracteres utf8mb4: " . $conn->error);
}

// Función para debug
function debug_db($mensaje) {
    error_log("DEBUG DB: " . $mensaje);
}
?>