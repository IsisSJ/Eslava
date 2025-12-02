<?php
require_once 'config_session.php';
include_once 'config_session.php';

include_once("conexion.php");

// CONFIGURACIÓN PARA INFINITYFREE - TU DOMINIO NUEVO
$CONFIG = [
    'dominio_produccion' => 'https://floreria.42web.io',
    'modo_desarrollo' => false
];

// Verificar que el usuario sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Función para generar QR
function generarCodigoQR($id_articulo, $nombre_articulo) {
    global $CONFIG;
    
    // URL CORRECTA para InfinityFree
    $url_producto = $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $id_articulo;
    
    // Generar código QR usando API
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&margin=10&data=" . urlencode($url_producto);
    
    return [
        'qr_url' => $qr_code_url,
        'producto_url' => $url_producto,
        'producto_id' => $id_articulo,
        'producto_nombre' => $nombre_articulo
    ];
}

// Procesar creación de QR individual
if (isset($_GET['crear_qr'])) {
    $articulo_id = intval($_GET['crear_qr']);
    
    // Obtener información del artículo
    $sql = "SELECT id, nombre, precio FROM articulos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $articulo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $articulo = $result->fetch_assoc();
    
    if ($articulo) {
        // Generar QR
        $qr_data = generarCodigoQR($articulo['id'], $articulo['nombre']);
        
        // Guardar en la base de datos
        $update_sql = "UPDATE articulos SET qr_code = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $qr_data['qr_url'], $articulo['id']);
        
        if ($update_stmt->execute()) {
            $_SESSION['mensaje'] = "✅ QR creado exitosamente para: " . $articulo['nombre'];
            $_SESSION['tipo_mensaje'] = "success";
            $_SESSION['qr_data'] = $qr_data; // Para mostrar en la página
        } else {
            $_SESSION['mensaje'] = "❌ Error al crear QR: " . $update_stmt->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $update_stmt->close();
    } else {
        $_SESSION['mensaje'] = "❌ Artículo no encontrado";
        $_SESSION['tipo_mensaje'] = "error";
    }
    $stmt->close();
    
    header('Location: crear_qr.php');
    exit();
}

// Procesar creación masiva de QR
if (isset($_POST['accion']) && $_POST['accion'] === 'crear_todos_qr') {
    // Obtener todos los artículos sin QR
    $sql = "SELECT id, nombre FROM articulos WHERE qr_code IS NULL OR qr_code = ''";
    $result = $conn->query($sql);
    
    $creados = 0;
    $errores = 0;
    $detalles = [];
    
    if ($result->num_rows > 0) {
        while ($articulo = $result->fetch_assoc()) {
            // Generar QR para cada artículo
            $qr_data = generarCodigoQR($articulo['id'], $articulo['nombre']);
            
            // Guardar en la base de datos
            $update_sql = "UPDATE articulos SET qr_code = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $qr_data['qr_url'], $articulo['id']);
            
            if ($update_stmt->execute()) {
                $creados++;
                $detalles[] = "✅ " . $articulo['nombre'];
            } else {
                $errores++;
                $detalles[] = "❌ " . $articulo['nombre'] . " - Error: " . $update_stmt->error;
            }
            $update_stmt->close();
        }
        
        $_SESSION['mensaje'] = "🎯 Proceso completado: $creados QR creados, $errores errores";
        $_SESSION['tipo_mensaje'] = $errores === 0 ? "success" : "warning";
        $_SESSION['detalles_qr'] = $detalles;
    } else {
        $_SESSION['mensaje'] = "ℹ️ Todos los artículos ya tienen QR generado";
        $_SESSION['tipo_mensaje'] = "info";
    }
    
    header('Location: crear_qr.php');
    exit();
}

// Obtener estadísticas
$total_articulos = $conn->query("SELECT COUNT(*) as count FROM articulos")->fetch_assoc()['count'];
$articulos_con_qr = $conn->query("SELECT COUNT(*) as count FROM articulos WHERE qr_code IS NOT NULL AND qr_code != ''")->fetch_assoc()['count'];
$articulos_sin_qr = $total_articulos - $articulos_con_qr;

// Procesar mensajes
$mensaje = '';
$tipo_mensaje = '';
$qr_data = null;
$detalles_qr = [];

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    $qr_data = $_SESSION['qr_data'] ?? null;
    $detalles_qr = $_SESSION['detalles_qr'] ?? [];
    
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
    unset($_SESSION['qr_data']);
    unset($_SESSION['detalles_qr']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Códigos QR - Florería 42Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 80px;
            max-width: 1200px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .qr-preview {
            max-width: 250px;
            border: 3px solid #28a745;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }
        .url-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            font-size: 0.9em;
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .btn-action {
            margin: 2px;
        }
        .config-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .detalles-list {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-success">
                <i class="fas fa-qr-code me-2"></i>Crear Códigos QR
            </h1>
            <div>
                <span class="badge config-badge fs-6">
                    <i class="fas fa-cloud me-1"></i><?php echo $CONFIG['dominio_produccion']; ?>
                </span>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : ($tipo_mensaje === 'warning' ? 'warning' : 'success'); ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $tipo_mensaje === 'error' ? 'exclamation-triangle' : ($tipo_mensaje === 'warning' ? 'exclamation-circle' : 'check-circle'); ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                
                <!-- Mostrar QR recién creado -->
                <?php if ($qr_data): ?>
                    <div class="mt-3 p-3 border rounded bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <img src="<?php echo $qr_data['qr_url']; ?>" alt="QR Creado" class="qr-preview">
                            </div>
                            <div class="col-md-8">
                                <h5>Detalles del QR:</h5>
                                <p><strong>Producto:</strong> <?php echo $qr_data['producto_nombre']; ?></p>
                                <p><strong>ID:</strong> #<?php echo $qr_data['producto_id']; ?></p>
                                <div class="url-display">
                                    <?php echo $qr_data['producto_url']; ?>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo $qr_data['qr_url']; ?>" download="qr_<?php echo $qr_data['producto_id']; ?>.png" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Descargar QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Mostrar detalles de creación masiva -->
                <?php if (!empty($detalles_qr)): ?>
                    <div class="mt-3">
                        <h6>Detalles del proceso:</h6>
                        <div class="detalles-list border rounded p-2">
                            <?php foreach ($detalles_qr as $detalle): ?>
                                <div class="small"><?php echo $detalle; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h5>Total Artículos</h5>
                        <h2 class="mb-0"><?php echo $total_articulos; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h5>Con QR</h5>
                        <h2 class="mb-0"><?php echo $articulos_con_qr; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <h5>Sin QR</h5>
                        <h2 class="mb-0"><?php echo $articulos_sin_qr; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Creación Individual de QR -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Crear QR Individual
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtener artículos sin QR
                        $sql = "SELECT id, nombre, precio FROM articulos WHERE qr_code IS NULL OR qr_code = '' ORDER BY nombre";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0): ?>
                            <p class="text-muted">Selecciona un artículo para generar su código QR:</p>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group">
                                    <?php while ($articulo = $result->fetch_assoc()): ?>
                                        <a href="crear_qr.php?crear_qr=<?php echo $articulo['id']; ?>" 
                                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($articulo['nombre']); ?></strong>
                                                <br>
                                                <small class="text-muted">ID: #<?php echo $articulo['id']; ?> | $<?php echo number_format($articulo['precio'], 2); ?></small>
                                            </div>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-qrcode me-1"></i>Crear QR
                                            </span>
                                        </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>¡Todos los artículos tienen QR!</h5>
                                <p>No hay artículos pendientes de generar código QR.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Creación Masiva de QR -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Creación Masiva
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-magic fa-3x text-warning mb-3"></i>
                            <h5>Generar Todos los QR Pendientes</h5>
                            <p class="text-muted">Crea códigos QR para todos los artículos que no tengan uno.</p>
                            
                            <?php if ($articulos_sin_qr > 0): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong><?php echo $articulos_sin_qr; ?> artículos</strong> pendientes de generar QR
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="accion" value="crear_todos_qr">
                                    <button type="submit" class="btn btn-warning btn-lg w-100" onclick="return confirm('¿Estás seguro de que deseas generar <?php echo $articulos_sin_qr; ?> códigos QR?')">
                                        <i class="fas fa-bolt me-2"></i>Generar <?php echo $articulos_sin_qr; ?> QR
                                    </button>
                                </form>
                                
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-clock me-1"></i>Este proceso puede tomar unos segundos
                                </small>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    No hay artículos pendientes de generar QR
                                </div>
                                <button class="btn btn-outline-secondary w-100" disabled>
                                    <i class="fas fa-check me-2"></i>Todo Completado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Artículos con QR existentes -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>QR Existentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT id, nombre, qr_code FROM articulos WHERE qr_code IS NOT NULL AND qr_code != '' ORDER BY nombre LIMIT 10";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0): ?>
                            <p class="text-muted">Últimos artículos con QR generado:</p>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php while ($articulo = $result->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                        <div>
                                            <strong><?php echo htmlspecialchars($articulo['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted">ID: #<?php echo $articulo['id']; ?></small>
                                        </div>
                                        <div>
                                            <a href="crear_qr.php?crear_qr=<?php echo $articulo['id']; ?>" 
                                               class="btn btn-outline-warning btn-sm"
                                               title="Regenerar QR">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <?php if ($articulos_con_qr > 10): ?>
                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        y <?php echo ($articulos_con_qr - 10); ?> más...
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-qrcode fa-2x mb-2"></i>
                                <p>No hay QR generados aún</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Configuración -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>Configuración
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-cloud me-2"></i>Servidor:</h6>
                        <div class="url-display">
                            <?php echo $CONFIG['dominio_produccion']; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-link me-2"></i>Formato de URLs:</h6>
                        <div class="url-display">
                            <?php echo $CONFIG['dominio_produccion']; ?>/ver_producto.php?id=PRODUCTO_ID
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
            <a href="gestion_articulos.php" class="btn btn-outline-primary me-2">
                <i class="fas fa-boxes me-2"></i>Gestión de Artículos
            </a>
            <a href="verificar_qr.php" class="btn btn-outline-success me-2">
                <i class="fas fa-search me-2"></i>Verificar QR
            </a>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-2"></i>Panel Admin
            </a>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>

<?php
// Cerrar conexión
if (isset($conn)) {
    $conn->close();
}
?>