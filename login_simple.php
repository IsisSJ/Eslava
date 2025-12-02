<?php
// login_simple.php - Versión super simple sin errores
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['usuario_rol'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: articulos.php");
    }
    exit();
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = "Ingresa usuario y contraseña";
    } else {
        include_once('conexion.php');
        
        try {
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
                $_SESSION['usuario_rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                
                if ($user['rol'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: articulos.php");
                }
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } catch (Exception $e) {
            $error = "Error del sistema";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">🌺 Flores de Chinampa</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" name="nombre_usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Iniciar Sesión</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="registro.php">Registrarse</a> • 
                            <a href="list_users.php">Ver usuarios</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>