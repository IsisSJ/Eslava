<?php
session_start();

// Verificar sesión primero
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Incluir conexión después de verificar sesión
include_once("conexion.php");

// Verificar que la conexión esté activa
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la base de datos");
}

// Manejar eliminación de usuario
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    
    // No permitir eliminarse a sí mismo
    if ($id_eliminar != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_eliminar);
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Usuario eliminado correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar usuario: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "No puedes eliminarte a ti mismo";
    }
    header("Location: usuarios.php");
    exit();
}

// Manejar cambio de rol
if (isset($_POST['cambiar_rol'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $nuevo_rol = $_POST['nuevo_rol'];
    
    // No permitir cambiar el propio rol
    if ($id_usuario != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $nuevo_rol, $id_usuario);
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Rol actualizado correctamente";
            } else {
                $_SESSION['error'] = "Error al actualizar rol: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "No puedes cambiar tu propio rol";
    }
    header("Location: usuarios.php");
    exit();
}

// Obtener todos los usuarios
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
$total_usuarios = $usuarios->num_rows;
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
            margin-top: 100px; 
            margin-bottom: 50px;
        }
        .user-avatar { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 2px solid #dee2e6;
        }
        .avatar-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c757d, #495057);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            border: 2px solid #dee2e6;
        }
        .badge-admin { background-color: #dc3545; }
        .badge-cliente { background-color: #28a745; }
        .badge-consultor { background-color: #17a2b8; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .stats-card {
            border: none;
            border-radius: 10px;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
            </a>
        </div>

        <!-- Mostrar mensajes -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Estadísticas de usuarios -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card bg-primary">
                    <div class="stats-number"><?php echo $total_usuarios; ?></div>
                    <div class="stats-label">Total Usuarios</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-danger">
                    <div class="stats-number">
                        <?php echo $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin'")->fetch_assoc()['total']; ?>
                    </div>
                    <div class="stats-label">Administradores</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-success">
                    <div class="stats-number">
                        <?php echo $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'")->fetch_assoc()['total']; ?>
                    </div>
                    <div class="stats-label">Clientes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-info">
                    <div class="stats-number">
                        <?php echo $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'consultor'")->fetch_assoc()['total']; ?>
                    </div>
                    <div class="stats-label">Consultores</div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Lista de Usuarios
                    </h5>
                    <span class="badge bg-light text-dark fs-6"><?php echo $total_usuarios; ?> registros</span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($total_usuarios > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Foto</th>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($usuario = $usuarios->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($usuario['foto_perfil'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($usuario['foto_perfil']); ?>" 
                                                 class="user-avatar" 
                                                 alt="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>"
                                                 title="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>">
                                        <?php else: ?>
                                            <div class="avatar-placeholder" title="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>">
                                                <?php echo strtoupper(substr($usuario['nombre_usuario'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">#<?php echo $usuario['id']; ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                                            <?php if ($usuario['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge bg-info ms-1">
                                                    <i class="fas fa-user me-1"></i>Tú
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($usuario['correo']); ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                            <select name="nuevo_rol" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()" 
                                                    <?php echo $usuario['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>
                                                    style="min-width: 120px;">
                                                <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                                <option value="cliente" <?php echo $usuario['rol'] == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                                <option value="consultor" <?php echo $usuario['rol'] == 'consultor' ? 'selected' : ''; ?>>Consultor</option>
                                            </select>
                                            <input type="hidden" name="cambiar_rol" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                            <div class="btn-group">
                                                <a href="editar_perfil.php?id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-warning btn-sm"
                                                   title="Editar usuario">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="usuarios.php?eliminar=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('¿Estás seguro de eliminar al usuario \'<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>\'?\n\nEsta acción no se puede deshacer.')"
                                                   title="Eliminar usuario">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">
                                                <i class="fas fa-lock me-1"></i>No disponible
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4>No hay usuarios registrados</h4>
                        <p class="text-muted">Parece que no hay usuarios en el sistema aún.</p>
                        <a href="registro.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Registrar Primer Usuario
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <p><strong>Total de usuarios:</strong> <?php echo $total_usuarios; ?></p>
                            <p><strong>Tu usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?> (ID: <?php echo $_SESSION['user_id']; ?>)</p>
                            <p><strong>Permisos:</strong> Puedes gestionar todos los usuarios excepto tu propia cuenta.</p>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Precauciones</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <ul class="mb-0">
                                <li>No puedes eliminar o cambiar el rol de tu propia cuenta</li>
                                <li>La eliminación de usuarios es permanente</li>
                                <li>Los cambios de rol afectan los permisos inmediatamente</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmación adicional para eliminaciones
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a[href*="eliminar"]');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este usuario?\n\nEsta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>