<?php
// conexion.php - SOLO conexión a BD, SIN session_start

$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm'; 
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Log del error pero no mostrar detalles en producción
    error_log("❌ ERROR PDO: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>