<?php
include("conexion.php");
session_start();

// Validar que solo admin pueda entrar
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// Si viene el ID por GET mostramos el formulario
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM bitacora WHERE id = $id");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Actualizar Bitácora</title>
        </head>
        <body>
            <h2>Actualizar registro de Bitácora</h2>
            <form action="actualizar_bitacora.php" method="post">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                <p><b>Usuario:</b> <?php echo $row['usuario']; ?></p>
                <p><b>Password:</b> <?php echo $row['pw']; ?></p>
                <p><b>Fecha/Hora:</b> <?php echo $row['fecha_hora']; ?></p>

                <?php
                if (!empty($row['imagen'])) {
                    $imgData = base64_encode($row['imagen']);
                    echo "<p><b>Imagen:</b><br><img src='data:image/jpeg;base64,$imgData' width='100'></p>";
                }
                ?>

                <label>Estado:</label>
                <select name="estado" required>
                    <option value="pendiente" <?php if($row['estado']=="pendiente") echo "selected"; ?>>Pendiente</option>
                    <option value="aceptado" <?php if($row['estado']=="aceptado") echo "selected"; ?>>Aceptado</option>
                    <option value="denegado" <?php if($row['estado']=="denegado") echo "selected"; ?>>Denegado</option>
                </select>
                <br><br>

                <label>Eliminar:</label>
                <select name="eliminar" required>
                    <option value="NO" <?php if($row['eliminar']=="NO") echo "selected"; ?>>NO</option>
                    <option value="SI" <?php if($row['eliminar']=="SI") echo "selected"; ?>>SI</option>
                </select>
                <br><br>

                <input type="submit" name="update" value="Guardar cambios">
            </form>
            <br>
            <a href="bitacora.php">Volver a Bitácora</a>
        </body>
        </html>
        <?php
    } else {
        echo "No se encontró el registro.";
    }
}

// Si viene el formulario por POST actualizamos
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];
    $eliminar = $_POST['eliminar'];

    $stmt = $conn->prepare("UPDATE bitacora SET estado = ?, eliminar = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado, $eliminar, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Registro actualizado correctamente');window.location='bitacora.php';</script>";
    } else {
        echo "Error al actualizar: " . $conn->error;
    }

    $stmt->close();
}
?>
