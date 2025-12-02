<?php
// conexion.php - Agregar función para mostrar imágenes
function mostrarImagen($imagen_data, $alt = "Producto", $width = 100) {
    if (!empty($imagen_data)) {
        // Detectar tipo de imagen
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($finfo, $imagen_data);
        finfo_close($finfo);
        
        $base64 = base64_encode($imagen_data);
        return "<img src='data:$mime_type;base64,$base64' 
                alt='$alt' 
                style='width: {$width}px; height: auto; border-radius: 8px;'
                class='img-thumbnail'>";
    }
    return "<div class='text-center text-muted' style='width: {$width}px; height: {$width}px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px;'>
                <i class='fas fa-image fa-2x'></i>
            </div>";
}
?>