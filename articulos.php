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
    <title>Gestión de Artículos - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 80px;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .img-placeholder {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Gestión de Artículos</h2>
            <div>
                <a href="nuevo_articulo.php" class="btn btn-success">➕ Nuevo Artículo</a>
                <a href="admin_dashboard.php" class="btn btn-secondary">← Dashboard</a>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Lista de Artículos (Total: <?php echo $articulos->num_rows; ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($articulos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Imagen</th>
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
                                    <td>
                                        <?php if (!empty($articulo['imagen'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($articulo['imagen']); ?>" 
                                                 class="product-img" 
                                                 alt="<?php echo htmlspecialchars($articulo['nombre']); ?>">
                                        <?php else: ?>
                                            <div class="img-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $articulo['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($articulo['nombre']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo strlen($articulo['descripcion']) > 50 ? 
                                                substr($articulo['descripcion'], 0, 50) . '...' : 
                                                $articulo['descripcion']; ?>
                                        </small>
                                    </td>
                                    <td>$<?php echo number_format($articulo['precio'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $articulo['stock'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $articulo['stock']; ?> unidades
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($articulo['fecha_creacion'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="eliminar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar \'<?php echo htmlspecialchars($articulo['nombre']); ?>\'?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <h5>No hay artículos registrados</h5>
                        <p>Comienza agregando tu primer artículo.</p>
                        <a href="nuevo_articulo.php" class="btn btn-primary">Agregar Primer Artículo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>