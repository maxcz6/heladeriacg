<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Establecer encabezados para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ventas_export_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

// Encabezados del CSV
fputcsv($output, array('ID Venta', 'Fecha', 'Cliente', 'Vendedor', 'Total', 'Estado'));

// Obtener todas las ventas
$stmt = $pdo->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado, v.id_cliente, v.id_vendedor,
           c.nombre as cliente_nombre,
           ve.nombre as vendedor_nombre
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    LEFT JOIN vendedores ve ON v.id_vendedor = ve.id_vendedor
    ORDER BY v.fecha DESC
");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Añadir cada venta al CSV
foreach ($ventas as $venta) {
    fputcsv($output, array(
        $venta['id_venta'],
        $venta['fecha'],
        $venta['cliente_nombre'] ?: 'Desconocido',
        $venta['vendedor_nombre'] ?: 'Desconocido',
        number_format($venta['total'], 2),
        $venta['estado']
    ));
}

fclose($output);
?>