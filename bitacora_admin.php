<?php
require_once 'config_session.php';
include_once 'config_session.php';
include_once("conexion.php");

// Solo admin
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

// --- ACCIONES RÁPIDAS ---
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if ($accion == "aceptar") {
        $conn->query("UPDATE bitacora SET estado='aceptado' WHERE id=$id");
    } elseif ($accion == "denegar") {
        $conn->query("UPDATE bitacora SET estado='denegado' WHERE id=$id");
    } elseif ($accion == "pendiente") {
        $conn->query("UPDATE bitacora SET estado='pendiente' WHERE id=$id");
    } elseif ($accion == "eliminar") {
        $conn->query("DELETE FROM bitacora WHERE id=$id");
    }

    header("Location: bitacora_admin.php");
    exit();
}

// --- LISTAR BITÁCORA ---
$result = $conn->query("SELECT * FROM bitacora ORDER BY fecha_hora DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Accesos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { margin-top: 80px; }
        .table-img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <h2>📑 Bitácora de Accesos - Administrador</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">⬅ Volver al panel</a>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Password</th>
                        <th>Fecha/Hora</th>
                        <th>Imagen</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['pw']); ?></td>
                        <td><?php echo $row['fecha_hora']; ?></td>
                        <td>
                            <?php if (!empty($row['imagen'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" 
                                     class="table-img" 
                                     alt="Imagen de acceso">
                            <?php else: ?>
                                <span class="text-muted">Sin foto</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                switch($row['estado']) {
                                    case 'aceptado': echo 'success'; break;
                                    case 'denegado': echo 'danger'; break;
                                    default: echo 'warning';
                                }
                            ?>"><?php echo ucfirst($row['estado']); ?></span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="bitacora_admin.php?accion=aceptar&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-success btn-sm">Aceptar</a>
                                <a href="bitacora_admin.php?accion=denegar&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm">Denegar</a>
                                <a href="bitacora_admin.php?accion=pendiente&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-warning btn-sm">Pendiente</a>
                                <a href="bitacora_admin.php?accion=eliminar&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('¿Eliminar este registro?')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>