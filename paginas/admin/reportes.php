<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

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
    SELECT DAYNAME(fecha) as dia, SUM(total) as total
    FROM ventas
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(fecha)
    ORDER BY FIELD(dia, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt_ventas_semana->execute();
$ventas_semana = $stmt_ventas_semana->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Reportes
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
                        <li><a href="empleados.php"><i class="fas fa-user-tie"></i> Empleados</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Reportes y Estadísticas</h1>
                <p>Aquí puedes ver los reportes y estadísticas de tu negocio</p>
            </div>

            <!-- Estadísticas generales -->
            <div class="reports-section">
                <h2>Estadísticas Generales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $productos_count; ?></h3>
                            <p>Productos Activos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $clientes_count; ?></h3>
                            <p>Total de Clientes</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $empleados_count; ?></h3>
                            <p>Empleados</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>S/. <?php echo number_format($ventas_mes['total_ventas'] ?: 0, 2); ?></h3>
                            <p>Ventas del Mes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de ventas -->
            <div class="reports-section">
                <h2>Estadísticas de Ventas</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>S/. <?php echo number_format($ventas_hoy['total_ventas'] ?: 0, 2); ?></h3>
                            <p>Ventas Hoy</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo $ventas_hoy['num_ventas'] ?: 0; ?></h3>
                            <p>Operaciones Hoy</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>S/. <?php echo number_format($ventas_mes['total_ventas'] ?: 0, 2); ?></h3>
                            <p>Ventas del Mes</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo $ventas_mes['num_ventas'] ?: 0; ?></h3>
                            <p>Operaciones del Mes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos más vendidos -->
            <div class="reports-section">
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

            <!-- Ventas por día -->
            <div class="reports-section">
                <h2>Ventas por Día (Últimos 7 días)</h2>
                <div class="sales-chart">
                    <?php
                    // Convertir el array de objetos a array de valores para calcular el máximo
                    $max_valor = !empty($ventas_semana) ? max(array_map(function($v) { return $v['total']; }, $ventas_semana)) : 0;
                    ?>
                    <?php foreach ($ventas_semana as $venta): ?>
                        <div class="chart-bar">
                            <div class="bar-label"><?php echo substr($venta['dia'], 0, 3); ?></div>
                            <div class="bar-container">
                                <div class="bar-fill" style="height: <?php echo $max_valor > 0 ? min(100, ($venta['total'] / $max_valor * 100)) : 0; ?>%"></div>
                            </div>
                            <div class="bar-value">S/. <?php echo number_format($venta['total'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="reports-actions">
                <button class="action-btn print" onclick="window.open('imprimir_reporte.php', '_blank')">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
                <button class="action-btn excel" onclick="window.location.href='exportar_reportes_excel.php'">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
            </div>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>