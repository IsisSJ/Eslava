<?php
// conexion.php - CONFIGURACIÓN CORRECTA PARA CLEVER CLOUD + RENDER

// Detectar entorno
$is_render = getenv('RENDER') ? true : false;

if ($is_render) {
    // ✅ CONFIGURACIÓN CORRECTA PARA RENDER + CLEVER CLOUD
    // Las variables de entorno en Render tienen nombres diferentes:
    $host = getenv('DB_NAME') ?: 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
    $dbname = 'bc8i4pda2kn2fqs150qm';  // Nombre fijo de la BD
    $username = 'uo5qglcqiyhjhqot';     // Usuario fijo
    $password = getenv('DB_PASS') ?: ''; // Contraseña de las variables
    $port = getenv('DB_PORT') ?: '3306';
} else {
    // Configuración local
    $host = 'localhost';
    $dbname = 'flores_chinampa';
    $username = 'root';
    $password = '';
    $port = '3306';
}

// DEBUG: Mostrar configuración (solo en logs)
error_log("Configuración BD:");
error_log("- Host: " . $host);
error_log("- DB: " . $dbname);
error_log("- User: " . $username);
error_log("- Port: " . $port);
error_log("- Password length: " . strlen($password));

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
    
    // Verificar conexión
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    error_log("✅ Conexión MySQL exitosa. Test: " . $result['test']);
    
} catch (PDOException $e) {
    error_log("❌ ERROR DE CONEXIÓN MySQL:");
    error_log("- Mensaje: " . $e->getMessage());
    error_log("- Código: " . $e->getCode());
    error_log("- Config: host=$host, db=$dbname, user=$username");
    
    // Mensaje detallado para debug
    if ($is_render) {
        die("<div style='padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>
                <h3>🔧 Problema de Conexión a Base de Datos</h3>
                <p><strong>Configuración detectada:</strong></p>
                <ul>
                    <li>Host: $host</li>
                    <li>Base de datos: $dbname</li>
                    <li>Usuario: $username</li>
                    <li>Puerto: $port</li>
                    <li>¿Contraseña configurada?: " . (empty($password) ? 'NO' : 'SÍ') . "</li>
                </ul>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><small>Si el problema persiste, verifica que la base de datos exista y el usuario tenga permisos.</small></p>
            </div>");
    } else {
        die("Error de conexión a la base de datos local.");
    }
}

// Función para mostrar imágenes
function mostrarImagen($imagen_data, $alt = "Producto", $width = 100) {
    if (!empty($imagen_data)) {
        $base64 = base64_encode($imagen_data);
        return "<img src='data:image/jpeg;base64,$base64' 
                alt='$alt' 
                style='width: {$width}px; height: auto; border-radius: 8px;'
                class='img-thumbnail'>";
    }
    return "<div class='text-center text-muted' 
                style='width: {$width}px; height: {$width}px; 
                       display: flex; align-items: center; justify-content: center; 
                       background: #f8f9fa; border-radius: 8px;'>
                <i class='fas fa-image fa-2x'></i>
            </div>";
}
?>