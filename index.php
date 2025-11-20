<?php
// index.php - VERSIÓN MEJORADA PARA IMÁGENES
error_reporting(E_ALL);
ini_set('display_errors', 1);

// INICIAR SESIÓN AL INICIO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('conexion.php');

// Si NO está logueado, redirigir al login
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener artículos de la base de datos
$articulos = [];
try {
    $sql = "SELECT id, nombre, descripcion, precio, imagen, stock FROM articulos WHERE stock > 0 ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("❌ Error al obtener artículos: " . $e->getMessage());
}

// Función para verificar si una imagen es válida
function esImagenValida($ruta_imagen) {
    if (empty($ruta_imagen) || $ruta_imagen === 'NULL') {
        return false;
    }
    
    // Verificar si es una ruta de archivo válida
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $ruta_imagen)) {
        return true;
    }
    
    // Si es texto muy largo, probablemente esté corrupto
    if (strlen($ruta_imagen) > 255) {
        return false;
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌹 Flores de Chinampa - Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
            background: #f8f9fa;
        }
        .product-image-placeholder {
            height: 200px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-direction: column;
        }
        .badge-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .flower-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-seedling me-2"></i>Flores de Chinampa
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    <small class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($_SESSION['rol']); ?></small>
                </span>
                
                <!-- Carrito para clientes -->
                <?php if ($_SESSION['rol'] === 'cliente'): ?>
                    <a href="carrito.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-shopping-cart me-1"></i>Carrito
                        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
                            <span class="badge bg-danger"><?php echo array_sum($_SESSION['carrito']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                
                <!-- Menú para administradores -->
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="gestion_articulos.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-cog me-1"></i>Gestión
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Bienvenida -->
        <div class="welcome-section text-center">
            <h1 class="text-success">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>! 🌸</h1>
            <p class="lead mb-0">Explora nuestro catálogo de flores frescas</p>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Modo <strong>Administrador</strong> - Puedes gestionar productos
                </div>
            <?php endif; ?>
        </div>

        <!-- Catálogo de Productos -->
        <h2 class="mb-4">Nuestras Flores 🌹</h2>
        
        <?php if (empty($articulos)): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No hay productos disponibles en este momento.
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <div class="mt-2">
                        <a href="gestion_articulos.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Agregar Productos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($articulos as $articulo): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card product-card h-100">
                            <!-- Imagen del producto -->
                            <div class="position-relative">
                                <?php 
                                $imagen_valida = esImagenValida($articulo['imagen']);
                                $iconos_flores = ['🌸', '🌹', '🌺', '🌻', '💐', '🥀', '🪷', '🌼'];
                                $icono_aleatorio = $iconos_flores[array_rand($iconos_flores)];
                                ?>
                                
                                <?php if ($imagen_valida): ?>
                                    <img src="<?php echo htmlspecialchars($articulo['imagen']); ?>" 
                                         class="card-img-top product-image" 
                                         alt="<?php echo htmlspecialchars($articulo['nombre']); ?>"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                
                                <!-- Placeholder bonito si no hay imagen válida -->
                                <div class="product-image-placeholder" style="<?php echo ($imagen_valida ? 'display: none;' : ''); ?>">
                                    <div class="flower-icon"><?php echo $icono_aleatorio; ?></div>
                                    <small><?php echo htmlspecialchars($articulo['nombre']); ?></small>
                                </div>
                                
                                <!-- Badge de stock -->
                                <span class="badge <?php echo $articulo['stock'] > 10 ? 'bg-success' : 'bg-warning'; ?> badge-stock">
                                    <?php echo $articulo['stock']; ?> en stock
                                </span>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($articulo['nombre']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($articulo['descripcion']); ?></p>
                                <div class="mt-auto">
                                    <h4 class="text-success mb-3">$<?php echo number_format($articulo['precio'], 2); ?></h4>
                                    
                                    <!-- Botones de acción -->
                                    <div class="d-grid gap-2">
                                        <a href="ver_producto.php?id=<?php echo $articulo['id']; ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ver Detalles
                                        </a>
                                        
                                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                                            <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </a>
                                        <?php else: ?>
                                            <!-- Botón de agregar al carrito FUNCIONAL -->
                                            <form action="agregar_carrito.php" method="POST" class="d-inline">
                                                <input type="hidden" name="id_articulo" value="<?php echo $articulo['id']; ?>">
                                                <input type="hidden" name="cantidad" value="1">
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="fas fa-cart-plus me-1"></i>Agregar al Carrito
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>