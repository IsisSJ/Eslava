<?php
require_once 'config_session.php';
require_once 'conexion.php';

echo "<h2>👥 Usuarios en la Base de Datos</h2>";

try {
    $stmt = $conn->query("SELECT id, nombre_usuario, correo, rol FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    if (empty($usuarios)) {
        echo "<div class='alert alert-warning'>No hay usuarios en la base de datos</div>";
        echo "<a href='crear_admin.php' class='btn btn-success'>Crear primer admin</a>";
    } else {
        echo "<table class='table table-striped'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>";
        
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td><strong>{$usuario['nombre_usuario']}</strong></td>";
            echo "<td>{$usuario['correo']}</td>";
            echo "<td><span class='badge bg-" . ($usuario['rol'] === 'admin' ? 'danger' : 'info') . "'>{$usuario['rol']}</span></td>";
            echo "<td>";
            echo "<a href='reset_user_password.php?id={$usuario['id']}' class='btn btn-sm btn-warning'>Reset Pass</a> ";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>