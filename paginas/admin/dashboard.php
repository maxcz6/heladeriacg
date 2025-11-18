<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener métricas del sistema
$metricas = obtenerMetricasSistema();
$ventas_recientes = obtenerVentasRecientes(5);
$productos_bajos = obtenerProductosBajos(5);
$ventas_diarias = obtenerReporteVentasDiarias();
$productos_top = obtenerProductosMasVendidos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-ice-cream"></i>
                Concelato Gelateria
            </div>
            <nav class="main-nav">
                <ul>
                    <li class="active"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                    <li><a href="ventas.php"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
                    <li><a href="empleados.php"><i class="fas fa-users"></i> Empleados</a></li>
                    <li><a href="clientes.php"><i class="fas fa-user-friends"></i> Clientes</a></li>
                    <li><a href="proveedores.php"><i class="fas fa-truck"></i> Proveedores</a></li>
                    <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                    <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Panel de Administración</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <button class="logout-btn" onclick="cerrarSesion()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </header>

            <div class="dashboard-grid">
                <!-- Estadísticas principales -->
                <div class="metric-cards">
                    <div class="metric-card total-sales">
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-info">
                            <h3>S/. <?php echo number_format($metricas['ventas_totales'], 2); ?></h3>
                            <p>Ventas Totales</p>
                        </div>
                    </div>
                    <div class="metric-card total-products">
                        <div class="card-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="card-info">
                            <h3><?php echo $metricas['productos_activos']; ?></h3>
                            <p>Productos</p>
                        </div>
                    </div>
                    <div class="metric-card total-customers">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3><?php echo $metricas['clientes_totales']; ?></h3>
                            <p>Clientes</p>
                        </div>
                    </div>
                    <div class="metric-card total-employees">
                        <div class="card-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-info">
                            <h3><?php echo $metricas['empleados_totales']; ?></h3>
                            <p>Empleados</p>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de ventas y productos más vendidos -->
                <div class="chart-section">
                    <div class="sales-chart">
                        <h3>Ventas por Día (Últimos 7 días)</h3>
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="top-products">
                        <h3>Productos Más Vendidos</h3>
                        <div class="top-products-list">
                            <?php foreach ($productos_top as $producto): ?>
                                <div class="top-product">
                                    <div class="product-info">
                                        <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                        <span class="product-sold"><?php echo $producto['total_vendido']; ?> vendidos</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="recent-activity">
                    <div class="recent-sales">
                        <h3>Ventas Recientes</h3>
                        <div class="recent-sales-list">
                            <?php foreach ($ventas_recientes as $venta): ?>
                                <div class="recent-sale">
                                    <div class="sale-info">
                                        <span class="sale-id">#<?php echo $venta['id_venta']; ?></span>
                                        <span class="sale-amount">S/. <?php echo number_format($venta['total'], 2); ?></span>
                                    </div>
                                    <div class="sale-date"><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="low-stock">
                        <h3>Productos con Bajo Stock</h3>
                        <div class="low-stock-list">
                            <?php if (count($productos_bajos) > 0): ?>
                                <?php foreach ($productos_bajos as $producto): ?>
                                    <div class="low-stock-item">
                                        <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                        <span class="stock-quantity"><?php echo $producto['stock']; ?>L</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-low-stock">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No hay productos con bajo stock</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configurar el gráfico de ventas
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    foreach ($ventas_diarias as $venta) {
                        echo '"' . date('d/m', strtotime($venta['dia'])) . '",';
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Ventas Diarias',
                    data: [
                        <?php 
                        foreach ($ventas_diarias as $venta) {
                            echo $venta['total'] . ',';
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(8, 145, 178, 0.6)',
                    borderColor: 'rgba(8, 145, 178, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>