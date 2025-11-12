<?php
echo "=== SISTEMA DE DIAGNÓSTICO ===<br><br>";

// Verificar archivos en el directorio
echo "📁 ARCHIVOS EN HTDOCS:<br>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file<br>";
    }
}

echo "<br>✅ PHP FUNCIONANDO: Sí<br>";
echo "🌐 DOMINIO: floreria.42web.io<br>";
echo "📅 FECHA: " . date('Y-m-d H:i:s') . "<br>";

// Verificar si index.php existe
if (file_exists('index.php')) {
    echo "🎯 INDEX.PHP: EXISTE<br>";
} else {
    echo "🎯 INDEX.PEX: NO EXISTE<br>";
}
?>