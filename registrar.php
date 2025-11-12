<?php
include("conexion.php");
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $clave   = password_hash($_POST["clave"], PASSWORD_DEFAULT);
    $rol     = $_POST["rol"];

    $sql = "INSERT INTO usuarios (usuario, clave, rol) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("sss", $usuario, $clave, $rol);

    if ($stmt->execute()) {
        $mensaje = "✅ Usuario registrado correctamente";
    } else {
        $mensaje = "❌ Error al registrar: " . $stmt->error;
    }
}
?>
