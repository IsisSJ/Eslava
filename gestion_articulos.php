<?php
session_start();
include_once("conexion.php");

// CONFIGURACIÓN PARA RENDER
$CONFIG = [
    'dominio_produccion' => 'https://eslava-3.onrender.com', // ← Tu URL real de Render
    'modo_desarrollo' => false
];

// Verificar que el usuario esté autenticado como administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Función para generar URL correcta del producto - VERSIÓN MEJORADA
function generarURLProducto($articulo_id) {
    global $CONFIG;
    
    // DEBUG: Verificar entorno
    error_log("🔍 Generando URL para producto $articulo_id");
    error_log("🔍 Dominio configurado: " . $CONFIG['dominio_produccion']);
    error_log("🔍 Modo desarrollo: " . ($CONFIG['modo_desarrollo'] ? 'true' : 'false'));
    error_log("🔍 HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'No definido'));
    
    // SIEMPRE usar el dominio de producción en Render
    // Render tiene variables de entorno específicas
    if (isset($_SERVER['RENDER']) || 
        $_SERVER['HTTP_HOST'] === 'eslava-3.onrender.com' ||
        !$CONFIG['modo_desarrollo']) {
        
        $url = $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $articulo_id;
        error_log("✅ URL generada (Producción): " . $url);
        return $url;
    }
    
    // Solo para desarrollo local (XAMPP, localhost, etc.)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Limpiar el host (remover puerto si existe)
    $host = preg_replace('/:\d+$/', '', $host);
    
    // Evitar localhost, 127.0.0.1, ::1
    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
        error_log("⚠️  Detectado localhost, forzando dominio de producción");
        return $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $articulo_id;
    }
    
    $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $url = $protocol . "://" . $host . $base_path . "/ver_producto.php?id=" . $articulo_id;
    
    error_log("🔧 URL generada (Desarrollo): " . $url);
    return $url;
}

// Procesar mensajes
$mensaje = '';
$tipo_mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Generar QR para un artículo específico
if (isset($_GET['generar_qr'])) {
    $articulo_id = $_GET['generar_qr'];
    
    // Obtener información del artículo
    $stmt = $conn->prepare("SELECT id, nombre FROM articulos WHERE id = ?");
    $stmt->execute([$articulo_id]);
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($articulo) {
        // ✅ URL CORRECTA para Render
        $qr_url = generarURLProducto($articulo_id);
        
        // Generar código QR usando API
        $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_url);
        
        // Guardar la URL del QR en la base de datos
        $update_stmt = $conn->prepare("UPDATE articulos SET qr_code = ? WHERE id = ?");
        if ($update_stmt->execute([$qr_code_url, $articulo_id])) {
            $_SESSION['mensaje'] = "✅ Código QR generado para: " . $articulo['nombre'];
            $_SESSION['tipo_mensaje'] = "success";
            
            // Mostrar URL generada en modo debug
            if (isset($_GET['debug'])) {
                $_SESSION['mensaje'] .= "<br>🌐 URL: " . $qr_url;
            }
        } else {
            $_SESSION['mensaje'] = "❌ Error al generar QR";
            $_SESSION['tipo_mensaje'] = "error";
        }
    }
    
    header('Location: gestion_articulos.php');
    exit();
}

// Eliminar artículo
if (isset($_GET['eliminar'])) {
    $articulo_id = $_GET['eliminar'];
    
    // Primero obtener información del artículo para el mensaje
    $stmt = $conn->prepare("SELECT nombre FROM articulos WHERE id = ?");
    $stmt->execute([$articulo_id]);
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($articulo) {
        // Eliminar el artículo
        $delete_stmt = $conn->prepare("DELETE FROM articulos WHERE id = ?");
        if ($delete_stmt->execute([$articulo_id])) {
            $_SESSION['mensaje'] = "✅ Artículo eliminado: " . $articulo['nombre'];
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al eliminar artículo";
            $_SESSION['tipo_mensaje'] = "error";
        }
    }
    
    header('Location: gestion_articulos.php');
    exit();
}

// Obtener artículos con la nueva estructura
$sql = "SELECT id, nombre, precio, stock, descripcion, imagen_path, qr_code, visitas, fecha_creacion 
        FROM articulos 
        ORDER BY visitas DESC, id DESC";
$stmt = $conn->query($sql);
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas para los cards
$total_articulos = count($articulos);

// Contar stock disponible
$stock_stmt = $conn->query("SELECT COUNT(*) as count FROM articulos WHERE stock > 0");
$stock_count = $stock_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total visitas
$visitas_stmt = $conn->query("SELECT SUM(visitas) as total FROM articulos");
$total_visitas = $visitas_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

// QR generados
$qr_stmt = $conn->query("SELECT COUNT(*) as count FROM articulos WHERE qr_code IS NOT NULL");
$qr_count = $qr_stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Artículos - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .container {
            margin-top: 80px;
        }
        .table-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .table-img:hover {
            transform: scale(1.1);
        }
        .qr-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #28a745;
            cursor: pointer;
        }
        .btn-action {
            margin: 2px;
            font-size: 0.8rem;
        }
        .stock-badge {
            font-size: 0.8em;
        }
        .visitas-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        }
        .product-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .product-price {
            color: #27ae60;
            font-weight: bold;
        }
        .card-stat {
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .config-info {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
            .btn-action {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-success">
                <i class="fas fa-boxes me-2"></i>Gestión de Artículos
            </h1>
            <div>
                <a href="nuevo_articulo.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nuevo Artículo
                </a>
                <a href="gestion_usuarios.php" class="btn btn-outline-primary">
                    <i class="fas fa-users me-2"></i>Usuarios
                </a>
            </div>
        </div>

        <!-- Información de Configuración Render -->
        <div class="config-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-cloud me-2"></i>Configurado para Render</h5>
                    <p class="mb-0">Dominio: <strong><?php echo $CONFIG['dominio_produccion']; ?></strong></p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-check-circle me-1"></i>QR Funcionales
                    </span>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $tipo_mensaje === 'error' ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary card-stat h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h5 class="card-title">Total Artículos</h5>
                        <h2 class="mb-0"><?php echo $total_articulos; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success card-stat h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-cubes fa-2x mb-2"></i>
                        <h5 class="card-title">En Stock</h5>
                        <h2 class="mb-0"><?php echo $stock_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning card-stat h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x mb-2"></i>
                        <h5 class="card-title">Total Visitas</h5>
                        <h2 class="mb-0"><?php echo $total_visitas; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info card-stat h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-qrcode fa-2x mb-2"></i>
                        <h5 class="card-title">QR Generados</h5>
                        <h2 class="mb-0"><?php echo $qr_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de artículos -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Artículos
                </h5>
                <span class="badge bg-light text-dark fs-6">
                    <i class="fas fa-box me-1"></i><?php echo $total_articulos; ?> registros
                </span>
            </div>
            <div class="card-body">
                <?php if (count($articulos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Imagen</th>
                                <th>QR</th>
                                <th>Visitas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($articulos as $row): ?>
                            <tr>
                                <!-- ID -->
                                <td class="align-middle">
                                    <span class="badge bg-secondary">#<?php echo $row['id']; ?></span>
                                </td>
                                
                                <!-- Producto -->
                                <td class="align-middle">
                                    <div class="product-name"><?php echo htmlspecialchars($row['nombre']); ?></div>
                                    <div class="product-price">$<?php echo number_format($row['precio'], 2); ?></div>
                                    <?php if (!empty($row['descripcion'])): ?>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo mb_strimwidth(htmlspecialchars($row['descripcion']), 0, 50, '...'); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Stock -->
                                <td class="align-middle">
                                    <span class="badge <?php echo $row['stock'] > 10 ? 'bg-success' : ($row['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?> stock-badge">
                                        <?php echo $row['stock']; ?> unidades
                                    </span>
                                    <?php if ($row['stock'] == 0): ?>
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Agotado</small>
                                    <?php elseif ($row['stock'] <= 5): ?>
                                        <br><small class="text-warning"><i class="fas fa-info-circle"></i> Stock bajo</small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Imagen -->
                                <td class="align-middle">
                                    <?php if (!empty($row['imagen_path'])): ?>
                                        <img src="<?php echo $row['imagen_path']; ?>" 
                                             alt="<?php echo htmlspecialchars($row['nombre']); ?>" 
                                             class="table-img"
                                             data-bs-toggle="tooltip" 
                                             data-bs-title="Clic para ver imagen completa"
                                             onclick="verImagenCompleta('<?php echo $row['imagen_path']; ?>', '<?php echo htmlspecialchars($row['nombre']); ?>')">
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="width: 60px; height: 60px; display: flex; flex-direction: column; justify-content: center; align-items: center; border: 2px dashed #ccc; border-radius: 8px;">
                                            <i class="fas fa-image"></i>
                                            <small>Sin imagen</small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- QR -->
                                <td class="align-middle">
                                    <?php if (!empty($row['qr_code'])): ?>
                                        <img src="<?php echo $row['qr_code']; ?>" 
                                             alt="Código QR" 
                                             class="qr-img"
                                             data-bs-toggle="tooltip" 
                                             data-bs-title="Clic para ver QR completo"
                                             onclick="verQRCompleto('<?php echo $row['qr_code']; ?>', '<?php echo htmlspecialchars($row['nombre']); ?>')">
                                    <?php else: ?>
                                        <a href="gestion_articulos.php?generar_qr=<?php echo $row['id']; ?>" 
                                           class="btn btn-outline-success btn-sm"
                                           data-bs-toggle="tooltip" 
                                           data-bs-title="Generar código QR para este producto">
                                            <i class="fas fa-qrcode"></i> Generar QR
                                        </a>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Visitas -->
                                <td class="align-middle">
                                    <span class="badge visitas-badge">
                                        <i class="fas fa-eye me-1"></i><?php echo $row['visitas']; ?>
                                    </span>
                                    <?php if ($row['visitas'] > 50): ?>
                                        <br><small class="text-success"><i class="fas fa-fire"></i> Popular</small>
                                    <?php elseif ($row['visitas'] > 10): ?>
                                        <br><small class="text-info"><i class="fas fa-star"></i> Interés</small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="align-middle">
                                    <div class="action-buttons">
                                        <!-- Editar -->
                                        <a href="editar_articulo.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-warning btn-sm btn-action" 
                                           data-bs-toggle="tooltip"
                                           data-bs-title="Editar artículo">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        
                                        <!-- Generar/Ver QR -->
                                        <?php if (empty($row['qr_code'])): ?>
                                            <a href="gestion_articulos.php?generar_qr=<?php echo $row['id']; ?>" 
                                               class="btn btn-info btn-sm btn-action"
                                               data-bs-toggle="tooltip"
                                               data-bs-title="Generar código QR">
                                                <i class="fas fa-qrcode"></i> QR
                                            </a>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-success btn-sm btn-action"
                                                    onclick="verQRCompleto('<?php echo $row['qr_code']; ?>', '<?php echo htmlspecialchars($row['nombre']); ?>')"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-title="Ver código QR completo">
                                                <i class="fas fa-eye"></i> QR
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Eliminar -->
                                        <button type="button" 
                                                class="btn btn-danger btn-sm btn-action" 
                                                onclick="confirmarEliminacion(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>')"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="Eliminar artículo">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-4x mb-3"></i>
                        <h4>No hay artículos registrados</h4>
                        <p class="mb-4">Comienza agregando tu primer artículo al catálogo</p>
                        <a href="nuevo_articulo.php" class="btn btn-success btn-lg">
                            <i class="fas fa-plus me-2"></i>Agregar Primer Artículo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información de ayuda -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información de la tabla
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-palette me-2"></i>Colores de stock:</h6>
                        <ul class="list-unstyled">
                            <li><span class="badge bg-success">Verde</span> - Stock suficiente (>10)</li>
                            <li><span class="badge bg-warning">Amarillo</span> - Stock bajo (1-10)</li>
                            <li><span class="badge bg-danger">Rojo</span> - Agotado (0)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-qrcode me-2"></i>Códigos QR:</h6>
                        <ul class="list-unstyled">
                            <li>✅ Escaneables desde cualquier dispositivo</li>
                            <li>✅ Redirigen a: <strong><?php echo $CONFIG['dominio_produccion']; ?></strong></li>
                            <li>✅ Incrementan las visitas automáticamente</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-chart-line me-2"></i>Métricas:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Visitas:</strong> Popularidad del producto</li>
                            <li><strong>Stock:</strong> Control de inventario</li>
                            <li><strong>QR:</strong> Marketing digital</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para imagen completa -->
    <div class="modal fade" id="imagenModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagenModalTitle">Imagen del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagenCompleta" src="" alt="" class="img-fluid rounded" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para QR completo -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalTitle">Código QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrCompleto" src="" alt="" class="img-fluid mb-3 rounded" style="max-width: 300px;">
                    <p class="text-muted">Escanea este código QR con tu celular para ver los detalles del producto</p>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            URL: <?php echo $CONFIG['dominio_produccion']; ?>/ver_producto.php?id=PRODUCTO_ID
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="descargarQR()">
                        <i class="fas fa-download me-2"></i>Descargar QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Función para confirmar eliminación
    function confirmarEliminacion(id, nombre) {
        if (confirm(`¿Estás seguro de que deseas eliminar el artículo "${nombre}"?\n\n⚠️ Esta acción no se puede deshacer.`)) {
            window.location.href = `gestion_articulos.php?eliminar=${id}`;
        }
    }

    // Función para ver imagen completa
    function verImagenCompleta(src, nombre) {
        document.getElementById('imagenCompleta').src = src;
        document.getElementById('imagenModalTitle').textContent = 'Imagen: ' + nombre;
        new bootstrap.Modal(document.getElementById('imagenModal')).show();
    }

    // Función para ver QR completo
    function verQRCompleto(src, nombre) {
        document.getElementById('qrCompleto').src = src;
        document.getElementById('qrModalTitle').textContent = 'QR: ' + nombre;
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }

    // Función para descargar QR
    function descargarQR() {
        const qrImg = document.getElementById('qrCompleto');
        const link = document.createElement('a');
        link.href = qrImg.src;
        link.download = 'qr_producto.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</body>
</html>