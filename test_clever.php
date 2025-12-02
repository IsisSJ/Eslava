<?php
// test_clever.php - Verificar conexión Clever Cloud
echo "<h3>Variables de Clever Cloud:</h3>";
echo "<pre>";
echo "MYSQL_ADDON_HOST: " . (getenv('MYSQL_ADDON_HOST') ?: 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com') . "\n";
echo "MYSQL_ADDON_DB: " . (getenv('MYSQL_ADDON_DB') ?: 'bc8i4pda2kn2fqs150qm') . "\n";
echo "MYSQL_ADDON_USER: " . (getenv('MYSQL_ADDON_USER') ?: 'uo5qglcqiyhjhqot') . "\n";
echo "MYSQL_ADDON_PASSWORD: " . (getenv('MYSQL_ADDON_PASSWORD') ?: 'wSlvgtI1vH86LAydhriK') . "\n";
echo "MYSQL_ADDON_PORT: " . (getenv('MYSQL_ADDON_PORT') ?: '3306') . "\n";
echo "</pre>";

// Probar conexión
$host = getenv('MYSQL_ADDON_HOST');
$dbname = getenv('MYSQL_ADDON_DB');
$username = getenv('MYSQL_ADDON_USER');
$password = getenv('MYSQL_ADDON_PASSWORD');
$port = getenv('MYSQL_ADDON_PORT') ?: '3306';

if ($host && $dbname && $username) {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $password);
        echo "✅ ¡Conexión exitosa a Clever Cloud MySQL!";
    } catch(PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "❌ Variables de Clever Cloud no configuradas";
}
?>