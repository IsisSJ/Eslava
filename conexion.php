<?php
// conexion.php - Configuración para Render/MySQL

// Detectar si estamos en entorno local o producción (Render)
$is_render = getenv('RENDER') ? true : false;

if ($is_render) {
    // Configuración para Render (Producción)
    $host = getenv('MYSQL_HOST') ?: 'localhost';
    $dbname = getenv('MYSQL_DATABASE') ?: 'flores_chinampa';
    $username = getenv('MYSQL_USERNAME') ?: 'admin';
    $password = getenv('MYSQL_PASSWORD') ?: '';
    $port = getenv('MYSQL_PORT') ?: '3306';
    
    // En Render usa TCP en lugar de socket
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
} else {
    // Configuración para entorno local
    $host = 'localhost';
    $dbname = 'flores_chinampa';
    $username = 'root';
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
}

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Debug (quitar en producción)
    error_log("✅ Conexión exitosa a: " . $dsn);
    
} catch(PDOException $e) {
    error_log("❌ Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor intenta más tarde.");
}
?>