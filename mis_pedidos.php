<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");

// Obtener pedidos del usuario
$stmt = $conn->prepare("
    SELECT p.*, 
           COUNT(pi.id) as items,
           DATE_FORMAT(p.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_formateada
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    WHERE p.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.fecha_creacion DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2><i class="fas fa-clipboard-list me-2"></i>Mis Pedidos</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info text-center py-4">
                <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                <h5>No tienes pedidos aún</h5>
                <p>Realiza tu primera compra en nuestro catálogo.</p>
                <a href="articulos.php" class="btn btn-primary">Ir al Catálogo</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($pedidos as $pedido): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-<?php 
                        switch($pedido['estado']) {
                            case 'pendiente': echo 'warning'; break;
                            case 'procesando': echo 'info'; break;
                            case 'enviado': echo 'primary'; break;
                            case 'entregado': echo 'success'; break;
                            case 'cancelado': echo 'danger'; break;
                            default: echo 'secondary';
                        }
                    ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pedido #<?php echo $pedido['id']; ?></h5>
                            <span class="badge bg-<?php 
                                switch($pedido['estado']) {
                                    case 'pendiente': echo 'warning'; break;
                                    case 'procesando': echo 'info'; break;
                                    case 'enviado': echo 'primary'; break;
                                    case 'entregado': echo 'success'; break;
                                    case 'cancelado': echo 'danger'; break;
                                    default: echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($pedido['estado']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small class="text-muted">Fecha</small>
                                    <p class="mb-0"><strong><?php echo $pedido['fecha_formateada']; ?></strong></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Productos</small>
                                    <p class="mb-0"><strong><?php echo $pedido['items']; ?> items</strong></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <small class="text-muted">Total</small>
                                    <h4 class="text-success">$<?php echo number_format($pedido['total'], 2); ?></h4>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Ver Detalle
                                </a>
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                    <a href="cancelar_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('¿Cancelar este pedido?')">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>