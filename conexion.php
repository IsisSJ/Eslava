<?php
// Configuración de sesiones para Render
if (getenv('RENDER')) {
    // En Render, usar sesiones de archivo en /tmp
    ini_set('session.save_path', '/tmp');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
}

// conexion.php - CONFIGURACIÓN PARA CLEVER CLOUD
// ⚠️ ⚠️ ⚠️ IMPORTANTE: REEMPLAZA 'AQUI_LA_CONTRASEÑA_REAL' CON TU CONTRASEÑA DE CLEVER CLOUD ⚠️ ⚠️ ⚠️

// Configuración para Clever Cloud (OBTÉN ESTOS DATOS DE TU CONSOLA)
$host = 'bc8i4pda2kn2fqs150qm-mysql.services.clever-cloud.com';
$dbname = 'bc8i4pda2kn2fqs150qm';
$username = 'uo5qglcqiyhjhqot';
$password = 'wSlvgtI1vH86LAydhriK'; // ⚠️ ⚠️ ⚠️ ¡¡¡REEMPLAZA ESTO!!!
$port = '3306';

// Si las variables de entorno existen, usarlas (para Render)
if (getenv('MYSQL_ADDON_HOST')) {
    $host = getenv('MYSQL_ADDON_HOST');
    $dbname = getenv('MYSQL_ADDON_DB');
    $username = getenv('MYSQL_ADDON_USER');
    $password = getenv('MYSQL_ADDON_PASSWORD');
    $port = getenv('MYSQL_ADDON_PORT') ?: '3306';
}

// Intentar conexión
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
    
    // Test de conexión
    $conn->query("SELECT 1");
    
    error_log("✅ Conexión a MySQL exitosa: $host");
    
} catch (PDOException $e) {
    error_log("❌ ERROR DE CONEXIÓN MySQL: " . $e->getMessage());
    error_log("Detalles: host=$host, user=$username, db=$dbname");
    
    // Mensaje amigable
    die("<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px;'>
            <h3>❌ Error de Conexión a la Base de Datos</h3>
            <p>No se pudo conectar a la base de datos. Por favor:</p>
            <ol>
                <li>Verifica que las credenciales en <code>conexion.php</code> sean correctas</li>
                <li>Revisa tu conexión a internet</li>
                <li>Contacta al administrador del sistema</li>
            </ol>
            <p><small>Error técnico: " . htmlspecialchars($e->getMessage()) . "</small></p>
            <p><a href='clever_credentials_test.php'>🔧 Verificar credenciales</a></p>
        </div>");
}
?>