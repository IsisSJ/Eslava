<?php
// test_conexion.php
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK';
$port = 3306;

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexión exitosa a la base de datos!<br>";
    
    // Probar consulta básica
    $stmt = $conn->query("SELECT DATABASE() as db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Base de datos conectada: " . $result['db'] . "<br>";
    
    // Verificar tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas encontradas (" . count($tables) . "):<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getCode();
}
?>