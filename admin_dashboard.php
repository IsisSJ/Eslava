<?php
session_start();
include_once("conexion.php");

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

$articulos = $conn->query("SELECT * FROM articulos ORDER BY fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Artículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container" style="margin-top: 80px;">
        <h2>📦 Gestión de Artículos</h2>
        
        <div class="mb-3">
            <a href="nuevo_articulo.php" class="btn btn-success">➕ Nuevo Artículo</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Lista de Artículos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($articulo = $articulos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $articulo['id']; ?></td>
                                <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                                <td>$<?php echo number_format($articulo['precio'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $articulo['stock'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $articulo['stock']; ?> unidades
                                    </span>
                                </td>
                                <td><?php echo $articulo['fecha_creacion']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning">Editar</button>
                                    <button class="btn btn-sm btn-info">Ver</button>
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
