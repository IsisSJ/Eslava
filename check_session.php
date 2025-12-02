<?php
// check_sessions.php - Verificar estado de sesiones
echo "<h3>🔍 Estado de Sesiones</h3>";

// Verificar si hay sesión activa
$session_status = session_status();
echo "<p>session_status(): $session_status</p>";
echo "<p>session_id(): " . (session_id() ?: 'NO INICIADA') . "</p>";
echo "<p>session_name(): " . session_name() . "</p>";

// Verificar qué archivos han sido incluidos
echo "<h3>Archivos incluidos:</h3>";
echo "<pre>";
$included_files = get_included_files();
foreach ($included_files as $file) {
    echo htmlspecialchars($file) . "\n";
}
echo "</pre>";

// Verificar si conexion.php tiene el problema
echo "<h3>Contenido de conexion.php (primeras 15 líneas):</h3>";
if (file_exists('conexion.php')) {
    $content = file_get_contents('conexion.php');
    $lines = explode("\n", $content);
    echo "<pre>";
    for ($i = 0; $i < 15 && $i < count($lines); $i++) {
        echo ($i+1) . ": " . htmlspecialchars($lines[$i]) . "\n";
    }
    echo "</pre>";
}

echo '<hr><a href="login_working.php">Ir a Login Working</a>';
?>