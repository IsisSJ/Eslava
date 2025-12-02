<?php
// verify_connection.php - Verificación simple
echo "<h3>🔍 Verificación de Conexión</h3>";

$host = getenv('DB_NAME') ?: 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = getenv('DB_PASS') ?: 'wSlvgtI1vH86LAydhriK';
$port = getenv('DB_PORT') ?: '3306';

echo "<pre>";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? 'NO CONFIGURADA' : 'CONFIGURADA (' . strlen($password) . ' chars)') . "\n";
echo "Port: $port\n";
echo "</pre>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: green; color: white; padding: 15px; border-radius: 5px;'>";
    echo "✅ ¡CONEXIÓN EXITOSA A LA BASE DE DATOS!";
    echo "</div>";
    
    // Mostrar tablas
    echo "<h4>Tablas en la base de datos:</h4>";
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>No hay tablas en la base de datos.</p>";
        echo "<p>Ejecuta el siguiente SQL en Clever Cloud:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px;'>";
        echo "CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telecomercio VARCHAR(20),
    rol ENUM('admin', 'cliente') DEFAULT 'cliente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li><strong>$table</strong></li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background: red; color: white; padding: 15px; border-radius: 5px;'>";
    echo "❌ ERROR: " . $e->getMessage();
    echo "</div>";
    
    // Sugerencias
    echo "<h4>Posibles soluciones:</h4>";
    echo "<ol>";
    echo "<li>Verifica que la base de datos exista en Clever Cloud</li>";
    echo "<li>Confirma que el usuario tenga permisos</li>";
    echo "<li>Revisa que la contraseña sea correcta</li>";
    echo "<li>Verifica que el servidor MySQL esté activo</li>";
    echo "</ol>";
}
?>