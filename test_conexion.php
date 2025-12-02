<?php
// test_connection.php - Test mejorado
echo "<h3>Test de Conexión Clever Cloud</h3>";

$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriKv';  // ⚠️ REEMPLAZA ESTO
$port = '3306';

echo "<pre>";
echo "Host: $host\n";
echo "Database: $dbname\n"; 
echo "Username: $username\n";
echo "Port: $port\n";
echo "</pre>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: green; color: white; padding: 10px;'>";
    echo "✅ ¡CONEXIÓN EXITOSA a Clever Cloud MySQL!";
    echo "</div>";
    
    // Probar consulta
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h4>Tablas en la base de datos:</h4>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<div style='background: red; color: white; padding: 10px;'>";
    echo "❌ ERROR: " . $e->getMessage();
    echo "</div>";
    
    // Información de debug
    echo "<h4>Información para debug:</h4>";
    echo "<pre>";
    echo "Código de error: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . "\n"; 
    echo "Línea: " . $e->getLine() . "\n";
    echo "</pre>";
}
?>