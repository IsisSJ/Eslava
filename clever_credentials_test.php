<?php
// clever_credentials_test.php - Ver todas las variables posibles
echo "<h3>🔍 Buscando credenciales de Clever Cloud</h3>";
echo "<pre>";

// Variables comunes de Clever Cloud
$possible_vars = [
    'MYSQL_ADDON_HOST',
    'MYSQL_ADDON_DB', 
    'MYSQL_ADDON_USER',
    'MYSQL_ADDON_PASSWORD',
    'MYSQL_ADDON_PORT',
    'MYSQL_ADDON_URI',
    'CC_MYSQL_ADDON_HOST',
    'CC_MYSQL_ADDON_DB',
    'CC_MYSQL_ADDON_USER',
    'CC_MYSQL_ADDON_PASSWORD'
];

foreach ($possible_vars as $var) {
    $value = getenv($var);
    echo "$var: " . ($value ? $value : "NO ENCONTRADO") . "\n";
}

echo "\n=== Todas las variables de entorno ===\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'MYSQL') !== false || strpos($key, 'DB') !== false) {
        echo "$key: $value\n";
    }
}

echo "</pre>";

// Intentar conexión con diferentes combinaciones
echo "<h3>🧪 Probando conexiones...</h3>";

$tests = [
    [
        'host' => getenv('MYSQL_ADDON_HOST') ?: 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com',
        'db' => getenv('MYSQL_ADDON_DB') ?: 'bc8i4pda2kn2fqs150qm',
        'user' => getenv('MYSQL_ADDON_USER') ?: 'uo5qglcqiyhjhqot',
        'pass' => getenv('MYSQL_ADDON_PASSWORD') ?: '',
        'port' => getenv('MYSQL_ADDON_PORT') ?: '3306'
    ]
];

foreach ($tests as $i => $test) {
    echo "<h4>Prueba #" . ($i+1) . "</h4>";
    echo "<pre>Host: " . $test['host'] . "\n";
    echo "DB: " . $test['db'] . "\n";
    echo "User: " . $test['user'] . "\n";
    echo "Pass: " . (empty($test['pass']) ? "VACÍA" : "*****") . "\n";
    echo "Port: " . $test['port'] . "</pre>";
    
    try {
        $dsn = "mysql:host={$test['host']};port={$test['port']};dbname={$test['db']};charset=utf8mb4";
        $conn = new PDO($dsn, $test['user'], $test['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<div style='background: green; color: white; padding: 10px;'>";
        echo "✅ ¡CONEXIÓN EXITOSA!";
        echo "</div>";
        
        // Mostrar tablas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tablas encontradas: " . implode(', ', $tables) . "</p>";
        
        break; // Salir si una conexión funciona
        
    } catch (PDOException $e) {
        echo "<div style='background: red; color: white; padding: 10px;'>";
        echo "❌ Error: " . $e->getMessage();
        echo "</div>";
    }
}

echo '<hr><h3>📋 Instrucciones:</h3>';
echo '<ol>';
echo '<li>Ve a <a href="https://console.clever-cloud.com" target="_blank">Clever Cloud Console</a></li>';
echo '<li>Selecciona tu add-on MySQL</li>';
echo '<li>Busca "Connection Information" o "Credentials"</li>';
echo '<li>Copia la CONTRASEÑA (password)</li>';
echo '<li>Actualiza conexion.php con esa contraseña</li>';
echo '</ol>';
?>