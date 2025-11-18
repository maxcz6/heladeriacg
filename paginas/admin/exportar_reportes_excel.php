<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Establecer encabezados para descarga XLSX
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="reporte_general_' . date('Y-m-d_H-i-s') . '.xls"');

echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
echo "<title>Reporte General - Concelato Gelateria</title>";
echo "</head>";
echo "<body>";
echo "<h1>Reporte General - Concelato Gelateria</h1>";
echo "<h2>Fecha: " . date('d/m/Y H:i:s') . "</h2>";

// Estadísticas generales
$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$stmt_stats->execute();
$productos_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM clientes");
$stmt_stats->execute();
$clientes_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM vendedores");
$stmt_stats->execute();
$empleados_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

// Ventas de hoy
$stmt_stats = $pdo->prepare("SELECT SUM(total) as total_ventas, COUNT(*) as num_ventas FROM ventas WHERE DATE(fecha) = CURDATE()");
$stmt_stats->execute();
$ventas_hoy = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Ventas del mes
$stmt_stats = $pdo->prepare("SELECT SUM(total) as total_ventas, COUNT(*) as num_ventas FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())");
$stmt_stats->execute();
$ventas_mes = $stmt_stats->fetch(PDO::FETCH_ASSOC);

echo "<h3>Estadísticas Generales</h3>";
echo "<table border='1'>";
echo "<tr><th>Métrica</th><th>Valor</th></tr>";
echo "<tr><td>Productos Activos</td><td>{$productos_count}</td></tr>";
echo "<tr><td>Total de Clientes</td><td>{$clientes_count}</td></tr>";
echo "<tr><td>Empleados</td><td>{$empleados_count}</td></tr>";
echo "<tr><td>Ventas del Mes</td><td>S/. " . number_format($ventas_mes['total_ventas'] ?: 0, 2) . "</td></tr>";
echo "</table>";

echo "<h3>Estadísticas de Ventas</h3>";
echo "<table border='1'>";
echo "<tr><th>Métrica</th><th>Valor</th></tr>";
echo "<tr><td>Ventas Hoy</td><td>S/. " . number_format($ventas_hoy['total_ventas'] ?: 0, 2) . "</td></tr>";
echo "<tr><td>Operaciones Hoy</td><td>" . ($ventas_hoy['num_ventas'] ?: 0) . "</td></tr>";
echo "<tr><td>Ventas del Mes</td><td>S/. " . number_format($ventas_mes['total_ventas'] ?: 0, 2) . "</td></tr>";
echo "<tr><td>Operaciones del Mes</td><td>" . ($ventas_mes['num_ventas'] ?: 0) . "</td></tr>";
echo "</table>";

// Productos más vendidos
$stmt_mas_vendidos = $pdo->prepare("
    SELECT p.nombre, SUM(dv.cantidad) as total_vendido
    FROM detalle_ventas dv
    JOIN productos p ON dv.id_producto = p.id_producto
    JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id_producto, p.nombre
    ORDER BY total_vendido DESC
    LIMIT 5
");
$stmt_mas_vendidos->execute();
$productos_mas_vendidos = $stmt_mas_vendidos->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Productos Más Vendidos (Últimos 30 días)</h3>";
echo "<table border='1'>";
echo "<tr><th>Producto</th><th>Unidades Vendidas</th></tr>";
foreach ($productos_mas_vendidos as $producto) {
    echo "<tr><td>" . $producto['nombre'] . "</td><td>" . $producto['total_vendido'] . "</td></tr>";
}
if (empty($productos_mas_vendidos)) {
    echo "<tr><td colspan='2'>No hay datos disponibles</td></tr>";
}
echo "</table>";

// Ventas por día de la semana
$stmt_ventas_semana = $pdo->prepare("
    SELECT DAYNAME(fecha) as dia, SUM(total) as total
    FROM ventas
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(fecha)
    ORDER BY FIELD(dia, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt_ventas_semana->execute();
$ventas_semana = $stmt_ventas_semana->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Ventas por Día (Últimos 7 días)</h3>";
echo "<table border='1'>";
echo "<tr><th>Día</th><th>Total (S/.)</th></tr>";
foreach ($ventas_semana as $venta) {
    echo "<tr><td>" . $venta['dia'] . "</td><td>S/. " . number_format($venta['total'], 2) . "</td></tr>";
}
if (empty($ventas_semana)) {
    echo "<tr><td colspan='2'>No hay datos disponibles</td></tr>";
}
echo "</table>";

echo "</body>";
echo "</html>";
?>