<?php
// debug_sesion.php
require_once 'config_session.php';

echo "<h1>🔍 DEBUG SESIONES</h1>";
echo "<h3>Sesión ID: " . session_id() . "</h3>";
echo "<h3>Datos de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Probar a agregar algo
$_SESSION['test'] = time();
echo "<p>Valor de prueba agregado: " . $_SESSION['test'] . "</p>";

echo '<a href="debug_sesion.php?reload=1">Recargar para ver si persiste</a>';
?>