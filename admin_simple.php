<?php
// admin_simple.php - Dashboard SIMPLE que SÍ funciona
session_start();

// Verificar sesión de manera SIMPLE
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_definitivo.php");
    exit();
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .welcome-box { 
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-box">
            <h1>🎉 ¡BIENVENIDO AL SISTEMA!</h1>
            <p class="lead">Has accedido exitosamente como administrador.</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h4>Información de Sesión</h4>
            </div>
            <div class="card-body">
                <p><strong>Usuario:</strong> ' . ($_SESSION['usuario_nombre'] ?? 'N/A') . '</p>
                <p><strong>Rol:</strong> ' . ($_SESSION['usuario_rol'] ?? 'N/A') . '</p>
                <p><strong>Session ID:</strong> ' . session_id() . '</p>
                <p><strong>¿logged_in?:</strong> ' . (isset($_SESSION['logged_in']) ? 'SÍ' : 'NO') . '</p>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Acciones:</h4>
            <div class="d-flex gap-2">
                <a href="admin.php" class="btn btn-primary">Ir al Dashboard Completo</a>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
                <a href="debug_login_complete.php" class="btn btn-info">Ver Debug</a>
            </div>
        </div>
        
        <div class="mt-4 card">
            <div class="card-header">
                <h5>Datos de Sesión (RAW):</h5>
            </div>
            <div class="card-body">
                <pre>' . print_r($_SESSION, true) . '</pre>
            </div>
        </div>
    </div>
</body>
</html>';