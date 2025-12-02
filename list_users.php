<?php
// list_users.php - Ver usuarios con enlaces al reset público
include_once('conexion.php');

echo "<h3>👥 Usuarios en la Base de Datos</h3>";

try {
    $stmt = $conn->query("SELECT id, nombre_usuario, correo, rol FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    if (empty($usuarios)) {
        echo "<p>No hay usuarios registrados.</p>";
    } else {
        echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
        echo "<tr style='background:#f8f9fa;'>
                <th>ID</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acción</th>
              </tr>";
        
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td><strong>{$usuario['nombre_usuario']}</strong></td>";
            echo "<td>{$usuario['correo']}</td>";
            echo "<td>{$usuario['rol']}</td>";
            echo "<td>
                    <a href='reset_password_public.php?id={$usuario['id']}' 
                       style='background:#28a745; color:white; padding:5px 10px; text-decoration:none; border-radius:3px;'>
                       🔄 Resetear
                    </a>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo '<hr>';
echo '<div style="margin-top: 20px;">';
echo '<a href="login.php" class="btn btn-primary">Volver al Login</a> ';
echo '<a href="reset_password_public.php?id=1" class="btn btn-warning">Resetear Admin (ID: 1)</a>';
echo '</div>';
?>