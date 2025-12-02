<?php
// admin.php - Dashboard admin SIMPLIFICADO
session_start();

// Si no está logueado, redirigir al login que SÍ funciona
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_working.php");
    exit();
}

// Si no es admin, mostrar error
if ($_SESSION['usuario_rol'] !== 'admin') {
    die("<h1>⛔ Acceso Denegado</h1>
         <p>No tienes permisos de administrador.</p>
         <a href='login_working.php'>Volver al login</a>");
}

include_once("conexion.php");

// Obtener estadísticas
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch()['total'];
$total_productos = $conn->query("SELECT COUNT(*) as total FROM articulos")->fetch()['total'];
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos")->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="admin.php">🌺 Admin Dashboard</a>
            <div class="navbar-text text-white">
                Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> 
                <a href="logout.php" class="btn btn-sm btn-outline-light ms-3">Salir</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h1>✅ Panel de Administración</h1>
        <p class="text-muted">Has accedido correctamente como administrador</p>
        
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h3><?php echo $total_usuarios; ?></h3>
                        <p class="mb-0">Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h3><?php echo $total_productos; ?></h3>
                        <p class="mb-0">Productos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <h3><?php echo $total_pedidos; ?></h3>
                        <p class="mb-0">Pedidos</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menú de administración -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">👥 Gestión de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <a href="debug_login.php" class="btn btn-info">Ver todos los usuarios</a>
                        <a href="reset_password_public.php?id=1" class="btn btn-warning">Resetear Admin</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">📦 Gestión de Productos</h5>
                    </div>
                    <div class="card-body">
                        <a href="articulos.php" class="btn btn-success">Ver productos</a>
                        <a href="nuevo_articulo.php" class="btn btn-outline-success">Nuevo producto</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">🔧 Herramientas de Sistema</h5>
                </div>
                <div class="card-body">
                    <a href="debug_login.php" class="btn btn-secondary">Debug System</a>
                    <a href="check_tables.php" class="btn btn-outline-secondary">Ver tablas BD</a>
                    <a href="test_conexion.php" class="btn btn-outline-secondary">Test Conexión</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>