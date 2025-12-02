<?php
// force_password_reset.php - Resetear contraseña SIN VERIFICACIONES
require_once 'conexion.php';

$user_id = intval($_POST['user_id'] ?? 1);
$new_password = $_POST['new_password'] ?? 'admin123';

echo "<h1>🔄 FORZAR RESET DE CONTRASEÑA</h1>";

try {
    // 1. Generar hash
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "<h3>Información:</h3>";
    echo "- User ID: $user_id<br>";
    echo "- Nueva contraseña: $new_password<br>";
    echo "- Hash generado: $password_hash<br>";
    
    // 2. Actualizar en BD
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->execute([$password_hash, $user_id]);
    
    // 3. Verificar que se actualizó
    $stmt = $conn->prepare("SELECT nombre_usuario, password FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "<div style='background:green;color:white;padding:20px;margin:10px 0;'>";
        echo "✅ CONTRASEÑA RESETEADA EXITOSAMENTE<br><br>";
        echo "<strong>Usuario:</strong> " . $usuario['nombre_usuario'] . "<br>";
        echo "<strong>Nueva contraseña:</strong> $new_password<br>";
        echo "<strong>Hash en BD:</strong> " . $usuario['password'] . "<br>";
        echo "</div>";
        
        // Verificar que la contraseña funciona
        $es_valida = password_verify($new_password, $usuario['password']);
        echo "<h3>Verificación:</h3>";
        echo "- password_verify('$new_password', hash): " . ($es_valida ? "✅ VÁLIDA" : "❌ INVÁLIDA") . "<br>";
        
        if ($es_valida) {
            echo "<div style='background:blue;color:white;padding:15px;margin:10px 0;'>";
            echo "🎉 ¡TODO CORRECTO! Ahora puedes usar:<br>";
            echo "<strong>Usuario:</strong> " . $usuario['nombre_usuario'] . "<br>";
            echo "<strong>Contraseña:</strong> $new_password<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background:red;color:white;padding:20px;'>";
    echo "❌ ERROR: " . $e->getMessage();
    echo "</div>";
}

echo '<br><br><a href="debug_login_complete.php">Volver al Debug</a>';
?>