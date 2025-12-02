<?php
// debug_login_complete.php - Debug exhaustivo del login
echo "<h1>🔍 DEBUG COMPLETO DEL LOGIN</h1>";

// 1. Verificar conexión a BD
echo "<h3>1. Conexión a Base de Datos:</h3>";
try {
    require_once 'conexion.php';
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Conexión OK: " . $result['test'] . "<br>";
    
    // Verificar tabla usuarios
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    echo "✅ Tabla 'usuarios' existe: " . ($stmt->fetch() ? 'SÍ' : 'NO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

// 2. Buscar usuario 'admin' específicamente
echo "<h3>2. Buscando usuario 'admin':</h3>";
try {
    $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Usuario 'admin' encontrado<br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Nombre: " . $admin['nombre_usuario'] . "<br>";
        echo "- Rol: " . $admin['rol'] . "<br>";
        echo "- Password hash: " . $admin['password'] . "<br>";
        echo "- Longitud hash: " . strlen($admin['password']) . " caracteres<br>";
        
        // Probar contraseña 'admin123' directamente
        $password_to_test = 'admin123';
        echo "<h4>Probando contraseña '$password_to_test':</h4>";
        
        // Método 1: password_verify
        $es_valida = password_verify($password_to_test, $admin['password']);
        echo "- password_verify(): " . ($es_valida ? "✅ <strong>VÁLIDA</strong>" : "❌ INVÁLIDA") . "<br>";
        
        // Método 2: Verificar manualmente el hash
        echo "- Hash de 'admin123': " . password_hash($password_to_test, PASSWORD_DEFAULT) . "<br>";
        
        // Método 3: Intentar otras contraseñas comunes
        echo "<h4>Probando otras contraseñas:</h4>";
        $passwords = ['admin123', 'admin', '123456', 'password', 'chinampa123', 'flores2024'];
        foreach ($passwords as $pass) {
            if (password_verify($pass, $admin['password'])) {
                echo "<div style='background:green;color:white;padding:10px;margin:5px 0;'>
                        ✅ ¡ENCONTRADA! Usa: <strong>admin / $pass</strong>
                      </div>";
            }
        }
        
    } else {
        echo "❌ Usuario 'admin' NO encontrado en la base de datos<br>";
        // Mostrar todos los usuarios
        echo "<h4>Usuarios existentes:</h4>";
        $stmt = $conn->query("SELECT id, nombre_usuario, rol FROM usuarios ORDER BY id");
        $usuarios = $stmt->fetchAll();
        if ($usuarios) {
            echo "<table border='1'><tr><th>ID</th><th>Usuario</th><th>Rol</th></tr>";
            foreach ($usuarios as $user) {
                echo "<tr><td>{$user['id']}</td><td>{$user['nombre_usuario']}</td><td>{$user['rol']}</td></tr>";
            }
            echo "</table>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Resetear contraseña si es necesario
echo "<h3>3. Resetear contraseña del admin:</h3>";
?>
<form method="POST" action="force_password_reset.php">
    <input type="hidden" name="user_id" value="1">
    Nueva contraseña: <input type="text" name="new_password" value="admin123">
    <button type="submit">Forzar Reset</button>
</form>

<hr>
<a href="login_working.php">Ir a Login Working</a> | 
<a href="admin_access_force.php">Acceso Forzado</a>