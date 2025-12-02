<?php
// emergency_access.php - Página de acceso de emergencia completo
session_start();
include_once('conexion.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Acceso de Emergencia</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; border: 2px solid #dc3545; }
        .card-header-danger { background: #dc3545; color: white; }
        .btn-group { display: flex; flex-wrap: wrap; gap: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='text-center mb-4'>🚨 Acceso de Emergencia</h1>
        
        <div class='card'>
            <div class='card-header card-header-danger'>
                <h4 class='mb-0'><i class='fas fa-exclamation-triangle me-2'></i>Herramientas de Emergencia</h4>
            </div>
            <div class='card-body'>
                <p>Utiliza estas herramientas si no puedes acceder al sistema normalmente.</p>
                
                <div class='row'>
                    <div class='col-md-6'>
                        <h5>🔑 Resetear Contraseñas</h5>
                        <div class='btn-group'>";

// Listar usuarios para reset
try {
    $stmt = $conn->query("SELECT id, nombre_usuario, rol FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "<a href='reset_password_public.php?id={$usuario['id']}' class='btn btn-warning'>
                {$usuario['nombre_usuario']} (ID: {$usuario['id']})
              </a>";
    }
} catch (Exception $e) {
    echo "<p class='text-danger'>Error: " . $e->getMessage() . "</p>";
}

echo "              </div>
                    </div>
                    
                    <div class='col-md-6'>
                        <h5>🔧 Otras Herramientas</h5>
                        <div class='d-grid gap-2'>
                            <a href='list_users.php' class='btn btn-info'>👥 Ver todos los usuarios</a>
                            <a href='login_simple.php' class='btn btn-success'>🌺 Login Simple</a>
                            <a href='reset_all.php' class='btn btn-danger'>🔄 Limpiar Sesiones</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login directo de emergencia -->
        <div class='card'>
            <div class='card-header bg-success text-white'>
                <h4 class='mb-0'><i class='fas fa-sign-in-alt me-2'></i>Login de Emergencia Directo</h4>
            </div>
            <div class='card-body'>
                <form method='POST' action='login_emergency_process.php'>
                    <div class='row'>
                        <div class='col-md-6 mb-3'>
                            <label>Usuario</label>
                            <select name='usuario_id' class='form-select' required>
                                <option value=''>Selecciona un usuario</option>";

// Opciones de usuarios
foreach ($usuarios as $usuario) {
    echo "<option value='{$usuario['id']}'>{$usuario['nombre_usuario']} ({$usuario['rol']})</option>";
}

echo "              </select>
                        </div>
                        <div class='col-md-6 mb-3'>
                            <label>Nueva Contraseña</label>
                            <input type='text' name='nueva_password' class='form-control' value='admin123' required>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-success btn-lg w-100'>
                        🔓 Acceder Directamente
                    </button>
                    <small class='text-muted d-block mt-2'>Esto reseteará la contraseña y te logueará automáticamente.</small>
                </form>
            </div>
        </div>
    </div>
    
    <script src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js'></script>
</body>
</html>";
?>