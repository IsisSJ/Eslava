<?php
session_start();
include_once("conexion.php");

// Obtener todas las categorías
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

// Contar productos por categoría
$stmt = $conn->prepare("
    SELECT c.id, COUNT(a.id) as total_productos
    FROM categorias c
    LEFT JOIN articulos a ON c.id = a.categoria_id AND a.stock > 0
    GROUP BY c.id
");
$stmt->execute();
$contador = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2><i class="fas fa-tags me-2"></i>Nuestras Categorías</h2>
        <p class="text-muted">Explora nuestras colecciones de flores</p>
        
        <div class="row">
            <?php foreach ($categorias as $categoria): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($categoria['imagen'])): ?>
                                <?php 
                                $base64 = base64_encode($categoria['imagen']);
                                echo "<img src='data:image/jpeg;base64,$base64' 
                                      style='max-height: 100px; max-width: 100%; object-fit: contain;'
                                      alt='" . htmlspecialchars($categoria['nombre']) . "'>";
                                ?>
                            <?php else: ?>
                                <i class="fas fa-leaf fa-4x text-success"></i>
                            <?php endif; ?>
                        </div>
                        <h4 class="card-title"><?php echo htmlspecialchars($categoria['nombre']); ?></h4>
                        <p class="card-text text-muted">
                            <?php echo htmlspecialchars($categoria['descripcion']); ?>
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">
                                <?php echo $contador[$categoria['id']] ?? 0; ?> productos
                            </span>
                        </div>
                        <a href="articulos.php?categoria=<?php echo $categoria['id']; ?>" 
                           class="btn btn-outline-success mt-3">
                            Ver Productos
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>