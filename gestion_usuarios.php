<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté autenticado como administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar cambio de rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_rol'])) {
    $usuario_id = $_POST['usuario_id'];
    $nuevo_rol = $_POST['nuevo_rol'];
    
    $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_rol, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Rol actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el rol: " . $stmt->error;
        $_SESSION['tipo_mensaje'] = "error";
    }
    $stmt->close();
    
    // Recargar la página para ver los cambios
    header('Location: gestion_usuarios.php');
    exit();
}

// Procesar eliminación de usuario
if (isset($_GET['eliminar'])) {
    $usuario_id = $_GET['eliminar'];
    
    // No permitir eliminar al usuario actual
    if ($usuario_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Usuario eliminado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar usuario: " . $stmt->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje'] = "No puedes eliminar tu propio usuario";
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    header('Location: gestion_usuarios.php');
    exit();
}

// Obtener todos los usuarios
$sql = "SELECT id, nombre_usuario, correo, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC";
$result = $conn->query($sql);

// Procesar mensajes
$mensaje = '';
$tipo_mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 80px;
        }
        .role-badge {
            font-size: 0.8em;
            cursor: pointer;
        }
        .btn-action {
            margin: 2px;
        }
        .user-table tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-success">
                <i class="fas fa-users me-2"></i>Gestión de Usuarios
            </h1>
            <div>
                <a href="gestion_articulos.php" class="btn btn-outline-success">
                    <i class="fas fa-boxes me-2"></i>Gestión de Artículos
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users me-2"></i>Total Usuarios
                        </h5>
                        <h2 class="mb-0"><?php echo $result->num_rows; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-shield me-2"></i>Administradores
                        </h5>
                        <h2 class="mb-0">
                            <?php 
                            $admin_count = $conn->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'admin'")->fetch_assoc()['count'];
                            echo $admin_count;
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user me-2"></i>Clientes
                        </h5>
                        <h2 class="mb-0">
                            <?php 
                            $cliente_count = $conn->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'cliente'")->fetch_assoc()['count'];
                            echo $cliente_count;
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-clock me-2"></i>Consultores
                        </h5>
                        <h2 class="mb-0">
                            <?php 
                            $consultor_count = $conn->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'consultor'")->fetch_assoc()['count'];
                            echo $consultor_count;
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Usuarios (Total: <?php echo $result->num_rows; ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover user-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol Actual</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($usuario = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                                        <?php if ($usuario['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-info">Tú</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                            <div class="input-group input-group-sm" style="width: 200px;">
                                                <select name="nuevo_rol" class="form-select" 
                                                        onchange="this.form.submit()" 
                                                        <?php echo $usuario['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                    <option value="cliente" <?php echo $usuario['rol'] == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                                    <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                                    <option value="consultor" <?php echo $usuario['rol'] == 'consultor' ? 'selected' : ''; ?>>Consultor</option>
                                                </select>
                                                <button type="submit" name="cambiar_rol" class="btn btn-outline-primary btn-sm" 
                                                        <?php echo $usuario['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <!-- Badge visual del rol -->
                                        <span class="badge 
                                            <?php 
                                            switch($usuario['rol']) {
                                                case 'admin': echo 'bg-danger'; break;
                                                case 'consultor': echo 'bg-warning'; break;
                                                default: echo 'bg-info';
                                            }
                                            ?> role-badge mt-1">
                                            <?php echo ucfirst($usuario['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Botón Editar (pendiente implementar) -->
                                            <button class="btn btn-warning btn-sm btn-action" title="Editar" disabled>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Botón Eliminar -->
                                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm btn-action btn-eliminar-usuario" 
                                                        data-id="<?php echo $usuario['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-sm btn-action" disabled title="No puedes eliminarte a ti mismo">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>No hay usuarios registrados</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información de roles -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información de Roles
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><span class="badge bg-danger">Administrador</span></h6>
                        <ul class="small">
                            <li>Acceso completo al sistema</li>
                            <li>Gestión de productos</li>
                            <li>Gestión de usuarios</li>
                            <li>Ver todos los pedidos</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><span class="badge bg-warning">Consultor</span></h6>
                        <ul class="small">
                            <li>Ver productos y pedidos</li>
                            <li>Generar reportes</li>
                            <li>No puede modificar datos</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><span class="badge bg-info">Cliente</span></h6>
                        <ul class="small">
                            <li>Comprar productos</li>
                            <li>Ver su historial de pedidos</li>
                            <li>Gestionar su perfil</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para confirmaciones -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmación para eliminar usuario
        const botonesEliminar = document.querySelectorAll('.btn-eliminar-usuario');
        
        botonesEliminar.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                if(confirm(`¿Estás seguro de que deseas eliminar al usuario "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                    window.location.href = `gestion_usuarios.php?eliminar=${id}`;
                }
            });
        });

        // Mostrar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>