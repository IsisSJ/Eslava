<?php
// articulos.php
require_once 'config_session.php';
require_once 'conexion.php';

// Obtener productos
try {
    $stmt = $conn->query("SELECT * FROM articulos WHERE estado = 'activo' ORDER BY id DESC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar productos: " . $e->getMessage());
}
?>

<?php include('header.php'); ?>

<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-bag"></i> Nuestros Productos</h2>
    
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($productos as $producto): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <img src="uploads/<?php echo htmlspecialchars($producto['imagen'] ?? 'default.jpg'); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                     style="height: 200px; object-fit: cover;">
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                    <p class="card-text text-muted small">
                        <?php echo substr(htmlspecialchars($producto['descripcion']), 0, 80); ?>...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 text-success mb-0">$<?php echo number_format($producto['precio'], 2); ?></span>
                        <span class="badge bg-secondary">Stock: <?php echo $producto['stock']; ?></span>
                    </div>
                </div>
                
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2">
                        <a href="detalle_producto.php?id=<?php echo $producto['id']; ?>" 
                           class="btn btn-outline-primary">
                           <i class="bi bi-eye"></i> Ver Detalle
                        </a>
                        <a href="carrito.php?accion=agregar&id=<?php echo $producto['id']; ?>&cantidad=1" 
                           class="btn btn-success <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>"
                           onclick="<?php echo ($producto['stock'] <= 0) ? 'return false;' : 'return confirm(\'¿Agregar al carrito?\');'; ?>">
                            <i class="bi bi-cart-plus"></i> 
                            <?php echo ($producto['stock'] <= 0) ? 'Sin Stock' : 'Agregar al Carrito'; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('footer.php'); ?>