<?php
// debug.php - Archivo temporal para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug - Verificando configuración</h1>";

// Verificar si PHP funciona
echo "<p>✅ PHP está funcionando</p>";

// Verificar sesiones
session_start();
echo "<p>✅ Sesiones funcionando</p>";

// Verificar conexión a BD
include_once("conexion.php");
echo "<p>✅ Conexión a BD cargada</p>";

// Verificar variables de sesión
echo "<pre>SESSION: ";
print_r($_SESSION);
echo "</pre>";

echo "<p>✅ Todo parece funcionar</p>";
?>