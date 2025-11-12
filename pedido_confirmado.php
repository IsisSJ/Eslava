<?php
session_start();

// Verificar que hay un pedido exitoso
if (!isset($_SESSION['pedido_exitoso'])) {
    header('Location: carrito.php');
    exit();
}

$pedido_info = $_SESSION['pedido_exitoso'];
$pedido_id = $pedido_info['pedido_id'];
$total = $pedido_info['total'];
$metodo_pago = $pedido_info['metodo_pago'];

// Mantener mensaje si existe
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';

// Limpiar sesiones después de mostrarlas
unset($_SESSION['pedido_exitoso']);
unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 80px;
        }
        .confirmation-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card confirmation-card">
                    <div class="success-header">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                        <h1 class="display-5 fw-bold">¡Pedido Confirmado!</h1>
                        <p class="lead mb-0">Tu pedido ha sido procesado exitosamente</p>
                    </div>
                    
                    <div class="card-body p-5">
                        <!-- Mostrar mensaje de email -->
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading">
                                    <?php echo $tipo_mensaje === 'error' ? '❌ Error' : '✅ Éxito'; ?>
                                </h4>
                                <?php echo $mensaje; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                
                                <?php if ($tipo_mensaje === 'error'): ?>
                                <hr>
                                <p class="mb-0">
                                    <small>
                                        <strong>Solución:</strong><br>
                                        1. Verifica que tu correo Gmail esté configurado correctamente<br>
                                        2. Usa la contraseña de aplicación, no tu contraseña normal<br>
                                        3. Revisa la carpeta de spam<br>
                                        4. Tu pedido fue procesado igualmente
                                    </small>
                                </p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h4 class="alert-heading">📦 Pedido Procesado</h4>
                                Tu pedido fue procesado correctamente. Si no recibiste el email, revisa tu carpeta de spam.
                            </div>
                        <?php endif; ?>

                        <!-- Información del pedido -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="text-success mb-4">
                                    <i class="fas fa-receipt me-2"></i>Resumen del Pedido
                                </h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Número de Pedido:</strong> <span class="badge bg-primary fs-6">#<?php echo $pedido_id; ?></span></p>
                                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                                        <p><strong>Método de Pago:</strong> <span class="text-capitalize"><?php echo $metodo_pago; ?></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total:</strong></p>
                                        <h3 class="text-success">$<?php echo number_format($total, 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="text-center mt-4">
                            <div class="btn-group" role="group">
                                <a href="productos.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-shopping-bag me-2"></i>Seguir Comprando
                                </a>
                                <a href="mis_pedidos.php" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-clipboard-list me-2"></i>Ver Mis Pedidos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>