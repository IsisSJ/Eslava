<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "consultor") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Consultor</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="panel">
        <h2>Bienvenido Consultor</h2>
        <p>Solo tienes permisos de consulta.</p>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>
</body>
</html>
