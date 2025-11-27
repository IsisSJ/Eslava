<?php
// conexion.php - Para Clever Cloud con mejor manejo de errores

// Configuración directa para Clever Cloud
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK';  // ⚠️ REEMPLAZA ESTO
$port = '3306';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    // Verificar que la conexión es válida
    $conn->query("SELECT 1");
    error_log("✅ Conexión exitosa a Clever Cloud MySQL");
    
} catch(PDOException $e) {
    $error_message = $e->getMessage();
    error_log("❌ Error de conexión MySQL: " . $error_message);
    
    // Mensajes específicos según el error
    if (strpos($error_message, 'Access denied') !== false) {
        die("Error: Credenciales incorrectas. Verifica usuario y contraseña.");
    } elseif (strpos($error_message, 'Unknown database') !== false) {
        die("Error: La base de datos no existe.");
    } elseif (strpos($error_message, 'Connection refused') !== false) {
        die("Error: No se puede conectar al servidor MySQL.");
    } else {
        die("Error de conexión con la base de datos: " . $error_message);
    }
}
?>