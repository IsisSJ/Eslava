<?php
include "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = "cliente"; // por defecto cliente

    // Procesar foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['tmp_name'] != "") {
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, foto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $usuario, $password, $rol, $foto);
    $stmt->send_long_data(3, $foto);

    if ($stmt->execute()) {
        $mensaje = "<div class='alert alert-success text-center'>✅ Usuario registrado correctamente. <a href='index.php' class='alert-link'>Inicia sesión aquí</a>.</div>";
    } else {
        $mensaje = "<div class='alert alert-danger text-center'>❌ Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Flores de Chinampa - Registro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #388E3C;
            --light-color: #F1F8E9;
            --dark-color: #1B5E20;
        }
        body {
            background: linear-gradient(135deg, var(--light-color), #E8F5E9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .auth-header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .auth-body {
            padding: 30px;
            background-color: white;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
        }
        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        .auth-link:hover {
            color: var(--dark-color);
            text-decoration: underline;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-container bg-white rounded shadow">
        <div class="auth-header">
            <img src="logo.png" alt="Flores de Chinampa" class="logo">
            <h2>Registro de Usuario</h2>
        </div>
        <div class="auth-body">
            <?php if (!empty($mensaje)) echo $mensaje; ?>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" capture="camera" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
            </form>
            <div class="text-center mt-3">
                <p>¿Ya tienes cuenta? <a href="index.php" class="auth-link">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>
