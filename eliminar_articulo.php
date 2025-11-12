<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté autenticado y sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Primero obtenemos el nombre del artículo para el mensaje
        $stmt = $conn->prepare("SELECT nombre FROM articulos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $articulo = $result->fetch_assoc();
        
        if ($articulo) {
            // Preparar la consulta de eliminación
            $stmt = $conn->prepare("DELETE FROM articulos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Artículo '{$articulo['nombre']}' eliminado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el artículo";
                $_SESSION['tipo_mensaje'] = "error";
            }
        } else {
            $_SESSION['mensaje'] = "Artículo no encontrado";
            $_SESSION['tipo_mensaje'] = "error";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    // Redirigir de vuelta a la gestión de artículos
    header('Location: gestion_articulos.php');
    exit();
} else {
    header('Location: gestion_articulos.php');
    exit();
}
?>