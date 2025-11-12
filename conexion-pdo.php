<?php
// conexion-pdo.php - Conexión temporal con PDO
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm'; 
$username = 'uo5qglcqiyhjhqot';
$password = 'TU_PASSWORD_REAL';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("✅ Conexión PDO exitosa");
} catch(PDOException $e) {
    error_log("❌ Error PDO: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>