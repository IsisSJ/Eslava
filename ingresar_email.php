<?php
session_start();
if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Ticket - Flores de Chinampa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .destinatario-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="email-container">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Enviar Comprobante</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Envía el ticket a quien quieras</h6>
                        <p class="mb-0">Puedes enviarlo a tu correo, a un familiar, amigo, o cualquier dirección de email.</p>
                    </div>
                    
                    <form action="procesar_email_multiple.php" method="POST" id="formEmail">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-users me-2"></i>Destinatarios del Comprobante
                            </label>
                            
                            <!-- Destinatario principal -->
                            <div class="destinatario-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control" name="emails[]" 
                                               placeholder="ejemplo@gmail.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre (Opcional)</label>
                                        <input type="text" class="form-control" name="nombres[]"
                                               placeholder="Nombre del destinatario">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Destinatarios adicionales -->
                            <div id="destinatarios-adicionales"></div>
                            
                            <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="agregarDestinatario()">
                                <i class="fas fa-plus me-1"></i>Agregar otro destinatario
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-comment me-2"></i>Mensaje Personal (Opcional)
                            </label>
                            <textarea class="form-control" name="mensaje_personal" rows="3" 
                                      placeholder="Agrega un mensaje personal para el destinatario..."></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Comprobante(s)
                            </button>
                            <a href="carrito.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Carrito
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let contadorDestinatarios = 1;
        
        function agregarDestinatario() {
            const container = document.getElementById('destinatarios-adicionales');
            const nuevoDestinatario = `
                <div class="destinatario-item" id="destinatario-${contadorDestinatarios}">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="emails[]" 
                                   placeholder="otro@ejemplo.com">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nombre (Opcional)</label>
                            <input type="text" class="form-control" name="nombres[]"
                                   placeholder="Nombre del destinatario">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="eliminarDestinatario(${contadorDestinatarios})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += nuevoDestinatario;
            contadorDestinatarios++;
        }
        
        function eliminarDestinatario(id) {
            const elemento = document.getElementById(`destinatario-${id}`);
            if (elemento) {
                elemento.remove();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>