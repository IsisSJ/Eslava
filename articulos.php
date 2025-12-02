<?php
// articulos.php - Para usuarios normales
session_start();

// Verificar si está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");

// Obtener artículos con stock
$articulos = $conn->query("SELECT * FROM articulos WHERE stock > 0 ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="articulos.php">🌺 Flores de Chinampa</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </span>
                <a class="btn btn-outline-light btn-sm" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>🌷 Nuestros Productos</h2>
        <p class="text-muted">Catálogo de flores disponibles</p>
        
        <div class="row">
            <?php if (count($articulos) > 0): ?>
                <?php foreach ($articulos as $articulo): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($articulo['nombre']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($articulo['descripcion']); ?></p>
                            <p class="h4 text-success">$<?php echo number_format($articulo['precio'], 2); ?></p>
                            <p class="card-text">
                                <span class="badge bg-<?php echo $articulo['stock'] > 10 ? 'success' : 'warning'; ?>">
                                    Stock: <?php echo $articulo['stock']; ?> unidades
                                </span>
                            </p>
                            <button class="btn btn-primary w-100">Agregar al Carrito</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>No hay productos disponibles</h5>
                        <p>Pronto tendremos nuevo stock.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>