<?php
// articulos_simple.php - Página simple sin redirecciones
session_start();

echo "<h1>ARTÍCULOS (TEST)</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    echo "<p>✅ Estás logueado como: " . ($_SESSION['usuario_nombre'] ?? 'N/A') . "</p>";
} else {
    echo "<p>❌ NO estás logueado</p>";
}

echo '<p><a href="logout.php">Cerrar Sesión</a></p>';
echo '<p><a href="login.php">Volver al Login</a></p>';
?>