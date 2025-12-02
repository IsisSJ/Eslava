<?php
// conexion.php - Conexión a Base de Datos para Flores de Chinampa
// NO incluye configuración de sesiones (eso va en config_session.php)

// ============================================
// DETECCIÓN DE ENTORNO
// ============================================
$is_render = getenv('RENDER') ? true : false;
$is_clever_cloud = getenv('MYSQL_ADDON_HOST') ? true : false;

// ============================================
// CONFIGURACIÓN DE CREDENCIALES
// ============================================
if ($is_render) {
    // ✅ CONFIGURACIÓN PARA RENDER + CLEVER CLOUD
    // En Render, las variables tienen nombres diferentes
    $host = getenv('DB_NAME') ?: 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
    $dbname = 'bc8i4pda2kn2fqs150qm';
    $username = 'uo5qglcqiyhjhqot';
    $password = getenv('DB_PASS') ?: 'wSlvgtI1vH86LAydhriK'; // Contraseña real encontrada
    $port = getenv('DB_PORT') ?: '3306';
    
} elseif ($is_clever_cloud) {
    // Configuración directa Clever Cloud (si se usa directamente)
    $host = getenv('MYSQL_ADDON_HOST');
    $dbname = getenv('MYSQL_ADDON_DB');
    $username = getenv('MYSQL_ADDON_USER');
    $password = getenv('MYSQL_ADDON_PASSWORD');
    $port = getenv('MYSQL_ADDON_PORT') ?: '3306';
    
} else {
    // Configuración local para desarrollo (XAMPP/MAMP)
    $host = 'localhost';
    $dbname = 'flores_chinampa';
    $username = 'root';
    $password = '';
    $port = '3306';
}

// ============================================
// ESTABLECER CONEXIÓN PDO
// ============================================
try {
    // Crear DSN (Data Source Name)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // Opciones de PDO para mejor rendimiento y seguridad
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Lanzar excepciones en errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devolver arrays asociativos
        PDO::ATTR_EMULATE_PREPARES => false,              // Usar prepared statements nativos
        PDO::ATTR_PERSISTENT => false,                    // No usar conexiones persistentes
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        PDO::ATTR_TIMEOUT => 5,                           // Timeout de 5 segundos
    ];
    
    // Crear instancia PDO
    $conn = new PDO($dsn, $username, $password, $options);
    
    // Verificar que la conexión funciona
    $conn->query("SELECT 1");
    
    // Log de éxito (solo en desarrollo)
    if ($is_render) {
        error_log("✅ Conexión exitosa a Clever Cloud MySQL desde Render");
    } elseif ($is_clever_cloud) {
        error_log("✅ Conexión exitosa a Clever Cloud MySQL");
    } else {
        error_log("✅ Conexión exitosa a MySQL local");
    }
    
} catch (PDOException $e) {
    // Log del error con detalles
    error_log("❌ ERROR DE CONEXIÓN MySQL:");
    error_log("- Mensaje: " . $e->getMessage());
    error_log("- Código: " . $e->getCode());
    error_log("- Configuración usada:");
    error_log("  Host: $host");
    error_log("  DB: $dbname");
    error_log("  User: $username");
    error_log("  Port: $port");
    error_log("  Entorno: " . ($is_render ? 'Render' : ($is_clever_cloud ? 'Clever Cloud' : 'Local')));
    
    // Mensaje amigable para el usuario
    $error_html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de Conexión</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                font-family: Arial, sans-serif;
            }
            .error-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-card">
                <h2 class="text-danger">❌ Error de Conexión a la Base de Datos</h2>
                <p class="lead">Lo sentimos, no se pudo conectar a la base de datos en este momento.</p>
                
                <div class="alert alert-info">
                    <h5>Información técnica:</h5>
                    <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                    ' . ($is_render ? '
                    <p><strong>Entorno:</strong> Render + Clever Cloud</p>
                    <p>Verifica que las variables de entorno en Render estén configuradas correctamente.</p>
                    ' : '') . '
                </div>
                
                <div class="mt-4">
                    <h5>¿Qué puedes hacer?</h5>
                    <ol>
                        <li>Verificar que la base de datos esté activa en Clever Cloud</li>
                        <li>Revisar las credenciales en el archivo <code>conexion.php</code></li>
                        <li>Esperar unos minutos e intentar nuevamente</li>
                        <li>Contactar al administrador del sistema</li>
                    </ol>
                </div>
                
                <div class="mt-4">
                    <a href="login_working.php" class="btn btn-primary">Intentar nuevamente</a>
                    <a href="test_conexion.php" class="btn btn-info">Probar conexión</a>
                    ' . ($is_render ? '
                    <a href="debug_login.php" class="btn btn-warning">Debug del sistema</a>
                    ' : '') . '
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    die($error_html);
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Muestra una imagen desde datos binarios de la base de datos
 * 
 * @param string $imagen_data Datos binarios de la imagen
 * @param string $alt Texto alternativo para la imagen
 * @param int $width Ancho de la imagen en píxeles
 * @return string HTML de la imagen o placeholder
 */
function mostrarImagen($imagen_data, $alt = "Producto", $width = 100) {
    if (!empty($imagen_data)) {
        $base64 = base64_encode($imagen_data);
        
        // Intentar detectar el tipo MIME
        $mime_type = 'image/jpeg'; // Valor por defecto
        
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $imagen_data);
            finfo_close($finfo);
        }
        
        return "<img src='data:$mime_type;base64,$base64' 
                alt='" . htmlspecialchars($alt) . "' 
                style='width: {$width}px; height: auto; border-radius: 8px;'
                class='img-thumbnail'>";
    }
    
    // Placeholder si no hay imagen
    return "<div class='text-center text-muted' 
                style='width: {$width}px; height: {$width}px; 
                       display: flex; align-items: center; justify-content: center; 
                       background: #f8f9fa; border-radius: 8px;'>
                <i class='fas fa-image fa-2x'></i>
            </div>";
}

/**
 * Limpia y sanitiza datos de entrada
 * 
 * @param mixed $data Datos a limpiar
 * @return mixed Datos sanitizados
 */
function limpiarInput($data) {
    if (is_array($data)) {
        return array_map('limpiarInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Ejecuta una consulta con manejo de errores
 * 
 * @param PDO $conn Conexión PDO
 * @param string $sql Consulta SQL
 * @param array $params Parámetros para prepared statement
 * @return PDOStatement|false Resultado de la consulta
 */
function ejecutarConsulta($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("❌ Error en consulta SQL: " . $e->getMessage());
        error_log("Consulta: $sql");
        error_log("Parámetros: " . print_r($params, true));
        return false;
    }
}

/**
 * Convierte un resultado PDO a array HTML para debug
 * 
 * @param PDOStatement $stmt Resultado de consulta
 * @return string HTML con la tabla de resultados
 */
function resultadosATablaHTML($stmt) {
    if (!$stmt) {
        return "<p class='text-danger'>No hay resultados</p>";
    }
    
    $resultados = $stmt->fetchAll();
    
    if (empty($resultados)) {
        return "<p class='text-muted'>No se encontraron registros</p>";
    }
    
    $html = '<table class="table table-striped table-sm">';
    $html .= '<thead><tr>';
    
    // Cabeceras
    foreach (array_keys($resultados[0]) as $columna) {
        $html .= '<th>' . htmlspecialchars($columna) . '</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    // Filas
    foreach ($resultados as $fila) {
        $html .= '<tr>';
        foreach ($fila as $valor) {
            $html .= '<td>' . htmlspecialchars($valor) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

// ============================================
// VERIFICACIÓN INICIAL (OPCIONAL - solo para debug)
// ============================================
if (isset($_GET['debug_db']) && $_GET['debug_db'] == 'true') {
    // Solo se ejecuta si se solicita explícitamente
    try {
        $stmt = $conn->query("SELECT 
                (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                (SELECT COUNT(*) FROM articulos) as total_articulos,
                (SELECT COUNT(*) FROM pedidos) as total_pedidos,
                VERSION() as mysql_version");
        
        $info = $stmt->fetch();
        
        error_log("=== INFO BASE DE DATOS ===");
        error_log("- Usuarios: " . $info['total_usuarios']);
        error_log("- Artículos: " . $info['total_articulos']);
        error_log("- Pedidos: " . $info['total_pedidos']);
        error_log("- MySQL: " . $info['mysql_version']);
        
    } catch (Exception $e) {
        error_log("Error en verificación inicial: " . $e->getMessage());
    }
}

// Nota: La conexión $conn está ahora disponible para todo el script
?>