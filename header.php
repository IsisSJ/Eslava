<?php
// header.php - Debe incluirse en TODAS las páginas
require_once 'config_session.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Mi Tienda Online
            </a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="articulos.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categorias.php">Categorías</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <!-- Carrito -->
                    <a href="carrito.php" class="btn btn-outline-light position-relative me-3">
                        <i class="bi bi-cart3"></i>
                        <?php if (!empty($_SESSION['carrito'])): ?>
                            <span class="cart-count"><?php echo array_sum($_SESSION['carrito']); ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Usuario/Login -->
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="mi_cuenta.php">Mi Cuenta</a></li>
                                <li><a class="dropdown-item" href="mis_pedidos.php">Mis Pedidos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mt-3">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>
    