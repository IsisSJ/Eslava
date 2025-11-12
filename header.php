<?php
// No iniciar sesión aquí, ya debe estar iniciada en los archivos que incluyen header.php
$usuario = $_SESSION['usuario'] ?? '';
$rol = $_SESSION['rol'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Obtener foto de perfil del usuario solo si hay usuario logueado
$foto_perfil = '';
if ($user_id && file_exists('conexion.php')) {
    // Incluir conexión de forma segura
    @include_once("conexion.php");
    
    // Verificar si $conn existe y está conectada
    if (isset($conn) && $conn && $conn->connect_error === null) {
        $stmt = $conn->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result) {
                    $user_data = $result->fetch_assoc();
                    $foto_perfil = $user_data['foto_perfil'] ?? '';
                }
            }
            $stmt->close();
        }
    }
}
?>

<style>
  .topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background-color: #495057;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 9999;
    padding: 0 20px;
  }

  .topbar a {
    color: #ced4da;
    background-color: #6c757d;
    padding: 8px 15px;
    border-radius: 7px;
    font-weight: bold;
    text-decoration: none;
    transition: background-color 0.3s ease;
    font-size: 14px;
  }

  .topbar a:hover {
    background-color: #adb5bd;
    color: #212529;
  }

  .user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
    color: #f8f9fa;
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #adb5bd;
  }

  .avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    border: 2px solid #adb5bd;
  }

  .welcome {
    color: #f8f9fa;
    font-weight: 600;
    margin-right: auto;
  }
</style>

<div class="topbar">
  <div class="welcome">
    <?php if ($usuario): ?>
      Bienvenido, <strong><?= htmlspecialchars($usuario) ?></strong> (<?= htmlspecialchars($rol) ?>)
    <?php endif; ?>
  </div>
  
  <?php if ($rol === 'admin'): ?>
    <a href="usuarios.php">Usuarios</a>
    <a href="articulos.php">Productos</a>
    <a href="bitacora.php">Bitácora</a>
  <?php elseif ($rol === 'cliente'): ?>
    <a href="productos.php">Productos</a>
    <a href="carrito.php">Carrito</a>
    <a href="mis_pedidos.php">Mis Pedidos</a>
  <?php elseif ($rol === 'consultor'): ?>
    <a href="productos.php">Productos</a>
    <a href="bitacora.php">Bitácora</a>
  <?php endif; ?>
  
  <?php if ($usuario): ?>
    <div class="user-info">
      <?php if (!empty($foto_perfil)): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($foto_perfil) ?>" 
             class="user-avatar" 
             alt="<?= htmlspecialchars($usuario) ?>">
      <?php else: ?>
        <div class="avatar-placeholder">
          <?= strtoupper(substr($usuario, 0, 1)) ?>
        </div>
      <?php endif; ?>
      
      <div>
        <a href="editar_perfil.php" class="btn btn-outline-light btn-sm">Editar Perfil</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
      </div>
    </div>
  <?php endif; ?>
</div>