<?php
// reset_all.php - Reset completo del sistema
session_start();
session_destroy();

// Eliminar todas las cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}

// Limpiar caché del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo "<h1>✅ Sistema reseteado completamente</h1>";
echo "<p>Se han eliminado:</p>";
echo "<ul>";
echo "<li>✅ Todas las sesiones</li>";
echo "<li>✅ Todas las cookies</li>";
echo "<li>✅ Todo el caché</li>";
echo "</ul>";
echo '<br><a href="login.php?nocache=' . time() . '">Ir al login (limpio)</a>';
?>