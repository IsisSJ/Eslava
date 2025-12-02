<?php
// phpinfo.php - Ver variables de entorno
echo "<h3>Variables de Entorno en Render:</h3>";
echo "<pre>";
echo "MYSQL_HOST: " . (getenv('MYSQL_HOST') ?: 'NO CONFIGURADO') . "\n";
echo "MYSQL_DATABASE: " . (getenv('MYSQL_DATABASE') ?: 'NO CONFIGURADO') . "\n"; 
echo "MYSQL_USERNAME: " . (getenv('MYSQL_USERNAME') ?: 'NO CONFIGURADO') . "\n";
echo "MYSQL_PASSWORD: " . (getenv('MYSQL_PASSWORD') ?: 'NO CONFIGURADO') . "\n";
echo "MYSQL_PORT: " . (getenv('MYSQL_PORT') ?: 'NO CONFIGURADO') . "\n";
echo "</pre>";

// Probar conexión directa
$host = getenv('MYSQL_HOST') ?: 'localhost';
$dbname = getenv('MYSQL_DATABASE') ?: 'flores_chinampa';
$username = getenv('MYSQL_USERNAME') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';
$port = getenv('MYSQL_PORT') ?: '3306';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $conn = new PDO($dsn, $username, $password);
    echo "✅ ¡Conexión exitosa a la base de datos!";
} catch(PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>