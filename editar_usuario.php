<?php
include_once("conexion.php");
session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// Si viene un ID por GET mostramos formulario
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM usuarios WHERE id = $id");

    if ($row = $result->fetch_assoc()) {
        ?>
        <h2>Editar Usuario</h2>
        <form action="editar_usuario.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="<?php echo $row['nombre']; ?>" required><br><br>

            <label>Correo:</label><br>
            <input type="email" name="correo" value="<?php echo $row['correo']; ?>" required><br><br>

            <label>Rol:</label><br>
            <select name="rol" required>
                <option value="admin" <?php if ($row['rol']=="admin") echo "selected"; ?>>Admin</option>
                <option value="cliente" <?php if ($row['rol']=="cliente") echo "selected"; ?>>Cliente</option>
            </select><br><br>

            <label>Foto:</label><br>
            <input type="file" name="foto" accept="image/*"><br><br>

            <input type="submit" name="update" value="Guardar cambios">
        </form>
        <a href="panel_admin.php">Volver</a>
        <?php
    } else {
        echo "Usuario no encontrado.";
    }
}

// Si viene formulario por POST actualizamos
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Si subió nueva foto
    if (isset($_FILES['foto']) && $_FILES['foto']['tmp_name'] != "") {
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=?, foto=? WHERE id=?");
        $stmt->bind_param("ssssi", $nombre, $correo, $rol, $foto, $id);
        $stmt->send_long_data(3, $foto);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Usuario actualizado correctamente');window.location='panel_admin.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>
