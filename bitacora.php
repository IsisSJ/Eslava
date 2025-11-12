<?php
// SOLO UNA VEZ session_start() al inicio del archivo
session_start();
include_once('conexion.php');

// Verificar login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Buscar info del usuario
$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $usuario_id = $user['id'] ?? null;
} else {
    $usuario_id = null;
}

// Insertar registro en bitácora
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hora = date('H:i:s');
    $fecha = date('Y-m-d');
    $tipo = $conn->real_escape_string($_POST['tipo']);

    if ($usuario_id) {
        $sql_insert = "INSERT INTO bitacora (usuario_id, fecha, hora, tipo) VALUES (?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql_insert);
        if ($stmt2) {
            $stmt2->bind_param("isss", $usuario_id, $fecha, $hora, $tipo);

            if ($stmt2->execute()) {
                $mensaje = "<p class='success'>🌸 Bitácora registrada correctamente.</p>";
            } else {
                $mensaje = "<p class='error'>Error: " . $conn->error . "</p>";
            }
            $stmt2->close();
        }
    }
}

// Consultar registros de bitácora del usuario
$result_bitacora = null;
if ($usuario_id) {
    $sql_bitacora = "SELECT * FROM bitacora WHERE usuario_id = ? ORDER BY fecha DESC, hora DESC";
    $stmt3 = $conn->prepare($sql_bitacora);
    if ($stmt3) {
        $stmt3->bind_param("i", $usuario_id);
        $stmt3->execute();
        $result_bitacora = $stmt3->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora - Chinampas</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to bottom, #88c999, #b3e6d1);
            background-attachment: fixed;
        }

        .main-content {
            padding: 120px 40px 40px;
            max-width: 950px;
            margin: auto;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            position: relative;
        }

        h1 {
            text-align: center;
            color: #2f5233;
            margin-bottom: 20px;
        }

        .success {
            color: #2e7d32;
            font-weight: bold;
            text-align: center;
        }

        .error {
            color: #c62828;
            font-weight: bold;
            text-align: center;
        }

        form {
            text-align: center;
            margin: 20px auto;
        }

        label {
            font-weight: 600;
            margin-right: 10px;
            color: #2f5233;
        }

        input[type="text"] {
            padding: 10px;
            width: 280px;
            border-radius: 8px;
            border: 1px solid #7fbf9d;
            outline: none;
        }

        button {
            margin-left: 15px;
            padding: 10px 20px;
            background-color: #388e3c;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #2e7d32;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: center;
        }

        th {
            background-color: #388e3c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2fdf5;
        }

        tr:hover {
            background-color: #d7f5e5;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
        }

        .footer a {
            text-decoration: none;
            padding: 10px 18px;
            background: #2f5233;
            color: white;
            border-radius: 8px;
            transition: 0.3s;
        }

        .footer a:hover {
            background: #244026;
        }

        /* Detalles decorativos chinampa */
        .flower {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 35px;
        }
        .canoe {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 35px;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="main-content">
        <span class="flower">🌸</span>
        <span class="canoe">🛶</span>
        <h1>Bitácora de <?= htmlspecialchars($usuario) ?> 🌿</h1>

        <?= isset($mensaje) ? $mensaje : '' ?>

        <form method="POST">
            <label for="tipo">Tipo:</label>
            <input type="text" name="tipo" id="tipo" placeholder="Ej: Ingreso, Salida..." required>
            <button type="submit">Registrar</button>
        </form>

        <?php if ($result_bitacora): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_bitacora->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fecha']) ?></td>
                            <td><?= htmlspecialchars($row['hora']) ?></td>
                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No hay registros en la bitácora.</p>
        <?php endif; ?>

        <div class="footer">
            <a href="<?= ($_SESSION['rol'] === 'admin') ? 'admin_dashboard.php' : 'menu.php' ?>">Volver al panel</a>
        </div>
    </div>
</body>
</html>

<?php
// ELIMINAR ESTA PARTE DUPLICADA DEL ARCHIVO
// Todo el código de bitácora para admin debe estar en otro archivo separado
// o integrado arriba con verificaciones de rol
?>

<?php
// ESTO ES LO QUE CAUSA EL ERROR - COMENTAR O ELIMINAR ESTA SECCIÓN
/*
include("conexion.php");
session_start(); // ← ESTE ES EL session_start() DUPLICADO

// Solo admin
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// --- ACCIONES RÁPIDAS ---
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if ($accion == "aceptar") {
        $conn->query("UPDATE bitacora SET estado='aceptado' WHERE id=$id");
    } elseif ($accion == "denegar") {
        $conn->query("UPDATE bitacora SET estado='denegado' WHERE id=$id");
    } elseif ($accion == "pendiente") {
        $conn->query("UPDATE bitacora SET estado='pendiente' WHERE id=$id");
    } elseif ($accion == "eliminar") {
        $conn->query("DELETE FROM bitacora WHERE id=$id");
    }

    header("Location: bitacora.php");
    exit();
}

// --- LISTAR BITÁCORA ---
$result = $conn->query("SELECT * FROM bitacora ORDER BY fecha_hora DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Accesos</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 100%; background: white; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #34495e; color: white; }
        img { border-radius: 6px; }
        .btn { padding: 5px 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin: 2px; }
        .btn-aceptar { background: #27ae60; color: white; }
        .btn-denegar { background: #c0392b; color: white; }
        .btn-pendiente { background: #2980b9; color: white; }
        .btn-eliminar { background: #e67e22; color: white; }
    </style>
</head>
<body>
    <h2>📑 Bitácora de Accesos</h2>
    <a href="panel_admin.php">⬅ Volver al panel</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Password</th>
            <th>Fecha/Hora</th>
            <th>Imagen</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['usuario']; ?></td>
            <td><?php echo $row['pw']; ?></td>
            <td><?php echo $row['fecha_hora']; ?></td>
            <td>
                <?php if (!empty($row['imagen'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" width="60">
                <?php else: ?>
                    Sin foto
                <?php endif; ?>
            </td>
            <td><?php echo $row['estado']; ?></td>
            <td>
                <a href="bitacora.php?accion=aceptar&id=<?php echo $row['id']; ?>">
                    <button class="btn btn-aceptar">Aceptar</button>
                </a>
                <a href="bitacora.php?accion=denegar&id=<?php echo $row['id']; ?>">
                    <button class="btn btn-denegar">Denegar</button>
                </a>
                <a href="bitacora.php?accion=pendiente&id=<?php echo $row['id']; ?>">
                    <button class="btn btn-pendiente">Pendiente</button>
                </a>
                <a href="bitacora.php?accion=eliminar&id=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar este registro?')">
                    <button class="btn btn-eliminar">Eliminar</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
*/
?>