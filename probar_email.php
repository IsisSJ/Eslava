<?php
// probar_email.php
session_start();

// Simular carrito para pruebas
$_SESSION['carrito'] = [
    [
        'id' => 1,
        'nombre' => 'Rosa Roja',
        'precio' => 25.00,
        'cantidad' => 2,
        'stock' => 10
    ],
    [
        'id' => 2, 
        'nombre' => 'Girasol',
        'precio' => 30.00,
        'cantidad' => 1,
        'stock' => 5
    ]
];

$_SESSION['usuario'] = 'usuario_prueba';
$_SESSION['email_temporal'] = 'isiszenith@gmail.com'; // ← PON TU CORREO REAL AQUÍ

header('Location: enviar_ticket.php?metodo=tarjeta');
exit();
?>