<?php
// cliente_dashboard.php - Dashboard para clientes
require_once 'config_session.php';

// Verificar que sea cliente
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_definitivo.php");
    exit();
}

if ($_SESSION['usuario_rol'] !== 'cliente') {
    // Si es admin, redirigir al dashboard admin
    if ($_SESSION['usuario_rol'] === 'admin') {
        header("Location: admin.php");
        exit();
    }
    die("Acceso no autorizado");
}

require_once 'conexion.php';

// Obtener información del cliente
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT nombre_usuario, correo, telecomercio FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$cliente = $stmt->fetch();

// Obtener últimos pedidos
$stmt = $conn->prepare("
    SELECT p.*, 
           COUNT(pi.id) as items,
           DATE_FORMAT(p.fecha_creacion, '%d/%m/%Y') as fecha
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    WHERE p.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.fecha_creacion DESC
    LIMIT 5
");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include('header_cliente.php'); ?>
    
    <div class="container mt-4">
        <!-- Perfil del cliente -->
        <div class="profile-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-user-circle me-2"></i>Hola, <?php echo htmlspecialchars($cliente['nombre_usuario']); ?>!</h2>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($cliente['correo']); ?></p>
                    <?php if ($cliente['telecomercio']): ?>
                        <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($cliente['telecomercio']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark fs-6 p-2">
                        <i class="fas fa-user-tag me-1"></i>Cliente
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <h4><?php echo count($pedidos); ?></h4>
                    <p class="text-muted">Pedidos Realizados</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-heart text-danger"></i>
                    <h4>0</h4>
                    <p class="text-muted">Favoritos</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-star text-warning"></i>
                    <h4>0</h4>
                    <p class="text-muted">Reseñas</p>
                </div>
            </div>
        </div>
        
        <!-- Menú de acciones -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-store me-2"></i>Comprar</h5>
                    </div>
                    <div class="card-body">
                        <p>Explora nuestro catálogo de flores y realiza tus compras.</p>
                        <a href="articulos.php" class="btn btn-success w-100">
                            <i class="fas fa-shopping-bag me-2"></i>Ver Catálogo
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Mis Pedidos</h5>
                    </div>
                    <div class="card-body">
                        <p>Revisa el estado de tus pedidos y tu historial de compras.</p>
                        <a href="mis_pedidos.php" class="btn btn-info w-100">
                            <i class="fas fa-history me-2"></i>Ver Mis Pedidos
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimos pedidos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Últimos Pedidos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pedidos)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>No has realizado pedidos aún</h5>
                        <p class="text-muted">Realiza tu primera compra en nuestro catálogo</p>
                        <a href="articulos.php" class="btn btn-primary">Ir al Catálogo</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Items</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                    <td><?php echo $pedido['fecha']; ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($pedido['estado']) {
                                                case 'pendiente': echo 'warning'; break;
                                                case 'procesando': echo 'info'; break;
                                                case 'enviado': echo 'primary'; break;
                                                case 'entregado': echo 'success'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($pedido['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $pedido['items']; ?> productos</td>
                                    <td>
                                        <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="mis_pedidos.php" class="btn btn-outline-primary">
                            Ver todos mis pedidos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>