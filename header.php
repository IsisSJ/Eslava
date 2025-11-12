<?php
// header.php - Encabezado común con PDO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo verificar conexión si no estamos en login.php
if (basename($_SERVER['PHP_SELF']) != 'login.php') {
    try {
        include_once('conexion.php');
        // Verificar conexión ejecutando una consulta simple
        $stmt = $conn->query("SELECT 1");
    } catch (PDOException $e) {
        error_log("Error de conexión en header: " . $e->getMessage());
        // No mostrar error al usuario, solo log
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-seedling me-2"></i>Flores de Chinampa
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Inicio
                    </a>
                </li>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_articulos.php">
                            <i class="fas fa-boxes me-1"></i>Productos
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['usuario']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text">Rol: <?php echo $_SESSION['rol']; ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>