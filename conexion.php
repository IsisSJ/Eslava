<?php
// conexion.php para Clever Cloud MySQL

// Detectar entorno
$is_clever_cloud = getenv('MYSQL_ADDON_HOST') ? true : false;

if ($is_clever_cloud) {
    // Configuración para Clever Cloud
    $host = getenv('MYSQL_ADDON_HOST');
    $dbname = getenv('MYSQL_ADDON_DB');
    $username = getenv('MYSQL_ADDON_USER');
    $password = getenv('MYSQL_ADDON_PASSWORD');
    $port = getenv('MYSQL_ADDON_PORT') ?: '3306';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
} else {
    // Configuración local
    $host = 'localhost';
    $dbname = 'flores_chinampa';
    $username = 'root';
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
}

try {
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    error_log("✅ Conectado a: " . ($is_clever_cloud ? "Clever Cloud MySQL" : "Local MySQL"));
    
} catch(PDOException $e) {
    error_log("❌ Error de conexión: " . $e->getMessage());
    
    // Mensaje detallado para debug
    if ($is_clever_cloud) {
        $debug_info = [
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'port' => $port,
            'error' => $e->getMessage()
        ];
        error_log("Clever Cloud Debug: " . print_r($debug_info, true));
    }
    
    die("Error de conexión con la base de datos. Por favor intenta más tarde.");
}
?>