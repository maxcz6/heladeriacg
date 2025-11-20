<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/admin_functions.php');

// Variables para filtros
$filtros = [];
$ventas_reporte = [];
$productos_reporte = [];
$clientes_reporte = [];
$inventario_reporte = [];

// Verificar si se está generando un reporte específico
$generar_reporte = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_reporte'])) {
    $generar_reporte = true;

    if ($_POST['tipo_reporte'] === 'ventas_detalles') {
        $filtros = [
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null,
            'tipo_fecha' => $_POST['tipo_fecha'] ?? 'diario'
        ];
        $ventas_reporte = generarReporteVentas($filtros['fecha_inicio'], $filtros['fecha_fin'], $filtros['tipo_fecha']);
    } elseif ($_POST['tipo_reporte'] === 'productos_vendidos') {
        $filtros = [
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null
        ];
        $productos_reporte = generarReporteProductosVendidos($filtros['fecha_inicio'], $filtros['fecha_fin']);
    } elseif ($_POST['tipo_reporte'] === 'ventas_cliente') {
        $filtros = [
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null
        ];
        $clientes_reporte = generarReporteVentasPorCliente($filtros['fecha_inicio'], $filtros['fecha_fin']);
    } elseif ($_POST['tipo_reporte'] === 'inventario') {
        $inventario_reporte = generarReporteInventario();
    }
}

// Si no se está generando un reporte, mostrar estadísticas generales
if (!$generar_reporte) {
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="admin-container">

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Reportes y Estadísticas</h1>
                <p>Aquí puedes ver los reportes y estadísticas de tu negocio</p>
            </div>

            <?php if (!$generar_reporte): ?>
            <!-- Formulario de reportes -->
            <div class="reports-form">
                <h2>Generar Reportes Personalizados</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Selecciona el Tipo de Reporte:</label>
                        <input type="hidden" id="tipo_reporte" name="tipo_reporte" value="">
                        
                        <div class="report-types-grid">
                            <div class="report-type-card" onclick="selectReportType('ventas_detalles')">
                                <div class="report-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                <h3>Ventas Detalladas</h3>
                                <p>Reporte completo de ingresos y transacciones por periodo.</p>
                            </div>
                            
                            <div class="report-type-card" onclick="selectReportType('productos_vendidos')">
                                <div class="report-icon"><i class="fas fa-ice-cream"></i></div>
                                <h3>Productos Top</h3>
                                <p>Ranking de los productos más vendidos y sus ingresos.</p>
                            </div>
                            
                            <div class="report-type-card" onclick="selectReportType('ventas_cliente')">
                                <div class="report-icon"><i class="fas fa-users"></i></div>
                                <h3>Por Cliente</h3>
                                <p>Análisis de compras y comportamiento de clientes.</p>
                            </div>
                            
                            <div class="report-type-card" onclick="selectReportType('inventario')">
                                <div class="report-icon"><i class="fas fa-boxes"></i></div>
                                <h3>Inventario</h3>
                                <p>Estado actual del stock y valoración de productos.</p>
                            </div>
                        </div>
                    </div>

                    <div id="fecha_grupo" style="display:none; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio">
                        </div>

                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin">
                        </div>

                        <div class="form-group" id="tipo_fecha_grupo" style="display:none; flex: 1; min-width: 200px;">
                            <label for="tipo_fecha">Agrupar por:</label>
                            <select id="tipo_fecha" name="tipo_fecha">
                                <option value="diario">Diario</option>
                                <option value="semanal">Semanal</option>
                                <option value="mensual">Mensual</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="submit" class="action-btn primary">
                            <i class="fas fa-chart-bar"></i> Generar Reporte
                        </button>
                    </div>
                </form>
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
                            <h3>S/. <?php echo number_format(isset($ventas_mes['total_ventas']) ? $ventas_mes['total_ventas'] : 0, 2); ?></h3>
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
                            <h3>S/. <?php echo number_format(isset($ventas_hoy['total_ventas']) ? $ventas_hoy['total_ventas'] : 0, 2); ?></h3>
                            <p>Ventas Hoy</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo isset($ventas_hoy['num_ventas']) ? $ventas_hoy['num_ventas'] : 0; ?></h3>
                            <p>Operaciones Hoy</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>S/. <?php echo number_format(isset($ventas_mes['total_ventas']) ? $ventas_mes['total_ventas'] : 0, 2); ?></h3>
                            <p>Ventas del Mes</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo isset($ventas_mes['num_ventas']) ? $ventas_mes['num_ventas'] : 0; ?></h3>
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
                <button class="action-btn print" onclick="window.open('funcionalidades/imprimir_reporte.php', '_blank')">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
                <button class="action-btn excel" onclick="window.location.href='funcionalidades/exportar_reportes_excel.php'">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
            </div>
            <?php endif; ?>

            <?php if ($generar_reporte && !empty($ventas_reporte)): ?>
            <div class="reports-section">
                <h2>Reporte de Ventas Detallado</h2>

                <div class="reports-actions">
                    <a href="funcionalidades/exportar_reportes_excel.php?tipo=ventas&fecha_inicio=<?php echo $filtros['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $filtros['fecha_fin'] ?? ''; ?>" class="action-btn excel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="funcionalidades/exportar_ventas_csv.php?fecha_inicio=<?php echo $filtros['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $filtros['fecha_fin'] ?? ''; ?>" class="action-btn secondary">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                </div>

                <div class="table-container">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Cantidad de Ventas</th>
                                <th>Ingresos Totales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas_reporte as $venta): ?>
                            <tr>
                                <td><?php echo $venta['periodo']; ?></td>
                                <td><?php echo $venta['cantidad_ventas']; ?></td>
                                <td>S/. <?php echo number_format($venta['total_ingresos'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($generar_reporte && !empty($productos_reporte)): ?>
            <div class="reports-section">
                <h2>Productos Más Vendidos</h2>

                <div class="reports-actions">
                    <a href="funcionalidades/exportar_reportes_excel.php?tipo=productos&fecha_inicio=<?php echo $filtros['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $filtros['fecha_fin'] ?? ''; ?>" class="action-btn excel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="productos_reporte.php?fecha_inicio=<?php echo $filtros['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $filtros['fecha_fin'] ?? ''; ?>" class="action-btn secondary">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                </div>

                <div class="table-container">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>ID Producto</th>
                                <th>Producto</th>
                                <th>Sabor</th>
                                <th>Total Vendido</th>
                                <th>Ingresos Totales</th>
                                <th>Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_reporte as $producto): ?>
                            <tr>
                                <td><?php echo $producto['id_producto']; ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                <td><?php echo $producto['total_vendido']; ?></td>
                                <td>S/. <?php echo number_format($producto['ingresos_totales'], 2); ?></td>
                                <td><?php echo $producto['veces_vendido']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($generar_reporte && !empty($clientes_reporte)): ?>
            <div class="reports-section">
                <h2>Ventas por Cliente</h2>

                <div class="reports-actions">
                    <a href="funcionalidades/exportar_reportes_excel.php?tipo=clientes&fecha_inicio=<?php echo $filtros['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $filtros['fecha_fin'] ?? ''; ?>" class="action-btn excel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                </div>

                <div class="table-container">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>ID Cliente</th>
                                <th>Nombre</th>
                                <th>Total Compras</th>
                                <th>Total Gastado</th>
                                <th>Última Compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes_reporte as $cliente): ?>
                            <tr>
                                <td><?php echo $cliente['id_cliente']; ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_nombre']); ?></td>
                                <td><?php echo $cliente['total_compras']; ?></td>
                                <td>S/. <?php echo number_format($cliente['total_gastado'], 2); ?></td>
                                <td><?php echo $cliente['ultima_compra'] ? date('d/m/Y', strtotime($cliente['ultima_compra'])) : 'Nunca'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($generar_reporte && !empty($inventario_reporte)): ?>
            <div class="reports-section">
                <h2>Reporte de Inventario</h2>

                <div class="reports-actions">
                    <a href="funcionalidades/exportar_reportes_excel.php?tipo=inventario" class="action-btn excel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="productos_reporte.php" class="action-btn secondary">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                </div>

                <div class="table-container">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>ID Producto</th>
                                <th>Producto</th>
                                <th>Sabor</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Proveedor</th>
                                <th>Estado Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventario_reporte as $producto): ?>
                            <tr>
                                <td><?php echo $producto['id_producto']; ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                <td><?php echo $producto['stock']; ?></td>
                                <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo htmlspecialchars(isset($producto['proveedor']) && $producto['proveedor'] ? $producto['proveedor'] : 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($producto['estado_stock']); ?>">
                                        <?php echo $producto['estado_stock']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function selectReportType(tipo) {
            // Actualizar input oculto
            document.getElementById('tipo_reporte').value = tipo;
            
            // Actualizar visualmente las tarjetas
            const cards = document.querySelectorAll('.report-type-card');
            cards.forEach(card => card.classList.remove('selected'));
            
            // Encontrar la tarjeta clickeada y seleccionarla (basado en el onclick)
            const selectedCard = document.querySelector(`.report-type-card[onclick*="${tipo}"]`);
            if (selectedCard) selectedCard.classList.add('selected');

            // Mostrar/Ocultar filtros según selección
            const fecha_grupo = document.getElementById('fecha_grupo');
            const tipo_fecha_grupo = document.getElementById('tipo_fecha_grupo');

            if (tipo === 'ventas_detalles' || tipo === 'productos_vendidos' || tipo === 'ventas_cliente') {
                fecha_grupo.style.display = 'flex';

                if (tipo === 'ventas_detalles') {
                    tipo_fecha_grupo.style.display = 'block';
                } else {
                    tipo_fecha_grupo.style.display = 'none';
                }
            } else {
                fecha_grupo.style.display = 'none';
                tipo_fecha_grupo.style.display = 'none';
            }
        }
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>