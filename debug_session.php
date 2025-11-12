<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>🔍 Debug de Sesión</h2>
    <div class="card">
        <div class="card-body">
            <h4>Variables de Sesión:</h4>
            <pre><?php
            echo "usuario: " . ($_SESSION['usuario'] ?? 'NO DEFINIDO') . "\n";
            echo "rol: " . ($_SESSION['rol'] ?? 'NO DEFINIDO') . "\n";
            echo "user_id: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "\n";
            echo "session_id: " . session_id() . "\n";
            ?></pre>
            
            <h4>Probar Accesos:</h4>
            <div class="d-flex gap-2">
                <a href="editar_perfil.php" class="btn btn-primary">Ir a Editar Perfil</a>
                <a href="login.php" class="btn btn-secondary">Ir a Login</a>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>