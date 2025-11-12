<?php
// procesar_login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once('conexion.php');

if ($_POST) {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    
    echo "<h1>Procesando login...</h1>";
    echo "<p>Usuario: " . htmlspecialchars($usuario) . "</p>";
    
    // Consulta simple para probar
    $sql = "SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ? OR correo = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo "<p>Usuario encontrado: " . $user['nombre_usuario'] . "</p>";
            
            // Verificar contraseña (temporal: mostrar hash)
            echo "<p>Hash en BD: " . substr($user['password'], 0, 20) . "...</p>";
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['usuario'] = $user['nombre_usuario'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['user_id'] = $user['id'];
                
                echo "<p>✅ Login exitoso! Redirigiendo...</p>";
                echo "<script>setTimeout(() => window.location.href = 'index.php', 2000);</script>";
            } else {
                echo "<p>❌ Contraseña incorrecta</p>";
            }
        } else {
            echo "<p>❌ Usuario no encontrado</p>";
        }
        $stmt->close();
    } else {
        echo "<p>❌ Error en consulta: " . $conn->error . "</p>";
    }
    
    echo '<p><a href="login.php">Volver al login</a></p>';
} else {
    header('Location: login.php');
    exit();
}
?>