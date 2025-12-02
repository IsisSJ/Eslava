<?php
// check_tables.php - Verificar estructura de la BD
include_once('conexion.php');

echo "<h3>Verificación de Base de Datos</h3>";

// Verificar tablas
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<h4>Tablas existentes:</h4>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";

// Verificar estructura de tabla usuarios
if (in_array('usuarios', $tables)) {
    echo "<h4>Estructura de tabla 'usuarios':</h4>";
    $columns = $conn->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar algunos usuarios
    echo "<h4>Usuarios en el sistema:</h4>";
    $usuarios = $conn->query("SELECT id, nombre_usuario, correo, rol FROM usuarios LIMIT 10")->fetchAll();
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Correo</th><th>Rol</th></tr>";
    foreach ($usuarios as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['nombre_usuario']}</td>";
        echo "<td>{$user['correo']}</td>";
        echo "<td>{$user['rol']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar si existe un usuario admin
$admin = $conn->query("SELECT * FROM usuarios WHERE rol = 'admin' LIMIT 1")->fetch();
if ($admin) {
    echo "<div style='background: green; color: white; padding: 10px; margin: 10px 0;'>";
    echo "✅ Usuario admin encontrado: <strong>{$admin['nombre_usuario']}</strong>";
    echo "</div>";
} else {
    echo "<div style='background: orange; color: white; padding: 10px; margin: 10px 0;'>";
    echo "⚠️ No hay usuario admin. Puedes crear uno:";
    echo "<br><code>INSERT INTO usuarios (nombre_usuario, correo, password, rol) VALUES ('admin', 'admin@chinampa.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')</code>";
    echo "</div>";
}
?>