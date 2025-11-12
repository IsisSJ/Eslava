<?php
// conexion.php - Conexión a TU Clever Cloud
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm'; 
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK'; // 👈 HAZ CLIC EN 🔓 Y COPIA EL PASSWORD

// Crear conexión
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    error_log("❌ Error de conexión: " . $conn->connect_error);
    die("Error de conexión a la base de datos");
}

$conn->set_charset("utf8mb4");
error_log("✅ Conexión exitosa a Clever Cloud");
?>