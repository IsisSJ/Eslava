<?php
// debug_login.php - Debug completo del login
session_start();
include_once('conexion.php');

echo "<h1>🔍 DEBUG COMPLETO DEL LOGIN</h1>";

// 1. Verificar conexión a BD
echo "<h3>1. Conexión a Base de Datos:</h3>";
try {
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Conexión OK: " . $result['test'] . "<br>";
    
    // Verificar tabla usuarios
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    echo "✅ Tabla 'usuarios' existe: " . ($stmt->fetch() ? 'SÍ' : 'NO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

// 2. Verificar usuario admin específicamente
echo "<h3>2. Usuario 'admin':</h3>";
try {
    $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Usuario admin encontrado<br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Nombre: " . $admin['nombre_usuario'] . "<br>";
        echo "- Rol: " . $admin['rol'] . "<br>";
        echo "- Hash password: " . substr($admin['password'], 0, 30) . "...<br>";
        
        // Probar contraseñas comunes
        $passwords_to_test = ['admin123', 'chinampa123', 'password', '123456', 'admin', 'flores2024'];
        echo "<h4>Probar contraseñas:</h4>";
        foreach ($passwords_to_test as $pass) {
            $es_valida = password_verify($pass, $admin['password']);
            echo "- '$pass': " . ($es_valida ? "✅ <strong>VÁLIDA!</strong>" : "❌ inválida") . "<br>";
            if ($es_valida) {
                echo "<div style='background:green;color:white;padding:10px;margin:5px 0;'>
                        ¡CONTRASEÑA ENCONTRADA! Usa: <strong>admin / $pass</strong>
                      </div>";
            }
        }
        
        // Resetear si ninguna funciona
        echo "<h4>Resetear contraseña:</h4>";
        echo "<form method='POST' action='force_reset.php'>
                <input type='hidden' name='user_id' value='" . $admin['id'] . "'>
                Nueva contraseña: <input type='text' name='new_password' value='admin123'>
                <button type='submit'>Forzar Reset</button>
              </form>";
    } else {
        echo "❌ Usuario admin NO encontrado<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Verificar sesión actual
echo "<h3>3. Sesión PHP:</h3>";
echo "- session_id(): " . session_id() . "<br>";
echo "- session_status(): " . session_status() . "<br>";
echo "- ¿logged_in?: " . (isset($_SESSION['logged_in']) ? 'SÍ' : 'NO') . "<br>";
if (isset($_SESSION['logged_in'])) {
    echo "- Valor: " . ($_SESSION['logged_in'] ? 'true' : 'false') . "<br>";
}

// 4. Probar login directo
echo "<h3>4. Probar Login Directo:</h3>";
?>
<form method="POST" action="test_login_direct.php">
    Usuario: <input type="text" name="usuario" value="admin"><br>
    Contraseña: <input type="password" name="password" value="admin123"><br>
    <button type="submit">Probar Login</button>
</form>

<hr>
<a href="login.php">Ir al Login normal</a> | 
<a href="emergency_access.php">Acceso Emergencia</a>