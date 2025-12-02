<?php
// login_super_simple.php - Login SIN complejidades
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    // Conexión directa SIN configuración complicada
    try {
        $host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
        $dbname = 'bc8i4pda2kn2fqs150qm';
        $username = 'uo5qglcqiyhjhqot';
        $dbpassword = 'wSlvgtI1vH86LAydhriK';
        $port = '3306';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar usuario
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login exitoso
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
            $_SESSION['usuario_rol'] = $user['rol'];
            $_SESSION['logged_in'] = true;
            
            header("Location: admin.php");
            exit();
        } else {
            $error = "Credenciales incorrectas";
        }
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Super Simple</title>
    <style>
        body { font-family: Arial; padding: 50px; }
        .login-box { max-width: 300px; margin: 0 auto; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: green; color: white; padding: 10px; width: 100%; border: none; }
        .error { background: red; color: white; padding: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🌺 Login Super Simple</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" value="admin" required>
            <input type="password" name="password" placeholder="Contraseña" value="admin123" required>
            <button type="submit">Entrar</button>
        </form>
        
        <p style="margin-top: 20px;">
            <strong>Probar con:</strong><br>
            Usuario: <code>admin</code><br>
            Contraseña: <code>admin123</code>
        </p>
        
        <p>
            <a href="debug_login_complete.php">🔍 Debug</a> | 
            <a href="admin_access_force.php">🚨 Acceso Forzado</a>
        </p>
    </div>
</body>
</html>