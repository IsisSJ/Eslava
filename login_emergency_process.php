<?php
// login_emergency_process.php - Procesar login de emergencia
session_start();
include_once('conexion.php');

$usuario_id = intval($_POST['usuario_id'] ?? 0);
$nueva_password = trim($_POST['nueva_password'] ?? '');

if ($usuario_id <= 0 || strlen($nueva_password) < 6) {
    die("Datos inválidos");
}

try {
    // 1. Resetear contraseña
    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->execute([$password_hash, $usuario_id]);
    
    // 2. Obtener información del usuario
    $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        die("Usuario no encontrado");
    }
    
    // 3. Establecer sesión
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
    $_SESSION['usuario_email'] = $usuario['correo'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['emergency_login'] = true; // Marcar como login de emergencia
    
    // 4. Redirigir
    if ($usuario['rol'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: articulos.php");
    }
    exit();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>