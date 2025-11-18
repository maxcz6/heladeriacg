<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener estadísticas para el dashboard
$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$stmt_stats->execute();
$productos_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM vendedores");
$stmt_stats->execute();
$empleados_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM clientes");
$stmt_stats->execute();
$clientes_count = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT SUM(total) as total_ventas FROM ventas WHERE DATE(fecha) = CURDATE()");
$stmt_stats->execute();
$ventas_hoy = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total_ventas'] ?: 0;

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM ventas WHERE estado = 'Pendiente'");
$stmt_stats->execute();
$pedidos_pendientes = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock < 10");
$stmt_stats->execute();
$productos_bajos = $stmt_stats->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Admin
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
                        <li><a href="empleados.php"><i class="fas fa-user-tie"></i> Empleados</a></li>
                        <li><a href="reportes.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                        <li><a href="promociones.php"><i class="fas fa-percentage"></i> Promociones</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Panel de Administración</h1>
                <p>Bienvenido de vuelta, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>

            <!-- Estadísticas generales -->
            <div class="stats-section">
                <h2>Estadísticas Generales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $productos_count; ?></h3>
                            <p>Productos Activos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $clientes_count; ?></h3>
                            <p>Clientes</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon employees">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $empleados_count; ?></h3>
                            <p>Empleados</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3>S/. <?php echo number_format($ventas_hoy, 2); ?></h3>
                            <p>Ventas Hoy</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="dashboard-grid">
                    <!-- Productos más vendidos -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-fire"></i> Productos Más Vendidos (Hoy)</h3>
                        <div class="products-list">
                            <?php
                            $stmt_mas_vendidos = $pdo->prepare("
                                SELECT p.nombre, SUM(dv.cantidad) as total_vendido
                                FROM detalle_ventas dv
                                JOIN productos p ON dv.id_producto = p.id_producto
                                JOIN ventas v ON dv.id_venta = v.id_venta
                                WHERE DATE(v.fecha) = CURDATE()
                                GROUP BY p.id_producto, p.nombre
                                ORDER BY total_vendido DESC
                                LIMIT 5
                            ");
                            $stmt_mas_vendidos->execute();
                            $mas_vendidos = $stmt_mas_vendidos->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($mas_vendidos) > 0):
                                foreach ($mas_vendidos as $producto):
                            ?>
                                <div class="product-item">
                                    <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                    <span class="product-qty"><?php echo $producto['total_vendido']; ?> vendidos</span>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <p class="no-data">No hay ventas hoy</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Últimas ventas -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-shopping-bag"></i> Últimas Ventas</h3>
                        <div class="sales-list">
                            <?php
                            $stmt_ventas = $pdo->prepare("
                                SELECT v.id_venta, v.fecha, v.total, c.nombre as cliente_nombre
                                FROM ventas v
                                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                                ORDER BY v.fecha DESC
                                LIMIT 5
                            ");
                            $stmt_ventas->execute();
                            $ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($ventas) > 0):
                                foreach ($ventas as $venta):
                            ?>
                                <div class="sale-item">
                                    <div class="sale-info">
                                        <span class="sale-id">#<?php echo $venta['id_venta']; ?></span>
                                        <span class="sale-client"><?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Desconocido'); ?></span>
                                    </div>
                                    <div class="sale-details">
                                        <span class="sale-date"><?php echo date('d/m H:i', strtotime($venta['fecha'])); ?></span>
                                        <span class="sale-amount">S/. <?php echo number_format($venta['total'], 2); ?></span>
                                    </div>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <p class="no-data">No hay ventas recientes</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Productos con bajo stock -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-exclamation-triangle"></i> Productos con Bajo Stock</h3>
                        <div class="low-stock-list">
                            <?php
                            $stmt_bajos = $pdo->prepare("
                                SELECT nombre, stock
                                FROM productos
                                WHERE stock < 10 AND activo = 1
                                ORDER BY stock ASC
                                LIMIT 5
                            ");
                            $stmt_bajos->execute();
                            $bajos = $stmt_bajos->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($bajos) > 0):
                                foreach ($bajos as $producto):
                            ?>
                                <div class="stock-item">
                                    <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                    <span class="stock-qty"><?php echo $producto['stock']; ?>L</span>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <p class="no-data">No hay productos con bajo stock</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actividad reciente -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-bolt"></i> Actividad Reciente</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <i class="fas fa-plus-circle activity-add"></i>
                                <div class="activity-info">
                                    <p>Nuevo producto agregado</p>
                                    <span>Hace 15 minutos</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-shopping-cart activity-sale"></i>
                                <div class="activity-info">
                                    <p>Venta realizada #<?php echo rand(100, 999); ?></p>
                                    <span>Hace 30 minutos</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-user-plus activity-customer"></i>
                                <div class="activity-info">
                                    <p>Nuevo cliente registrado</p>
                                    <span>Hace 1 hora</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-chart-line activity-report"></i>
                                <div class="activity-info">
                                    <p>Reporte diario generado</p>
                                    <span>Hace 2 horas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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