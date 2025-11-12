<?php
session_start();

// Solo admin
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        header {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
        }
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: #ecf0f1;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            transition: 0.3s;
        }
        .card:hover {
            background: #dfe6e9;
            transform: scale(1.05);
        }
        .card a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
        }
        .logout {
            float: right;
            margin-top: -40px;
            margin-right: 20px;
        }
        .logout a {
            color: #f39c12;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Panel de Administración</h1>
        <div class="logout">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?> 👋</h2>

        <div class="menu">
            <div class="card">
                <a href="usuarios.php">👤 Gestión de Usuarios</a>
            </div>
            <div class="card">
                <a href="articulos.php">📦 Gestión de Artículos</a>
            </div>
            <div class="card">
                <a href="bitacora.php">📑 Bitácora de Accesos</a>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> - Sistema CRUD con Roles
    </footer>
</body>
</html>

