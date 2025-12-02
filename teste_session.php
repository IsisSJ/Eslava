<?php
// test_session.php - Verificar configuración de sesiones
session_start();

echo "<h1>🔍 TEST DE SESIONES PHP</h1>";

// 1. Información del servidor
echo "<h3>1. Información del Servidor:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

// 2. Configuración de sesión
echo "<h3>2. Configuración de Sesión:</h3>";
$session_config = [
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite')
];

foreach ($session_config as $key => $value) {
    echo "$key: $value<br>";
}

// 3. Cookies actuales
echo "<h3>3. Cookies del Navegador:</h3>";
if (empty($_COOKIE)) {
    echo "No hay cookies<br>";
} else {
    foreach ($_COOKIE as $name => $value) {
        echo "$name: $value<br>";
    }
}

// 4. Sesión actual
echo "<h3>4. Sesión Actual:</h3>";
if (empty($_SESSION)) {
    echo "Sesión vacía<br>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// 5. Probar establecer sesión
echo "<h3>5. Probar a Establecer Sesión:</h3>";
$_SESSION['test_time'] = time();
$_SESSION['test_random'] = rand(1000, 9999);

echo "Se establecieron valores de prueba:<br>";
echo "- test_time: " . $_SESSION['test_time'] . "<br>";
echo "- test_random: " . $_SESSION['test_random'] . "<br>";

// 6. Enlace para verificar persistencia
echo "<h3>6. Verificar Persistencia:</h3>";
echo '<a href="test_session.php?check=1">Recargar para ver si persiste la sesión</a><br>';
echo '<a href="test_session.php?clean=1">Limpiar sesión</a>';

// 7. Si hay parámetro check
if (isset($_GET['check'])) {
    echo "<h4>Resultado de persistencia:</h4>";
    if (isset($_SESSION['test_time'])) {
        echo "✅ La sesión PERSISTE. test_time: " . $_SESSION['test_time'];
    } else {
        echo "❌ La sesión NO persiste";
    }
}

// 8. Limpiar si se solicita
if (isset($_GET['clean'])) {
    session_destroy();
    echo "<script>window.location.href = 'test_session.php';</script>";
}
?>