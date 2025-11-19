<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// En una instalación real, necesitarías instalar PhpSpreadsheet
// composer require phpoffice/phpspreadsheet

// Por ahora, simularemos la exportación generando un archivo XLSX básico
// Este archivo simulará la funcionalidad real

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ventas_export_' . date('Y-m-d_H-i-s') . '.xlsx"');

// Para simular un archivo XLSX básico, generamos contenido simulado
$xlsx_content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/archivos_plantillas/plantilla_ventas.xlsx');

if ($xlsx_content === false) {
    // Si no existe la plantilla, generamos un archivo simulado
    $xlsx_content = "Exportación de Ventas - Fecha: " . date('Y-m-d H:i:s');
}

echo $xlsx_content;
?>