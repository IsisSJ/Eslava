<?php
// admin_access_force.php - Acceso FORZADO al admin (sin verificar contraseña)
require_once 'conexion.php';

echo "<h1>🚨 ACCESO FORZADO AL ADMINISTRADOR</h1>";

try {
    // 1. Buscar usuario admin
    $stmt = $conn->prepare("SELECT id, nombre_usuario FROM usuarios WHERE nombre_usuario = 'admin' OR rol = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if (!$admin) {
        // Si no hay admin, usar el primer usuario
        $stmt = $conn->query("SELECT id, nombre_usuario FROM usuarios ORDER BY id LIMIT 1");
        $admin = $stmt->fetch();
    }
    
    if ($admin) {
        // 2. Crear sesión forzada
        session_start();
        session_regenerate_id(true);
        
        $_SESSION['usuario_id'] = $admin['id'];
        $_SESSION['usuario_nombre'] = $admin['nombre_usuario'];
        $_SESSION['usuario_rol'] = 'admin';
        $_SESSION['logged_in'] = true;
        $_SESSION['forced_access'] = true;
        
        echo "<div style='background:green;color:white;padding:20px;'>";
        echo "✅ ACCESO FORZADO CONCEDIDO<br><br>";
        echo "<strong>Usuario:</strong> " . $admin['nombre_usuario'] . "<br>";
        echo "<strong>ID:</strong> " . $admin['id'] . "<br>";
        echo "<strong>Session ID:</strong> " . session_id() . "<br>";
        echo "</div>";
        
        // Redirigir automáticamente
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'admin.php';
                }, 3000);
              </script>";
        
        echo "<p>Redirigiendo en 3 segundos...</p>";
        echo '<p><a href="admin.php">Ir al Dashboard ahora</a></p>';
        
    } else {
        echo "<div style='background:red;color:white;padding:20px;'>";
        echo "❌ NO HAY USUARIOS EN LA BASE DE DATOS";
        echo "</div>";
        
        // Crear usuario admin automáticamente
        echo "<h3>Creando usuario admin automáticamente...</h3>";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, password, rol) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['admin', 'admin@chinampa.com', $password_hash]);
        
        $new_id = $conn->lastInsertId();
        echo "<p>✅ Usuario admin creado con ID: $new_id</p>";
        echo "<p><a href='admin_access_force.php'>Intentar acceso nuevamente</a></p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:red;color:white;padding:20px;'>";
    echo "❌ ERROR CRÍTICO: " . $e->getMessage();
    echo "</div>";
    echo "<p><a href='debug_login_complete.php'>Ir al Debug</a></p>";
}
?>