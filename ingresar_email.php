<?php
session_start();
// Verificar sesión del login
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header('Location: login.php');
    exit();
}

$metodo_pago = $_GET['metodo'] ?? 'efectivo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Emails - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .email-row { margin-bottom: 15px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
        .add-email-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📧 Enviar Ticket por Email</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if (isset($_SESSION['error_email'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_email']; 
                                unset($_SESSION['error_email']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <strong>💡 Puedes enviar el ticket a múltiples personas</strong><br>
                            Agrega los emails de quienes deben recibir el comprobante de compra.
                        </div>

                        <form method="POST" action="procesar_email_multiple.php?metodo=<?php echo $metodo_pago; ?>">
                            <div id="emails-container">
                                <!-- Fila de email 1 -->
                                <div class="email-row">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre del destinatario</label>
                                            <input type="text" class="form-control" name="nombres[]" 
                                                   placeholder="Ej: Juan Pérez" 
                                                   value="<?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="emails[]" 
                                                   placeholder="ejemplo@email.com" required
                                                   value="<?php echo htmlspecialchars($_SESSION['usuario_email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-secondary add-email-btn" id="add-email">
                                ➕ Agregar otro email
                            </button>

                            <div class="mb-3">
                                <label class="form-label">Mensaje personal (opcional)</label>
                                <textarea class="form-control" name="mensaje_personal" rows="3" 
                                          placeholder="Ej: ¡Hola! Te comparto el ticket de mi compra de flores..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    📤 Enviar Tickets
                                </button>
                                <a href="carrito.php" class="btn btn-outline-secondary">
                                    ← Volver al Carrito
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-email').addEventListener('click', function() {
            const container = document.getElementById('emails-container');
            const newRow = document.createElement('div');
            newRow.className = 'email-row';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del destinatario</label>
                        <input type="text" class="form-control" name="nombres[]" placeholder="Ej: María García">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <div class="input-group">
                            <input type="email" class="form-control" name="emails[]" placeholder="ejemplo@email.com" required>
                            <button type="button" class="btn btn-outline-danger remove-email">🗑️</button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newRow);

            // Agregar evento al botón de eliminar
            newRow.querySelector('.remove-email').addEventListener('click', function() {
                container.removeChild(newRow);
            });
        });

        // Agregar eventos a los botones de eliminar existentes
        document.querySelectorAll('.remove-email').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.email-row').remove();
            });
        });
    </script>
</body>
</html>