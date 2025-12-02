<?php
// cliente_dashboard.php - Dashboard ESPECÍFICO para clientes
require_once 'config_session.php';

// Verificar si el usuario está logueado (cualquier usuario)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirigir al login de CLIENTES, no al admin
    header("Location: login.php");
    exit();
}

// OPCIONAL: Si quieres que SOLO clientes normales accedan (no admins)
// Puedes permitir a todos los usuarios logueados o filtrar por rol
/*
if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    // Los admins tienen su propio dashboard
    header("Location: admin_dashboard.php");
    exit();
}
*/

require_once 'conexion.php';

// Obtener datos del usuario actual
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Cliente';

// Obtener pedidos del cliente
$pedidos = [];
$total_pedidos = 0;

if ($usuario_id > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, COUNT(d.id) as items, SUM(d.cantidad * d.precio_unitario) as total
            FROM pedidos p
            LEFT JOIN pedidos_detalle d ON p.id = d.pedido_id
            WHERE p.usuario_id = ?
            GROUP BY p.id
            ORDER BY p.fecha_creacion DESC
            LIMIT 5
        ");
        $stmt->execute([$usuario_id]);
        $pedidos = $stmt->fetchAll();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $total_pedidos = $stmt->fetch()['total'];
    } catch (PDOException $e) {
        // Error silencioso o mostrar mensaje
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Dashboard Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .dashboard-card {
            border-radius: 15px;
            transition: transform 0.3s;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .welcome-section {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Header Específico para Clientes -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="cliente_dashboard.php">
                <i class="bi bi-person-circle"></i> Mi Cuenta
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCliente">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarCliente">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="articulos.php"><i class="bi bi-bag"></i> Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php"><i class="bi bi-cart3"></i> Carrito</a>
                    </li>
                </ul>
                
                <div class="navbar-text text-white">
                    <span class="me-3">Hola, <?php echo htmlspecialchars($usuario_nombre); ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Sección de Bienvenida -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-emoji-smile"></i> ¡Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?>!</h1>
                    <p class="lead">Gestiona tus pedidos, actualiza tu información y descubre nuevas ofertas.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white text-dark rounded p-3 d-inline-block">
                        <small class="text-muted">Miembro desde</small>
                        <h4 class="mb-0"><?php echo date('M Y', strtotime($_SESSION['fecha_registro'] ?? 'now')); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-check display-4 text-primary mb-3"></i>
                        <h3><?php echo $total_pedidos; ?></h3>
                        <p class="text-muted mb-0">Pedidos Totales</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history display-4 text-success mb-3"></i>
                        <h3><?php echo count(array_filter($pedidos, function($p) { return $p['estado'] === 'pendiente'; })); ?></h3>
                        <p class="text-muted mb-0">Pedidos Pendientes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-truck display-4 text-warning mb-3"></i>
                        <h3><?php echo count(array_filter($pedidos, function($p) { return $p['estado'] === 'enviado'; })); ?></h3>
                        <p class="text-muted mb-0">En Camino</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card border-info">
                    <div class="card-body text-center">
                        <a href="carrito.php" class="text-decoration-none">
                            <i class="bi bi-cart3 display-4 text-info mb-3"></i>
                            <h3><?php echo isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0; ?></h3>
                            <p class="text-muted mb-0">Items en Carrito</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="row">
            <!-- Mis Pedidos Recientes -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Mis Pedidos Recientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pedidos)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x display-1 text-muted"></i>
                                <h5 class="mt-3">No tienes pedidos aún</h5>
                                <p class="text-muted">¡Realiza tu primera compra!</p>
                                <a href="articulos.php" class="btn btn-primary">Ver Productos</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th># Pedido</th>
                                            <th>Fecha</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_creacion'])); ?></td>
                                            <td><?php echo $pedido['items']; ?></td>
                                            <td class="fw-bold">$<?php echo number_format($pedido['total'] ?? 0, 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    switch($pedido['estado']) {
                                                        case 'completado': echo 'success'; break;
                                                        case 'pendiente': echo 'warning'; break;
                                                        case 'enviado': echo 'info'; break;
                                                        case 'cancelado': echo 'danger'; break;
                                                        default: echo 'secondary';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="mis_pedidos.php" class="btn btn-outline-primary">Ver Todos los Pedidos</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="articulos.php" class="btn btn-outline-success btn-lg">
                                <i class="bi bi-bag-plus"></i> Continuar Comprando
                            </a>
                            <a href="carrito.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-cart-check"></i> Ver Mi Carrito
                            </a>
                            <a href="editar_perfil.php" class="btn btn-outline-info">
                                <i class="bi bi-person-gear"></i> Editar Perfil
                            </a>
                            <a href="direcciones.php" class="btn btn-outline-warning">
                                <i class="bi bi-geo-alt"></i> Mis Direcciones
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ofertas Especiales -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-star"></i> Recomendados Para Ti</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Envío Gratis</h6>
                                    <small>Hoy</small>
                                </div>
                                <p class="mb-1">En compras mayores a $50.000</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">10% Descuento</h6>
                                    <small>Válido hasta 31/12</small>
                                </div>
                                <p class="mb-1">Usa el código: BIENVENIDO10</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-shop"></i> Mi Tienda Online</h5>
                    <p class="mb-0">© 2024 Todos los derechos reservados</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">
                        <i class="bi bi-headset"></i> Soporte: soporte@mitienda.com<br>
                        <i class="bi bi-telephone"></i> +57 300 123 4567
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar hora local
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = 
                now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>