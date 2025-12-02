<?php
// conexion.php - VERSIÓN SEGURA Y ROBUSTA

// Configuración para Clever Cloud MySQL
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK'; // ⚠️ REEMPLAZA CON TU CONTRASEÑA REAL
$port = '3306';

// Intentar conexión con manejo de errores mejorado
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // Opciones de PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    // Crear conexión
    $conn = new PDO($dsn, $username, $password, $options);
    
    // Verificar que la conexión funcione
    $conn->query("SELECT 1");
    
    // Para debug
    error_log("✅ Conexión a MySQL establecida correctamente");
    
} catch (PDOException $e) {
    // Registrar error pero no mostrar detalles al usuario
    error_log("❌ ERROR DE CONEXIÓN MySQL: " . $e->getMessage());
    
    // Mensaje genérico para el usuario
    die("Lo sentimos, hay problemas técnicos. Por favor intenta más tarde.");
}

// Función auxiliar para mostrar imágenes
function mostrarImagen($imagen_data, $alt = "Producto", $width = 100) {
    if (!empty($imagen_data)) {
        $base64 = base64_encode($imagen_data);
        // Intentar detectar el tipo MIME
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $imagen_data);
            finfo_close($finfo);
        } else {
            // Fallback a JPEG
            $mime_type = 'image/jpeg';
        }
        return "<img src='data:$mime_type;base64,$base64' 
                alt='$alt' 
                style='width: {$width}px; height: auto; border-radius: 8px;'
                class='img-thumbnail'>";
    }
    return "<div class='text-center text-muted' style='width: {$width}px; height: {$width}px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px;'>
                <i class='fas fa-image fa-2x'></i>
            </div>";
}

// Función para sanitizar entrada
function limpiarInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para redirigir con mensaje
function redirigirConMensaje($url, $tipo = 'success', $mensaje = '') {
    $_SESSION[$tipo == 'error' ? 'error' : 'mensaje'] = $mensaje;
    header("Location: $url");
    exit();
}
?>