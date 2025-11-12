<?php
// conexion.php - conexión a Clever Cloud MySQL
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com";
$username   = "uo5qglcqiyhjhqot";
$password   = "wSlvgtI1vH86LAydhriK";
$database   = "bc8i4pda2kn2fqs150qm";
$port       = 3306;

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb;'>
        <h3>Error de conexión a la base de datos</h3>
        <p><strong>Mensaje:</strong> " . $conn->connect_error . "</p>
        <p><strong>Verifica:</strong></p>
        <ul>
            <li>Que los datos de Clever Cloud sean correctos</li>
            <li>Que la base esté activa</li>
        </ul>
    </div>");
}

if (!$conn->set_charset("utf8mb4")) {
    die("Error cargando el conjunto de caracteres utf8mb4: " . $conn->error);
}

// Función para depurar (opcional)
function debug_db($mensaje) {
    error_log("DEBUG DB: " . $mensaje);
}
?>
