<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Recuperar todas las estadísticas
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

// Ventas por día de la semana
$stmt_ventas_semana = $pdo->prepare("
    SELECT DAYNAME(fecha) as dia, DAYOFWEEK(fecha) as dia_numero, SUM(total) as total
    FROM ventas
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(fecha), DAYOFWEEK(fecha)
    ORDER BY DAYOFWEEK(fecha)
");
$stmt_ventas_semana->execute();
$ventas_semana = $stmt_ventas_semana->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Impresión - Concelato Gelateria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
            color: black;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0891b2;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .header h1 {
            color: #0891b2;
            margin: 0;
        }
        
        .date {
            font-size: 14px;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            color: #0891b2;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #f9f9f9;
        }
        
        .stat-card h3 {
            margin: 5px 0;
            color: #1e293b;
        }
        
        .stat-card p {
            margin: 3px 0;
            color: #475569;
            font-size: 14px;
        }
        
        .products-top-list {
            margin: 10px 0;
        }
        
        .top-product {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .product-info {
            display: flex;
            gap: 15px;
        }
        
        .product-name {
            font-weight: bold;
        }
        
        .product-sold {
            color: #059669;
        }
        
        .sales-chart {
            display: flex;
            gap: 15px;
            align-items: end;
            margin-top: 15px;
        }
        
        .chart-bar {
            text-align: center;
            flex: 1;
        }
        
        .bar-container {
            height: 150px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            position: relative;
            margin-top: 5px;
        }
        
        .bar-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0891b2;
            transition: height 0.3s;
        }
        
        .bar-value {
            font-weight: bold;
            margin-top: 5px;
            color: #1e293b;
        }
        
        @media print {
            body {
                margin: 0;
            }
            
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte General</h1>
        <h3>Concelato Gelateria</h3>
        <p class="date">Fecha de Impresión: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="section">
        <h2>Estadísticas Generales</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $productos_count; ?></h3>
                <p>Productos Activos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $clientes_count; ?></h3>
                <p>Total de Clientes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $empleados_count; ?></h3>
                <p>Empleados</p>
            </div>
            <div class="stat-card">
                <h3>S/. <?php echo number_format(isset($ventas_mes['total_ventas']) ? $ventas_mes['total_ventas'] : 0, 2); ?></h3>
                <p>Ventas del Mes</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Estadísticas de Ventas</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>S/. <?php echo number_format(isset($ventas_hoy['total_ventas']) ? $ventas_hoy['total_ventas'] : 0, 2); ?></h3>
                <p>Ventas Hoy</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($ventas_hoy['num_ventas']) ? $ventas_hoy['num_ventas'] : 0; ?></h3>
                <p>Operaciones Hoy</p>
            </div>
            <div class="stat-card">
                <h3>S/. <?php echo number_format(isset($ventas_mes['total_ventas']) ? $ventas_mes['total_ventas'] : 0, 2); ?></h3>
                <p>Ventas del Mes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($ventas_mes['num_ventas']) ? $ventas_mes['num_ventas'] : 0; ?></h3>
                <p>Operaciones del Mes</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Productos Más Vendidos (Últimos 30 días)</h2>
        <div class="products-top-list">
            <?php if (count($productos_mas_vendidos) > 0): ?>
                <?php foreach ($productos_mas_vendidos as $producto): ?>
                    <div class="top-product">
                        <div class="product-info">
                            <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                            <span class="product-sold">Unidades: <?php echo $producto['total_vendido']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay datos disponibles</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Ventas por Día (Últimos 7 días)</h2>
        <div class="sales-chart">
            <?php foreach ($ventas_semana as $venta): ?>
                <div class="chart-bar">
                    <div class="bar-label"><?php echo substr($venta['dia'], 0, 3); ?></div>
                    <div class="bar-container">
                        <div class="bar-fill" style="height: <?php echo isset($venta['total']) && is_numeric($venta['total']) && count($ventas_semana) > 0 ? min(100, ($venta['total'] / max(array_column($ventas_semana, 'total')) * 100)) : 10; ?>%; background: #0891b2;"></div>
                    </div>
                    <div class="bar-value">S/. <?php echo number_format($venta['total'], 2); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button onclick="window.print()" style="padding: 10px 20px; background: #0891b2; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Imprimir</button>
</body>
</html>