<?php
// force_reset.php - Resetear contraseña SIN VERIFICACIONES
include_once('conexion.php');

$user_id = intval($_POST['user_id'] ?? 1);
$new_password = $_POST['new_password'] ?? 'admin123';

echo "<h1>🔄 FORZAR RESET DE CONTRASEÑA</h1>";

try {
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->execute([$password_hash, $user_id]);
    
    // Obtener info del usuario
    $stmt = $conn->prepare("SELECT nombre_usuario FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();
    
    echo "<div style='background:green;color:white;padding:20px;'>";
    echo "✅ CONTRASEÑA RESETEADA EXITOSAMENTE<br><br>";
    echo "<strong>Usuario:</strong> " . $usuario['nombre_usuario'] . "<br>";
    echo "<strong>Nueva contraseña:</strong> " . htmlspecialchars($new_password) . "<br>";
    echo "<strong>Hash generado:</strong> " . substr($password_hash, 0, 30) . "...<br>";
    echo "</div>";
    
    // Verificar que funciona
    $es_valida = password_verify($new_password, $password_hash);
    echo "<br>Verificación: " . ($es_valida ? "✅ La contraseña es válida" : "❌ Error en la verificación");
    
} catch (Exception $e) {
    echo "<div style='background:red;color:white;padding:20px;'>";
    echo "❌ ERROR: " . $e->getMessage();
    echo "</div>";
}

echo '<br><br><a href="debug_login.php">Volver al Debug</a>';
?>