<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Establecer encabezados para descarga XLSX
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="ventas_export_' . date('Y-m-d_H-i-s') . '.xls"');

echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
echo "</head>";
echo "<body>";
echo "<table border='1'>";
echo "<tr><th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Estado</th></tr>";

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

foreach ($ventas as $venta) {
    echo "<tr>";
    echo "<td>" . $venta['id_venta'] . "</td>";
    echo "<td>" . $venta['fecha'] . "</td>";
    echo "<td>" . (isset($venta['cliente_nombre']) && $venta['cliente_nombre'] ? $venta['cliente_nombre'] : 'Desconocido') . "</td>";
    echo "<td>" . (isset($venta['vendedor_nombre']) && $venta['vendedor_nombre'] ? $venta['vendedor_nombre'] : 'Desconocido') . "</td>";
    echo "<td>" . number_format($venta['total'], 2) . "</td>";
    echo "<td>" . $venta['estado'] . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";
?>