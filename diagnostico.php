<?php
// diagnostico.php - EJECUTAR TEMPORALMENTE
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🔍 Diagnóstico del Sistema</h3>";

// 1. Verificar PHP
echo "<h4>✅ PHP Version: " . PHP_VERSION . "</h4>";

// 2. Verificar conexión a BD
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm'; 
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión a BD exitosa</p>";
    
    // 3. Verificar estructura de tabla usuarios
    $stmt = $conn->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📊 Estructura de tabla 'usuarios':</h4>";
    echo "<table border='1' style='width:100%'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Verificar usuarios existentes
    $stmt = $conn->query("SELECT id, nombre_usuario, correo, rol, LENGTH(password) as pass_length FROM usuarios LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>👥 Usuarios en la base de datos:</h4>";
    if (empty($users)) {
        echo "<p style='color: red;'>❌ No hay usuarios en la tabla</p>";
    } else {
        echo "<table border='1' style='width:100%'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Long. Password</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nombre_usuario']}</td>";
            echo "<td>{$user['correo']}</td>";
            echo "<td>{$user['rol']}</td>";
            echo "<td>{$user['pass_length']} chars</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Probar consulta de login
    echo "<h4>🔐 Probando consulta de login:</h4>";
    $test_user = 'admi';
    $sql = "SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ? OR correo = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$test_user, $test_user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Consulta funciona - Usuario encontrado: " . $result['nombre_usuario'] . "</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ Usuario 'admi' no encontrado</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h4>🎯 Próximos pasos:</h4>";
echo "<ol>";
echo "<li>Ejecuta este diagnóstico</li>";
echo "<li>Comparte los resultados</li>";
echo "<li>Corregiremos el problema específico</li>";
echo "</ol>";

echo "<div style='background: #ffeb3b; padding: 10px; margin: 10px 0;'>";
echo "<strong>⚠️ SEGURIDAD:</strong> Elimina este archivo después de usarlo.";
echo "</div>";
?>