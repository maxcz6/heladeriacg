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
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación mejorada y responsiva -->
    <!-- Header con navegación mejorada y responsiva -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main content -->
    <main class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Panel de Administración</h1>
                <p class="subtitle">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
        </div>

        <!-- Stats Grid -->
        <section class="stats-grid" aria-label="Estadísticas generales">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $productos_count; ?></h3>
                    <p>Productos Activos</p>
                    <a href="productos.php" class="stat-link">Ver detalles →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $clientes_count; ?></h3>
                    <p>Clientes Registrados</p>
                    <a href="clientes.php" class="stat-link">Ver detalles →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $empleados_count; ?></h3>
                    <p>Empleados Activos</p>
                    <a href="empleados.php" class="stat-link">Ver detalles →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3>S/ <?php echo number_format($ventas_hoy, 2); ?></h3>
                    <p>Ventas de Hoy</p>
                    <a href="ventas.php" class="stat-link">Ver detalles →</a>
                </div>
            </div>
        </section>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Top Products Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-fire"></i> Productos Más Vendidos (Hoy)
                    </h2>
                </div>
                <div class="card-body">
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
                    ?>
                    <div class="product-list">
                        <?php foreach ($mas_vendidos as $producto): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <span class="item-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                <span class="item-badge"><?php echo $producto['total_vendido']; ?> vendidos</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No hay ventas registradas hoy</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Sales Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-shopping-bag"></i> Últimas Ventas
                    </h2>
                </div>
                <div class="card-body">
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
                    ?>
                    <div class="sales-list">
                        <?php foreach ($ventas as $venta): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <span class="item-name">
                                    Venta #<?php echo $venta['id_venta']; ?>
                                </span>
                                <span class="item-secondary">
                                    <?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Desconocido'); ?>
                                </span>
                            </div>
                            <div class="item-meta">
                                <span class="item-date">
                                    <?php echo date('d/m H:i', strtotime($venta['fecha'])); ?>
                                </span>
                                <span class="item-amount">
                                    S/ <?php echo number_format($venta['total'], 2); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No hay ventas recientes</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-exclamation-triangle"></i> Bajo Stock
                    </h2>
                </div>
                <div class="card-body">
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
                    ?>
                    <div class="stock-list">
                        <?php foreach ($bajos as $producto): ?>
                        <div class="list-item alert-item">
                            <div class="item-main">
                                <span class="item-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                            </div>
                            <span class="stock-badge warning">
                                <?php echo $producto['stock']; ?> unidades
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">Todos los productos tienen stock adecuado</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h2>
                </div>
                <div class="card-body">
                    <div class="actions-grid">
                        <a href="productos.php" class="action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Producto</span>
                        </a>
                        <a href="empleados.php" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Nuevo Empleado</span>
                        </a>
                        <a href="clientes.php" class="action-btn">
                            <i class="fas fa-address-card"></i>
                            <span>Nuevo Cliente</span>
                        </a>
                        <a href="ventas.php" class="action-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Nueva Venta</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="/heladeriacg/js/admin/dashboard-utils.js"></script>
    <script src="/heladeriacg/js/admin/script.js"></script>

    <script>
        // ============================================
        // DASHBOARD INTERACTIVITY
        // ============================================

        document.addEventListener('DOMContentLoaded', () => {
            initializeDashboard();
            setupKeyboardShortcuts();
        });

        function initializeDashboard() {
            // Animar tarjetas de estadísticas
            animateElements('.stats-grid .stat-card', 100, 'slideUp');
            
            // Animar tarjetas de contenido
            animateElements('.dashboard-grid .card', 100, 'slideUp');
            
            // Animar items de listas
            animateListCascade('.list-item', 50);

            // Agregar efectos hover
            addHoverEffect('.stat-card', 'lift');
            addHoverEffect('.card', 'lift');
            addHoverEffect('.action-btn', 'grow');
            addHoverEffect('.list-item', 'lift');

            // Hacer clickeable las tarjetas de estadísticas
            makeStatCardsClickable();

            // Mostrar notificación de bienvenida (opcional)
            // showToast('Bienvenido al Dashboard', 'success', 2000);
        }

        function makeStatCardsClickable() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('click', () => {
                    const link = card.querySelector('.stat-link');
                    if (link) {
                        window.location.href = link.getAttribute('href');
                    }
                });
            });
        }

        function setupKeyboardShortcuts() {
            addKeyboardShortcuts({
                'alt+d': () => window.location.href = 'index.php',
                'alt+p': () => window.location.href = 'productos.php',
                'alt+v': () => window.location.href = 'ventas.php',
                'alt+e': () => window.location.href = 'empleados.php',
                'alt+c': () => window.location.href = 'clientes.php'
            });
        }

        // ============================================
        // FORMATEO DE DATOS
        // ============================================

        // Ejemplo de uso en el código (comentado)
        // Descomentar si se necesita formatear valores dinámicamente
        /*
        document.querySelectorAll('[data-format="currency"]').forEach(el => {
            const value = parseFloat(el.textContent);
            el.textContent = formatCurrency(value, 'PEN');
        });

        document.querySelectorAll('[data-format="date"]').forEach(el => {
            const date = el.textContent;
            el.textContent = formatDate(date, 'short');
        });
        */
    </script>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>