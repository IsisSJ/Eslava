<?php
// test_conexion_simple.php
echo "<h3>Probando conexión...</h3>";

// Intenta cargar conexion.php
if (file_exists('conexion.php')) {
    echo "✅ conexion.php existe<br>";
    
    // Verificar el contenido
    $content = file_get_contents('conexion.php');
    if (strpos($content, 'new PDO') !== false) {
        echo "✅ conexion.php contiene PDO<br>";
    } else {
        echo "❌ conexion.php no contiene PDO<br>";
    }
} else {
    echo "❌ conexion.php NO existe<br>";
}

// Probar conexión directa
try {
    // Configuración para Clever Cloud (usa tus datos reales)
    $host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
    $dbname = 'bc8i4pda2kn2fqs150qm';
    $username = 'uo5qglcqiyhjhqot';
    $password = 'TU_CONTRASEÑA_REAL'; // ⚠️ REEMPLAZA ESTO
    $port = '3306';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a BD exitosa!<br>";
    
    // Probar consulta
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Consulta SQL funcionando: " . $result['test'];
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>