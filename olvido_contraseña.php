<?php
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "Te hemos enviado un enlace para cambiar tu contraseña a $correo";
    } else {
        echo "Correo no encontrado";
    }
}
?>

<form method="POST">
    <label for="correo">Correo:</label>
    <input type="email" name="correo" id="correo" required>
    <button type="submit">Enviar enlace de recuperación</button>
</form>
