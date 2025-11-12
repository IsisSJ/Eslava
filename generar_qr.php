<?php
session_start();
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

function generarQRInfinityFree($id_articulo, $nombre_articulo) {
    global $CONFIG;
    
    // URL CORRECTA para producción
    $url_producto = $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $id_articulo;
    
    // Generar código QR usando API externa
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url_producto);
    
    return $qr_url;
}

// Procesar formulario para generar QR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_articulo = $_POST['id_articulo'] ?? 0;
    $accion = $_POST['accion'] ?? '';
    
    if ($id_articulo && $accion === 'generar_qr') {
        // Obtener datos del artículo
        $sql = "SELECT id, nombre FROM articulos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_articulo);
        $stmt->execute();
        $result = $stmt->get_result();
        $articulo = $result->fetch_assoc();
        
        if ($articulo) {
            // Generar QR con URL CORRECTA
            $nuevo_qr = generarQRInfinityFree($articulo['id'], $articulo['nombre']);
            
            // Actualizar en base de datos
            $update_sql = "UPDATE articulos SET qr_code = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $nuevo_qr, $articulo['id']);
            
            if ($update_stmt->execute()) {
                $mensaje = "✅ QR generado correctamente para: " . $articulo['nombre'];
                $mensaje .= "<br>🌐 URL: " . $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $articulo['id'];
                $tipo_mensaje = "success";
            } else {
                $mensaje = "❌ Error al guardar el QR en la base de datos";
                $tipo_mensaje = "error";
            }
            $update_stmt->close();
        } else {
            $mensaje = "❌ Artículo no encontrado";
            $tipo_mensaje = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar QR - Florería 42Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 80px;
            max-width: 900px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .qr-image {
            max-width: 300px;
            margin: 10px 0;
            border: 2px solid #28a745;
            border-radius: 8px;
        }
        .url-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            word-break: break-all;
        }
        .config-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .btn-action {
            margin: 2px;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-success">
                <i class="fas fa-qrcode me-2"></i>Generador de Códigos QR
            </h1>
            <span class="badge config-badge fs-6">
                <i class="fas fa-cloud me-1"></i><?php echo $CONFIG['dominio_produccion']; ?>
            </span>
        </div>

        <!-- Mensajes -->
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $tipo_mensaje === 'error' ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php if (isset($nuevo_qr) && $tipo_mensaje === 'success'): ?>
                    <div class="text-center mt-3">
                        <img src="<?php echo $nuevo_qr; ?>" alt="QR Generado" class="qr-image">
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Generar QR para artículo sin QR -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Generar Nuevo QR
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT id, nombre FROM articulos WHERE qr_code IS NULL OR qr_code = ''";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar Artículo:</label>
                                    <select name="id_articulo" class="form-select" required>
                                        <option value="">-- Seleccionar Artículo --</option>
                                        <?php while ($articulo = $result->fetch_assoc()): ?>
                                            <option value="<?php echo $articulo['id']; ?>">
                                                <?php echo htmlspecialchars($articulo['nombre']); ?> (ID: <?php echo $articulo['id']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <input type="hidden" name="accion" value="generar_qr">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-qrcode me-2"></i>Generar QR para InfinityFree
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                <p>✅ Todos los artículos ya tienen QR generado.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Regenerar QR existente -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sync-alt me-2"></i>Regenerar QR Existente
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT id, nombre, qr_code FROM articulos WHERE qr_code IS NOT NULL AND qr_code != ''";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0): ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Producto</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($articulo = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary">#<?php echo $articulo['id']; ?></span></td>
                                            <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_articulo" value="<?php echo $articulo['id']; ?>">
                                                    <input type="hidden" name="accion" value="generar_qr">
                                                    <button type="submit" class="btn btn-outline-warning btn-sm btn-action">
                                                        <i class="fas fa-sync-alt"></i> Regenerar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No hay artículos con QR existente.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Configuración -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información de Configuración
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-cloud me-2"></i>Configuración Actual:</h6>
                        <div class="url-info">
                            <strong>Dominio:</strong> <?php echo $CONFIG['dominio_produccion']; ?><br>
                            <strong>Modo:</strong> <?php echo $CONFIG['modo_desarrollo'] ? 'Desarrollo' : 'Producción'; ?><br>
                            <strong>Estado:</strong> <span class="badge bg-success">✅ Configurado</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-link me-2"></i>Ejemplo de URL:</h6>
                        <div class="url-info">
                            <?php echo $CONFIG['dominio_produccion']; ?>/ver_producto.php?id=1
                        </div>
                        <small class="text-muted">Esta es la URL que contendrán los códigos QR generados.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="verificar_qr.php" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Verificar Todos los QR
                    </a>
                    <a href="gestion_articulos.php" class="btn btn-outline-success">
                        <i class="fas fa-boxes me-2"></i>Gestión de Artículos
                    </a>
                    <a href="crear_todos_qr.php" class="btn btn-outline-warning">
                        <i class="fas fa-bolt me-2"></i>Generar Todos los QR
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-secondary">
                    <div class="card-body text-center">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h5>Total Artículos</h5>
                        <?php
                        $total = $conn->query("SELECT COUNT(*) as count FROM articulos")->fetch_assoc()['count'];
                        ?>
                        <h2 class="mb-0"><?php echo $total; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-qrcode fa-2x mb-2"></i>
                        <h5>Con QR</h5>
                        <?php
                        $con_qr = $conn->query("SELECT COUNT(*) as count FROM articulos WHERE qr_code IS NOT NULL")->fetch_assoc()['count'];
                        ?>
                        <h2 class="mb-0"><?php echo $con_qr; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <h5>Sin QR</h5>
                        <?php
                        $sin_qr = $conn->query("SELECT COUNT(*) as count FROM articulos WHERE qr_code IS NULL OR qr_code = ''")->fetch_assoc()['count'];
                        ?>
                        <h2 class="mb-0"><?php echo $sin_qr; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
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