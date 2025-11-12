<?php
include_once("conexion.php");
session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete = $conn->query("DELETE FROM usuarios WHERE id=$id");

    if ($delete) {
        echo "<script>alert('Usuario eliminado correctamente');window.location='panel_admin.php';</script>";
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
} else {
    echo "ID no especificado.";
}
?>
