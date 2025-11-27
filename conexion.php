<?php
// conexion.php - SIN session_start()

$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm'; 
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("✅ Conexión PDO exitosa a Clever Cloud - Base: $dbname");
} catch(PDOException $e) {
    error_log("❌ ERROR PDO: " . $e->getMessage());
    if (ini_get('display_errors')) {
        die("Error de conexión: " . $e->getMessage());
    } else {
        die("Error de conexión a la base de datos");
    }
}
?>