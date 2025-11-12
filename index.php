<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌹 Flores de Chinampa - Sitio Activo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        .main-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 800px;
            width: 90%;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn-custom {
            padding: 12px 30px;
            margin: 10px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-3px);
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature-item {
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="main-card">
        <i class="fas fa-check-circle success-icon"></i>
        <h1 class="text-success mb-3">¡Sitio Activado Correctamente! 🎉</h1>
        <p class="lead text-muted mb-4">El dominio <strong>floreria.42web.io</strong> está funcionando perfectamente</p>
        
        <div class="feature-grid">
            <div class="feature-item">
                <i class="fas fa-globe fa-2x text-primary mb-2"></i>
                <h5>Dominio Activo</h5>
                <small class="text-muted">floreria.42web.io</small>
            </div>
            <div class="feature-item">
                <i class="fas fa-server fa-2x text-success mb-2"></i>
                <h5>PHP Funcionando</h5>
                <small class="text-muted">InfinityFree</small>
            </div>
            <div class="feature-item">
                <i class="fas fa-qrcode fa-2x text-warning mb-2"></i>
                <h5>Sistema QR</h5>
                <small class="text-muted">Listo para usar</small>
            </div>
        </div>

        <div class="mb-4">
            <a href="login.php" class="btn btn-success btn-custom">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
            </a>
            <a href="gestion_articulos.php" class="btn btn-outline-success btn-custom">
                <i class="fas fa-boxes me-2"></i>Gestión de Productos
            </a>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Próximo paso:</strong> Subir los archivos PHP de tu sistema
        </div>

        <div class="mt-4">
            <h6 class="text-muted">Enlaces de prueba:</h6>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="login.php" class="btn btn-sm btn-outline-primary">Login</a>
                <a href="gestion_articulos.php" class="btn btn-sm btn-outline-secondary">Productos</a>
                <a href="ver_producto.php?id=1" class="btn btn-sm btn-outline-success">Ver Producto 1</a>
                <a href="generar_qr.php" class="btn btn-sm btn-outline-warning">Generar QR</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>