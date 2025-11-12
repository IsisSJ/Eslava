<?php
session_start();
include_once("conexion.php");

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Redirigir según el rol
if ($_SESSION['rol'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit();
} elseif ($_SESSION['rol'] === 'consultor') {
    header('Location: consultor.php');
    exit();
}
// Los clientes se quedan en este menú
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            min-height: 100vh;
        }
        .container {
            margin-top: 80px;
        }
        .welcome-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .menu-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .menu-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <!-- Tarjeta de Bienvenida -->
        <div class="card welcome-card mb-5">
            <div class="card-body text-center py-5">
                <h1 class="display-4">🌸 Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>
                <p class="lead">Descubre la belleza natural de nuestras flores de chinampa</p>
                <div class="mt-4">
                    <span class="badge bg-light text-dark fs-6 p-3">
                        <i class="fas fa-user me-2"></i>Cliente
                    </span>
                </div>
            </div>
        </div>

        <!-- Menú de Opciones -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card menu-card text-center p-4">
                    <div class="menu-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4>Tienda</h4>
                    <p class="text-muted">Explora nuestra colección de