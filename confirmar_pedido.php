<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté logueado y tenga productos en el carrito
if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}

$metodo_pago = $_GET['metodo'] ?? 'efectivo';
$carrito = $_SESSION['carrito'];
$total = 0;

foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

$iva = $total * 0.16;
$total_con_iva = $total + $iva;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Pedido - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { margin-top: 80px; }
        .producto-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Confirmar Pedido</h4>
                    </div>
                    <div class="card-body">
                        <!-- Resumen de productos -->
                        <h5>Productos en tu pedido:</h5>
                        <?php foreach ($carrito as $item): ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-2">
                                <?php if (!empty($item['imagen'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($item['imagen']); ?>" 
                                         class="producto-img" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                <?php else: ?>
                                    <div class="producto-img bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-6">
                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                <br>
                                <small class="text-muted">Cantidad: <?php echo $item['cantidad']; ?></small>
                            </div>
                            <div class="col-4 text-end">
                                <span class="fw-bold">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Resumen de pago -->
                        <div class="border-top pt-3">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Subtotal:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    $<?php echo number_format($total, 2); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <strong>IVA (16%):</strong>
                                </div>
                                <div class="col-6 text-end">
                                    $<?php echo number_format($iva, 2); ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <h5>Total:</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <h5 class="text-success">$<?php echo number_format($total_con_iva, 2); ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Método de pago -->
                        <div class="mt-4">
                            <h5>Método de Pago:</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-credit-card me-2"></i>
                                <strong>
                                    <?php 
                                    echo $metodo_pago === 'tarjeta' 
                                        ? 'Pago con Tarjeta' 
                                        : 'Pago en Efectivo';
                                    ?>
                                </strong>
                            </div>
                        </div>

<!-- Botones de acción -->
<!-- Botones de acción -->
<div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
    <a href="carrito.php" class="btn btn-secondary me-md-2">
        <i class="fas fa-arrow-left me-2"></i>Volver al Carrito
    </a>
    <button onclick="confirmarPedido()" class="btn btn-success me-md-2">
        <i class="fas fa-check me-2"></i>Confirmar Pedido
    </button>
    <button onclick="generarPDF()" class="btn btn-outline-primary me-md-2">
        <i class="fas fa-file-pdf me-2"></i>Descargar PDF
    </button>
    <!-- BOTÓN NUEVO PARA EMAIL -->
    <button onclick="enviarEmail()" class="btn btn-outline-info">
        <i class="fas fa-envelope me-2"></i>Enviar por Email
    </button>
</div>

<script>
function enviarEmail() {
    if (confirm('¿Quieres recibir el comprobante en tu correo electrónico?')) {
        window.location.href = 'enviar_ticket.php?metodo=<?php echo $metodo_pago; ?>';
    }
}
</script>
</body>
</html>