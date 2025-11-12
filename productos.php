<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté autenticado (cualquier rol puede ver productos)
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener productos disponibles
$productos = $conn->query("SELECT * FROM articulos WHERE stock > 0 ORDER BY fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 80px;
        }
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .product-img-container {
            height: 200px;
            overflow: hidden;
            border-radius: 15px 15px 0 0;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: cover;
        }
        .product-price {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card-text {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>🌸 Nuestras Flores</h1>
            <div>
                <?php if ($_SESSION['rol'] === 'cliente'): ?>
                    <a href="carrito.php" class="btn btn-success position-relative me-2">
                        <i class="fas fa-shopping-cart"></i> Carrito
                        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php 
                                $total_items = 0;
                                if (isset($_SESSION['carrito'])) {
                                    foreach ($_SESSION['carrito'] as $item) {
                                        $total_items += $item['cantidad'];
                                    }
                                }
                                echo $total_items;
                                ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo ($_SESSION['rol'] === 'admin') ? 'admin_dashboard.php' : 'menu.php'; ?>" class="btn btn-secondary">
                    ← Volver
                </a>
            </div>
        </div>

        <div class="row">
            <?php if ($productos->num_rows > 0): ?>
                <?php while($producto = $productos->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card product-card">
                        <div class="product-img-container position-relative">
                            <?php if (!empty($producto['imagen'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($producto['imagen']); ?>" 
                                     class="product-img" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="stock-badge badge bg-<?php echo $producto['stock'] > 5 ? 'success' : ($producto['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                <?php echo $producto['stock']; ?> disp.
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                            <p class="card-text text-muted small">
                                <?php 
                                $descripcion = $producto['descripcion'];
                                echo strlen($descripcion) > 80 ? 
                                    substr($descripcion, 0, 80) . '...' : 
                                    $descripcion; 
                                ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="product-price">$<?php echo number_format($producto['precio'], 2); ?></span>
                                
                                <?php if ($_SESSION['rol'] === 'cliente' && $producto['stock'] > 0): ?>
                                    <a href="agregar_carrito.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-primary btn-sm"
                                       onclick="agregarAlCarrito(event, <?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>')">
                                        <i class="fas fa-cart-plus"></i> Agregar
                                    </a>
                                <?php elseif ($_SESSION['rol'] === 'cliente'): ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-times"></i> Sin Stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No hay productos disponibles</h4>
                        <p>Pronto tendremos nuevas flores en stock.</p>
                        <a href="menu.php" class="btn btn-primary">Volver al Menú</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function agregarAlCarrito(event, id, nombre) {
            event.preventDefault();
            
            // Mostrar notificación
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong class="me-auto">Producto agregado</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        <strong>${nombre}</strong> agregado al carrito
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Hacer la petición AJAX para agregar al carrito
            fetch(`agregar_carrito.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar contador del carrito
                        if (data.carrito_count > 0) {
                            const carritoBadge = document.querySelector('.btn-success .badge');
                            if (carritoBadge) {
                                carritoBadge.textContent = data.carrito_count;
                            } else {
                                // Crear badge si no existe
                                const btnCarrito = document.querySelector('.btn-success');
                                btnCarrito.innerHTML += `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">${data.carrito_count}</span>`;
                            }
                        }
                    } else {
                        // Mostrar error
                        toast.querySelector('.toast-header').className = 'toast-header bg-danger text-white';
                        toast.querySelector('.toast-body').innerHTML = `<strong>Error:</strong> ${data.message}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toast.querySelector('.toast-header').className = 'toast-header bg-danger text-white';
                    toast.querySelector('.toast-body').innerHTML = '<strong>Error:</strong> No se pudo agregar al carrito';
                });
            
            // Eliminar toast después de 3 segundos
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html>