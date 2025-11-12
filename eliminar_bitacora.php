<?php
include("conexion.php");
session_start();

// Validar que solo admin pueda entrar
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// Verificar si se pasa el ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Primero verificamos si el registro existe
    $result = $conn->query("SELECT * FROM bitacora WHERE id = $id");

    if ($result->num_rows > 0) {
        // Eliminamos de la BD
        $delete = $conn->query("DELETE FROM bitacora WHERE id = $id");

        if ($delete) {
            echo "<script>alert('Registro eliminado correctamente');window.location='bitacora.php';</script>";
        } else {
            echo "Error al eliminar: " . $conn->error;
        }
    } else {
        echo "El registro con ID $id no existe.";
    }
} else {
    echo "No se especificó ningún ID.";
}
?>
