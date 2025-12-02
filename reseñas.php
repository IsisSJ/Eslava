<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");

// Procesar nueva reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = intval($_POST['producto_id']);
    $calificacion = intval($_POST['calificacion']);
    $comentario = trim($_POST['comentario']);
    
    // Verificar si ya reseñó este producto
    $stmt = $conn->prepare("SELECT id FROM reseñas WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$_SESSION['usuario_id'], $producto_id]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Ya has reseñado este producto";
    } else {
        $stmt = $conn->prepare("INSERT INTO reseñas (usuario_id, producto_id, calificacion, comentario) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['usuario_id'], $producto_id, $calificacion, $comentario])) {
            $_SESSION['mensaje'] = "✅ Reseña enviada. Será revisada por el administrador.";
        } else {
            $_SESSION['error'] = "Error al enviar reseña";
        }
    }
    header("Location: reseñas.php");
    exit();
}

// Obtener reseñas aprobadas del usuario
$stmt = $conn->prepare("
    SELECT r.*, a.nombre as producto_nombre, 
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y') as fecha
    FROM reseñas r
    JOIN articulos a ON r.producto_id = a.id
    WHERE r.usuario_id = ? AND r.estado = 'aprobado'
    ORDER BY r.fecha_creacion DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$reseñas = $stmt->fetchAll();

// Obtener productos comprados por el usuario (para reseñar)
$stmt = $conn->prepare("
    SELECT DISTINCT a.id, a.nombre
    FROM pedido_items pi
    JOIN pedidos p ON pi.pedido_id = p.id
    JOIN articulos a ON pi.producto_id = a.id
    WHERE p.usuario_id = ? AND p.estado = 'entregado'
    AND NOT EXISTS (
        SELECT 1 FROM reseñas r 
        WHERE r.usuario_id = ? AND r.producto_id = a.id
    )
");
$stmt->execute([$_SESSION['usuario_id'], $_SESSION['usuario_id']]);
$productos_sin_reseña = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reseñas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2><i class="fas fa-star me-2"></i>Mis Reseñas</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Formulario para nueva reseña -->
        <?php if (!empty($productos_sin_reseña)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dejar una Reseña</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Producto</label>
                            <select name="producto_id" class="form-select" required>
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($productos_sin_reseña as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Calificación</label>
                            <div class="star-rating">
                                <div class="d-flex">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" 
                                               name="calificacion" id="star<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required>
                                        <label class="form-check-label" for="star<?php echo $i; ?>">
                                            <i class="fas fa-star text-warning"></i>
                                        </label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Comentario</label>
                        <textarea name="comentario" class="form-control" rows="3" 
                                  placeholder="Comparte tu experiencia con este producto..." 
                                  maxlength="500"></textarea>
                        <small class="text-muted">Máximo 500 caracteres</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Reseña</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Reseñas existentes -->
        <h4 class="mb-3">Mis Reseñas Publicadas</h4>
        <?php if (empty($reseñas)): ?>
            <div class="alert alert-info">
                <p class="mb-0">No has publicado ninguna reseña aún.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($reseñas as $reseña): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($reseña['producto_nombre']); ?></h6>
                                <small class="text-muted"><?php echo $reseña['fecha']; ?></small>
                            </div>
                            <div class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $reseña['calificacion'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($reseña['comentario'])); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>