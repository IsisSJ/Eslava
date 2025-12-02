<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");

// Parámetros de búsqueda
$busqueda = $_GET['busqueda'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$precio_min = floatval($_GET['precio_min'] ?? 0);
$precio_max = floatval($_GET['precio_max'] ?? 10000);
$orden = $_GET['orden'] ?? 'nombre';

// Construir consulta
$sql = "SELECT * FROM articulos WHERE stock > 0";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($precio_min > 0) {
    $sql .= " AND precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max > 0 && $precio_max < 10000) {
    $sql .= " AND precio <= ?";
    $params[] = $precio_max;
}

// Ordenar
$ordenes_validos = ['nombre', 'precio_asc', 'precio_desc', 'stock'];
if (in_array($orden, $ordenes_validos)) {
    switch ($orden) {
        case 'precio_asc': $sql .= " ORDER BY precio ASC"; break;
        case 'precio_desc': $sql .= " ORDER BY precio DESC"; break;
        case 'stock': $sql .= " ORDER BY stock DESC"; break;
        default: $sql .= " ORDER BY nombre ASC";
    }
}

// Ejecutar consulta
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h2>🌷 Nuestros Productos</h2>
        
        <!-- Filtros y Búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="busqueda" class="form-control" 
                               placeholder="Buscar productos..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <input type="number" name="precio_min" class="form-control" 
                               placeholder="Precio mínimo" 
                               value="<?php echo $precio_min; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <input type="number" name="precio_max" class="form-control" 
                               placeholder="Precio máximo" 
                               value="<?php echo $precio_max; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <select name="orden" class="form-select">
                            <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                            <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                            <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                            <option value="stock" <?php echo $orden == 'stock' ? 'selected' : ''; ?>>Más Stock</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="row">
            <?php if (count($articulos) > 0): ?>
                <?php foreach ($articulos as $articulo): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <!-- Imagen del producto -->
                        <div class="text-center p-3">
                            <?php 
                            if (!empty($articulo['imagen'])) {
                                $base64 = base64_encode($articulo['imagen']);
                                echo "<img src='data:image/jpeg;base64,$base64' 
                                      class='card-img-top' 
                                      alt='" . htmlspecialchars($articulo['nombre']) . "'
                                      style='max-height: 200px; object-fit: contain;'>";
                            } else {
                                echo "<div class='text-center text-muted' style='height: 200px; display: flex; align-items: center; justify-content: center;'>
                                        <i class='fas fa-image fa-4x'></i>
                                      </div>";
                            }
                            ?>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($articulo['nombre']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($articulo['descripcion']); ?></p>
                            <p class="h4 text-success">$<?php echo number_format($articulo['precio'], 2); ?></p>
                            <p class="card-text">
                                <span class="badge bg-<?php echo $articulo['stock'] > 10 ? 'success' : ($articulo['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                    Stock: <?php echo $articulo['stock']; ?> unidades
                                </span>
                            </p>
                            
                            <!-- Formulario para agregar al carrito -->
                            <form method="GET" action="carrito.php" class="mt-3">
                                <input type="hidden" name="accion" value="agregar">
                                <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                                
                                <div class="input-group mb-2">
                                    <input type="number" name="cantidad" class="form-control" 
                                           value="1" min="1" max="<?php echo $articulo['stock']; ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Agregar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>No se encontraron productos</h5>
                        <p>Intenta con otros criterios de búsqueda.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Contador de resultados -->
        <div class="mt-3 text-muted">
            Mostrando <?php echo count($articulos); ?> producto(s)
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>