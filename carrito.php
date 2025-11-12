<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener items del carrito desde la sesión
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compra - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 80px;
        }
        .producto-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #28a745;
        }
        .payment-method.selected {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        .card-details {
            display: none;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="row">
            <!-- Resumen del Pedido -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Carrito de Compra</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($carrito)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h5>Tu carrito está vacío</h5>
                                <p>Agrega algunos productos para continuar</p>
                                <a href="productos.php" class="btn btn-success">Ver Productos</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($carrito as $item): ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-md-2">
                                    <?php if (!empty($item['imagen'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($item['imagen']); ?>" 
                                             class="producto-img" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                    <?php else: ?>
                                        <div class="producto-img bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                    <p class="text-muted mb-1">Código #<?php echo $item['id']; ?></p>
                                    <p class="text-success mb-0">
                                        <small>Stock disponible: <?php echo $item['stock']; ?></small>
                                    </p>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <span class="fw-bold text-success">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                                    <button class="btn btn-sm btn-outline-danger mt-1" onclick="eliminarDelCarrito(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php 
                                $total += $item['precio'] * $item['cantidad'];
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-method" onclick="selectPaymentMethod('efectivo')">
                            <input type="radio" name="metodo_pago" value="efectivo" id="efectivo" class="me-2">
                            <label for="efectivo" class="fw-bold">
                                <i class="fas fa-money-bill-wave me-2"></i>Pago en Efectivo
                            </label>
                            <p class="text-muted mb-0 mt-1">Paga cuando recibas tu pedido</p>
                        </div>

                        <div class="payment-method" onclick="selectPaymentMethod('tarjeta')">
                            <input type="radio" name="metodo_pago" value="tarjeta" id="tarjeta" class="me-2">
                            <label for="tarjeta" class="fw-bold">
                                <i class="fas fa-credit-card me-2"></i>Pago con Tarjeta
                            </label>
                            <p class="text-muted mb-0 mt-1">Pago seguro con tarjeta de crédito/débito</p>
                        </div>

                        <!-- Formulario de Tarjeta (se muestra solo cuando se selecciona tarjeta) -->
                        <div id="tarjetaDetails" class="card-details">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Número de Tarjeta</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" 
                                               oninput="formatCardNumber(this)">
                                        <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha de Expiración</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="MM/AA" maxlength="5" 
                                               oninput="formatExpiryDate(this)">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CVV</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="123" maxlength="3" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Nombre en la Tarjeta</label>
                                    <input type="text" class="form-control" placeholder="JUAN PEREZ GARCIA">
                                </div>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="saveCard">
                                <label class="form-check-label" for="saveCard">
                                    Guardar información de tarjeta para futuras compras
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen del Pedido -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Productos (<?php echo count($carrito); ?>):</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío:</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (16%):</span>
                            <span>$<?php echo number_format($total * 0.16, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-success fs-5">$<?php echo number_format($total * 1.16, 2); ?></strong>
                        </div>
                        
                        <button class="btn btn-success w-100 py-2" onclick="procesarPago()">
                            <i class="fas fa-lock me-2"></i>Proceder al Pago
                        </button>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Pago 100% seguro
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Información de Envío -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-truck me-2"></i>Información de Envío</h6>
                        <ul class="list-unstyled small text-muted">
                            <li><i class="fas fa-check text-success me-1"></i> Envío gratis en compras mayores a $500</li>
                            <li><i class="fas fa-check text-success me-1"></i> Tiempo de entrega: 2-3 días hábiles</li>
                            <li><i class="fas fa-check text-success me-1"></i> Solo entregas en CDMX y área metropolitana</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPaymentMethod(method) {
            // Remover selección de todos los métodos
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Seleccionar el método clickeado
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[value="${method}"]`).checked = true;
            
            // Mostrar/ocultar detalles de tarjeta
            const cardDetails = document.getElementById('tarjetaDetails');
            if (method === 'tarjeta') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }

        function formatCardNumber(input) {
            // Remover todos los espacios existentes
            let value = input.value.replace(/\s+/g, '');
            
            // Agregar espacio cada 4 dígitos
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            
            // Limitar a 16 dígitos + 3 espacios = 19 caracteres
            if (value.length > 19) {
                value = value.substring(0, 19);
            }
            
            input.value = value;
        }

        function formatExpiryDate(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            
            input.value = value;
        }

        function eliminarDelCarrito(productoId) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                // Aquí iría la lógica para eliminar del carrito
                alert('Producto eliminado del carrito');
                location.reload();
            }
        }

        function procesarPago() {
            const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
            
            if (!metodoPago) {
                alert('Por favor selecciona un método de pago');
                return;
            }

            if (metodoPago.value === 'tarjeta') {
                // Validar datos de tarjeta
                const cardNumber = document.querySelector('input[placeholder="1234 5678 9012 3456"]').value;
                const expiryDate = document.querySelector('input[placeholder="MM/AA"]').value;
                const cvv = document.querySelector('input[placeholder="123"]').value;
                const cardName = document.querySelector('input[placeholder="JUAN PEREZ GARCIA"]').value;

                if (!cardNumber || cardNumber.replace(/\s/g, '').length !== 16) {
                    alert('Por favor ingresa un número de tarjeta válido (16 dígitos)');
                    return;
                }

                if (!expiryDate || expiryDate.length !== 5) {
                    alert('Por favor ingresa una fecha de expiración válida (MM/AA)');
                    return;
                }

                if (!cvv || cvv.length !== 3) {
                    alert('Por favor ingresa un CVV válido (3 dígitos)');
                    return;
                }

                if (!cardName) {
                    alert('Por favor ingresa el nombre que aparece en la tarjeta');
                    return;
                }
            }

            // Redirigir a confirmación de pedido
            window.location.href = 'confirmar_pedido.php?metodo=' + metodoPago.value;
        }
    </script>
</body>
</html>