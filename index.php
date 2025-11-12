<?php
// index.php - Página principal PARA TODOS LOS USUARIOS
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión
include_once('conexion.php');

// Si NO está logueado, redirigir al login
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener artículos para mostrar en el catálogo
$articulos = [];
try {
    $sql = "SELECT id, nombre, descripcion, precio, imagen, stock FROM articulos WHERE stock > 0 ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener artículos: " . $e->getMessage());
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
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .badge-stock {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-seedling me-2"></i>Flores de Chinampa
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    <small class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($_SESSION['rol']); ?></small>
                </span>
                
                <!-- Menú para administradores -->
                <?php if ($_SESSION['rol'] === 'administrador'): ?>
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
            <?php if ($_SESSION['rol'] === 'administrador'): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Modo <strong>Administrador</strong> - Puedes gestionar productos desde el botón superior
                </div>
            <?php endif; ?>
        </div>

        <!-- Catálogo de Productos -->
        <h2 class="mb-4">Nuestras Flores 🌹</h2>
        
        <?php if (empty($articulos)): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No hay productos disponibles en este momento.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($articulos as $articulo): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card product-card h-100">
                            <!-- Imagen del producto -->
                            <div class="position-relative">
                                <?php if (!empty($articulo['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($articulo['imagen']); ?>" 
                                         class="card-img-top product-image" 
                                         alt="<?php echo htmlspecialchars($articulo['nombre']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
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
                                        
                                        <?php if ($_SESSION['rol'] === 'administrador'): ?>
                                            <a href="editar_articulo.php?id=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-success btn-sm">
                                                <i class="fas fa-cart-plus me-1"></i>Agregar al Carrito
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Información adicional para clientes -->
        <?php if ($_SESSION['rol'] !== 'administrador'): ?>
            <div class="row mt-5">
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-shipping-fast fa-2x text-success mb-3"></i>
                            <h5>Envío Rápido</h5>
                            <p class="text-muted">Recibe tus flores en 24-48 horas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-award fa-2x text-success mb-3"></i>
                            <h5>Calidad Garantizada</h5>
                            <p class="text-muted">Flores frescas directo de chinampa</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-headset fa-2x text-success mb-3"></i>
                            <h5>Soporte 24/7</h5>
                            <p class="text-muted">Estamos aquí para ayudarte</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Flores de Chinampa. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>