<?php
// admin_simple.php - Dashboard SIMPLE sin redirecciones
session_start();

echo "<h1>ADMIN DASHBOARD (TEST)</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo '<p><a href="logout.php">Cerrar Sesión</a></p>';
echo '<p><a href="login.php">Volver al Login</a></p>';
?>