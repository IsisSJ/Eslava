<?php
require_once 'config_session.php';
include_once 'config_session.php';

// TEMPORAL: Aceptar cualquier sesión para debug
if (!isset($_SESSION['logged_in'])) {
    // Si hay parámetros GET, crear sesión temporal
    if (isset($_GET['debug'])) {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_nombre'] = 'Admin Debug';
        $_SESSION['usuario_rol'] = 'admin';
        $_SESSION['logged_in'] = true;
    } else {
        header("Location: login_working.php");
        exit();
    }
}

include_once("conexion.php");

echo "<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <nav class='navbar navbar-dark bg-success'>
        <div class='container'>
            <a class='navbar-brand' href='admin_dashboard.php'>🌺 Admin Dashboard</a>
            <div class='navbar-text'>
                Usuario: " . ($_SESSION['usuario_nombre'] ?? 'N/A') . "
                <a href='logout.php' class='btn btn-sm btn-outline-light ms-3'>Salir</a>
            </div>
        </div>
    </nav>
    
    <div class='container mt-4'>
        <h1>✅ ¡BIENVENIDO AL PANEL DE ADMINISTRACIÓN!</h1>
        <p>Has accedido correctamente al sistema.</p>
        
        <div class='row mt-4'>
            <div class='col-md-4'>
                <div class='card text-white bg-primary'>
                    <div class='card-body'>
                        <h3>Gestión</h3>
                        <a href='articulos.php' class='btn btn-light'>Ver Productos</a>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-white bg-success'>
                    <div class='card-body'>
                        <h3>Herramientas</h3>
                        <a href='debug_login.php' class='btn btn-light'>Debug</a>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-white bg-warning'>
                    <div class='card-body'>
                        <h3>Sesión</h3>
                        <p>ID: " . session_id() . "</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='mt-4'>
            <h3>Información de Sesión:</h3>
            <pre>" . print_r($_SESSION, true) . "</pre>
        </div>
    </div>
</body>
</html>";