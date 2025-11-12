<?php
session_start();
include_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}

require_once('tcpdf/tcpdf.php');

class PDF extends TCPDF {
    // Cabecera del PDF
    public function Header() {
        // Logo
        $image_file = 'logo.png'; // Asegúrate de tener un logo
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 10, 30, 0, 'PNG');
        }
        
        // Título
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Flores de Chinampa', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
    }

    // Pie de página
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear PDF
$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Flores de Chinampa');
$pdf->SetAuthor('Flores de Chinampa');
$pdf->SetTitle('Comprobante de Pedido');
$pdf->SetSubject('Comprobante de Pedido');

$pdf->AddPage();

// Contenido del PDF
$carrito = $_SESSION['carrito'];
$total = 0;

// Información del cliente
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Comprobante de Pedido', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Cliente: ' . $_SESSION['usuario'], 0, 1);
$pdf->Cell(0, 6, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1);
$pdf->Cell(0, 6, 'Método de Pago: ' . ($_GET['metodo'] === 'tarjeta' ? 'Tarjeta' : 'Efectivo'), 0, 1);
$pdf->Ln(10);

// Tabla de productos
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(100, 10, 'Producto', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(30, 10, 'Precio', 1, 0, 'C');
$pdf->Cell(30, 10, 'Subtotal', 1, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
foreach ($carrito as $item) {
    $subtotal = $item['precio'] * $item['cantidad'];
    $total += $subtotal;
    
    // Producto con imagen
    if (!empty($item['imagen'])) {
        // Guardar imagen temporalmente
        $temp_image = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        file_put_contents($temp_image, $item['imagen']);
        
        // Agregar imagen (pequeña)
        $pdf->Cell(100, 20, '', 1, 0);
        $pdf->Image($temp_image, $pdf->GetX() - 100 + 5, $pdf->GetY() + 2, 15, 15, 'JPEG');
        $pdf->SetXY($pdf->GetX() - 100 + 25, $pdf->GetY());
        $pdf->Cell(75, 20, $item['nombre'], 0, 0, 'L');
        $pdf->SetXY($pdf->GetX() + 100, $pdf->GetY() - 20);
        
        // Limpiar archivo temporal
        unlink($temp_image);
    } else {
        $pdf->Cell(100, 10, $item['nombre'], 1, 0);
    }
    
    $pdf->Cell(30, 10, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(30, 10, '$' . number_format($item['precio'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, '$' . number_format($subtotal, 2), 1, 1, 'C');
}

// Totales
$iva = $total * 0.16;
$total_con_iva = $total + $iva;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(160, 10, 'Subtotal:', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($total, 2), 1, 1, 'C');

$pdf->Cell(160, 10, 'IVA (16%):', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($iva, 2), 1, 1, 'C');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(160, 10, 'TOTAL:', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($total_con_iva, 2), 1, 1, 'C');

// Mensaje final
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->MultiCell(0, 10, '¡Gracias por tu compra! Tu pedido será procesado y enviado en un plazo de 2-3 días hábiles.', 0, 'C');

// Descargar PDF
$pdf->Output('comprobante_pedido_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
?>