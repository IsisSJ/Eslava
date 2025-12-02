<?php
require_once 'config_session.php';
include_once 'config_session.php';

// DEBUG: Verificar estado
error_log("=== ADMIN DASHBOARD ===");
error_log("Session data: " . print_r($_SESSION, true));

// Verificar si está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("No está logueado, redirigiendo a login");
    header("Location: login.php");
    exit();
}

// Verificar si es admin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    error_log("No es admin, mostrando acceso denegado");
    echo "<h1>⛔ Acceso Denegado</h1>";
    echo "<p>No tienes permisos de administrador.</p>";
    echo '<a href="articulos.php">Ir a Artículos</a>';
    exit();
}

// TODO LO DEMÁS IGUAL (tu código original de admin_dashboard.php)
include_once("conexion.php");

// Si llegamos aquí, el usuario ES admin y está logueado
$articulos = $conn->query("SELECT * FROM articulos ORDER BY fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Artículos - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { margin-top: 80px; }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .img-placeholder { width: 60px; height: 60px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; }
        .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php 
    // Header temporal simple
    echo '<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="admin_dashboard.php">';
    echo '<i class="fas fa-leaf me-2"></i>Flores de Chinampa - Admin';
    echo '</a>';
    echo '<div class="navbar-nav ms-auto">';
    echo '<span class="navbar-text me-3">';
    echo '<i class="fas fa-user me-1"></i>';
    echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin');
    echo '</span>';
    echo '<a class="btn btn-outline-light btn-sm" href="logout.php">';
    echo '<i class="fas fa-sign-out-alt me-1"></i>Salir';
    echo '</a>';
    echo '</div></div></nav>';
    ?>
    
    <div class="container">
        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <h3><i class="fas fa-box"></i></h3>
                    <h4><?php echo $articulos->rowCount(); ?></h4>
                    <p>Total Artículos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <h3><i class="fas fa-check-circle"></i></h3>
                    <h4>
                        <?php 
                        $stock_activo = $conn->query("SELECT COUNT(*) as total FROM articulos WHERE stock > 0")->fetch()['total'];
                        echo $stock_activo;
                        ?>
                    </h4>
                    <p>Con Stock</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <h3><i class="fas fa-exclamation-triangle"></i></h3>
                    <h4>
                        <?php 
                        $sin_stock = $conn->query("SELECT COUNT(*) as total FROM articulos WHERE stock = 0")->fetch()['total'];
                        echo $sin_stock;
                        ?>
                    </h4>
                    <p>Sin Stock</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                    <h3><i class="fas fa-users"></i></h3>
                    <h4>
                        <?php 
                        $total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch()['total'];
                        echo $total_usuarios;
                        ?>
                    </h4>
                    <p>Total Usuarios</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-boxes me-2"></i>Gestión de Artículos</h2>
            <div>
                <a href="nuevo_articulo.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Nuevo Artículo
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Lista de Artículos (Total: <?php echo $articulos->rowCount(); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if ($articulos->rowCount() > 0): ?>
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
                                <?php while($articulo = $articulos->fetch()): ?>
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
                                    <td><strong>#<?php echo $articulo['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($articulo['nombre']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo strlen($articulo['descripcion']) > 50 ? 
                                                substr($articulo['descripcion'], 0, 50) . '...' : 
                                                $articulo['descripcion']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            $<?php echo number_format($articulo['precio'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $articulo['stock'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $articulo['stock']; ?> unidades
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('d/m/Y H:i', strtotime($articulo['fecha_creacion'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-warning"
                                               title="Editar artículo">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-danger"
                                               title="Eliminar artículo"
                                               onclick="return confirm('¿Estás seguro de eliminar \'<?php echo htmlspecialchars($articulo['nombre']); ?>\'?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="ver_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-info"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                        <h5>No hay artículos registrados</h5>
                        <p class="text-muted">Comienza agregando tu primer artículo al catálogo.</p>
                        <a href="nuevo_articulo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Agregar Primer Artículo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>