// Función para generar URL correcta del producto - VERSIÓN MEJORADA
function generarURLProducto($articulo_id) {
    global $CONFIG;
    
    // DEBUG: Verificar entorno
    error_log("🔍 Generando URL para producto $articulo_id");
    error_log("🔍 Dominio configurado: " . $CONFIG['dominio_produccion']);
    error_log("🔍 Modo desarrollo: " . ($CONFIG['modo_desarrollo'] ? 'true' : 'false'));
    error_log("🔍 HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'No definido'));
    
    // SIEMPRE usar el dominio de producción en Render
    // Render tiene variables de entorno específicas
    if (isset($_SERVER['RENDER']) || 
        $_SERVER['HTTP_HOST'] === 'eslava-3.onrender.com' ||
        !$CONFIG['modo_desarrollo']) {
        
        $url = $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $articulo_id;
        error_log("✅ URL generada (Producción): " . $url);
        return $url;
    }
    
    // Solo para desarrollo local (XAMPP, localhost, etc.)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Limpiar el host (remover puerto si existe)
    $host = preg_replace('/:\d+$/', '', $host);
    
    // Evitar localhost, 127.0.0.1, ::1
    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
        error_log("⚠️  Detectado localhost, forzando dominio de producción");
        return $CONFIG['dominio_produccion'] . "/ver_producto.php?id=" . $articulo_id;
    }
    
    $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $url = $protocol . "://" . $host . $base_path . "/ver_producto.php?id=" . $articulo_id;
    
    error_log("🔧 URL generada (Desarrollo): " . $url);
    return $url;
}