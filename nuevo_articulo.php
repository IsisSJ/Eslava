<?php
session_start();
include_once("conexion.php");

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Crear directorio de imágenes si no existe
$upload_dir = "images/productos/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Procesar el formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $descripcion = trim($_POST['descripcion']);
    
    $imagen_path = null;
    $imagen_nombre = null;
    
    echo "<!-- Debug: Formulario recibido -->";
    echo "<!-- Debug: Nombre: $nombre, Precio: $precio, Stock: $stock -->";
    
    // Procesar imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        echo "<!-- Debug: Archivo - Nombre: $file_name, Tamaño: $file_size, Error: $file_error -->";
        
        // Validar tipo de archivo por extensión
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Validar tamaño (máximo 2MB)
            if ($file_size <= 2 * 1024 * 1024) {
                // Generar nombre único
                $new_file_name = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_file_name;
                
                echo "<!-- Debug: Intentando guardar en: $upload_path -->";
                
                // Mover archivo
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $imagen_path = $upload_path;
                    $imagen_nombre = $file_name;
                    echo "<!-- Debug: Imagen guardada exitosamente -->";
                } else {
                    $error = "Error al guardar la imagen en el servidor";
                    echo "<!-- Debug: Error al mover archivo -->";
                }
            } else {
                $error = "La imagen es demasiado grande (máximo 2MB)";
            }
        } else {
            $error = "Formato de imagen no permitido. Usa JPG, PNG o GIF";
        }
    } else {
        echo "<!-- Debug: No se subió imagen o hubo error: " . ($_FILES['imagen']['error'] ?? 'N/A') . " -->";
    }
    
    // Validar datos requeridos
    if (empty($nombre)) {
        $error = "El nombre del artículo es requerido";
    } elseif ($precio <= 0) {
        $error = "El precio debe ser mayor a 0";
    } elseif ($stock < 0) {
        $error = "El stock no puede ser negativo";
    }
    
    // Insertar en base de datos
    if (empty($error)) {
        if ($imagen_path) {
            // Insertar con imagen
            $stmt = $conn->prepare("INSERT INTO articulos (nombre, precio, stock, descripcion, imagen_path, imagen_nombre) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdisss", $nombre, $precio, $stock, $descripcion, $imagen_path, $imagen_nombre);
        } else {
            // Insertar sin imagen
            $stmt = $conn->prepare("INSERT INTO articulos (nombre, precio, stock, descripcion) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdis", $nombre, $precio, $stock, $descripcion);
        }
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "✅ Artículo creado correctamente" . ($imagen_path ? " con imagen" : "");
            $_SESSION['tipo_mensaje'] = "success";
            header('Location: gestion_articulos.php');
            exit();
        } else {
            $error = "Error al crear el artículo: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Artículo - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 80px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            border: 2px dashed #28a745;
            border-radius: 8px;
            padding: 5px;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success">
                <i class="fas fa-plus me-2"></i>Nuevo Artículo
            </h2>
            <a href="gestion_articulos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Información del Artículo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="formArticulo">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre del Artículo *</label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                                       placeholder="Ej: Rosa Roja" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3" 
                                          placeholder="Describe el artículo..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Precio *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" class="form-control" name="precio" 
                                                   value="<?php echo $_POST['precio'] ?? ''; ?>" 
                                                   placeholder="0.00" min="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Stock *</label>
                                        <input type="number" class="form-control" name="stock" 
                                               value="<?php echo $_POST['stock'] ?? ''; ?>" 
                                               placeholder="0" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Imagen del Producto</label>
                                <div class="border rounded p-3 bg-light">
                                    <input type="file" class="form-control" name="imagen" id="imagen" 
                                           accept="image/jpeg, image/jpg, image/png, image/gif" 
                                           onchange="previewImage(this)">
                                    
                                    <img id="imagePreview" class="image-preview mt-2">
                                    
                                    <div class="form-text mt-2">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Formatos: JPG, PNG, GIF<br>
                                            Tamaño máximo: 2MB
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Consejo:</strong> Una buena imagen ayuda a vender más
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Guardar Artículo
                        </button>
                        <a href="gestion_articulos.php" class="btn btn-secondary btn-lg">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                // Validar tamaño
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 2MB.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Formato no permitido. Usa JPG, PNG o GIF.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Validación del formulario
        document.getElementById('formArticulo').addEventListener('submit', function(e) {
            const precio = document.querySelector('input[name="precio"]');
            const stock = document.querySelector('input[name="stock"]');
            
            if (parseFloat(precio.value) <= 0) {
                alert('El precio debe ser mayor a 0');
                e.preventDefault();
                return false;
            }
            
            if (parseInt(stock.value) < 0) {
                alert('El stock no puede ser negativo');
                e.preventDefault();
                return false;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>