<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Obtener el ID del empleado basado en el usuario
$stmt_empleado = $pdo->prepare("SELECT id_vendedor, id_sucursal FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado) {
    // Si no hay empleado asociado, redireccionar
    header('Location: ../publico/login.php');
    exit();
} else {
    $id_vendedor = $usuario_empleado['id_vendedor'];
    $id_sucursal = $usuario_empleado['id_sucursal'];
    
    // Si no tiene sucursal asignada, se la asignamos a la principal
    if (!$id_sucursal) {
        $stmt_sucursal = $pdo->prepare("SELECT id_sucursal FROM sucursales WHERE activa = 1 LIMIT 1");
        $stmt_sucursal->execute();
        $sucursal = $stmt_sucursal->fetch(PDO::FETCH_ASSOC);
        if ($sucursal) {
            $id_sucursal = $sucursal['id_sucursal'];
            $stmt_update = $pdo->prepare("UPDATE usuarios SET id_sucursal = :id_sucursal WHERE id_usuario = :id_usuario");
            $stmt_update->bindParam(':id_sucursal', $id_sucursal);
            $stmt_update->bindParam(':id_usuario', $_SESSION['id_usuario']);
            $stmt_update->execute();
        }
    }
}

// Obtener información de la sucursal actual
$sucursal_info = obtenerSucursalPorId($id_sucursal);
$empleados_sucursal = obtenerEmpleadosPorSucursal($id_sucursal);
$inventario_sucursal = obtenerInventarioPorSucursal($id_sucursal);

// Obtener productos activos disponibles en la sucursal
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, i.stock_sucursal as stock
    FROM productos p
    JOIN inventario_sucursal i ON p.id_producto = i.id_producto
    WHERE p.activo = 1 AND i.id_sucursal = :id_sucursal AND i.stock_sucursal > 0
    ORDER BY p.nombre
");
$stmt_productos->bindParam(':id_sucursal', $id_sucursal);
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas del día
$stmt_stats = $pdo->prepare("
    SELECT COUNT(*) as total_ventas, SUM(total) as total_ingresos 
    FROM ventas 
    WHERE DATE(fecha) = CURDATE() AND id_sucursal = :id_sucursal
");
$stmt_stats->bindParam(':id_sucursal', $id_sucursal);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$stmt_pedidos = $pdo->prepare("
    SELECT COUNT(*) as total_pedidos 
    FROM ventas 
    WHERE DATE(fecha) = CURDATE() AND id_sucursal = :id_sucursal AND estado = 'Pendiente'
");
$stmt_pedidos->bindParam(':id_sucursal', $id_sucursal);
$stmt_pedidos->execute();
$pedidos_count = $stmt_pedidos->fetch(PDO::FETCH_ASSOC)['total_pedidos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empleado - <?php echo htmlspecialchars($sucursal_info['nombre'] ?? 'Sucursal Principal'); ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    <?php echo htmlspecialchars($sucursal_info['nombre'] ?? 'Heladería'); ?> - Empleado
                </div>
                <nav>
                    <ul>
                        <li><a href="ventas.php"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
                        <li><a href="inventario.php"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li><a href="pedidos_recibidos.php"><i class="fas fa-list"></i> Pedidos</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="sucursal-name"><?php echo htmlspecialchars($sucursal_info['nombre'] ?? 'Sin sucursal'); ?></span>
                </div>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="employee-main">
            <div class="welcome-section">
                <h1>Panel de Empleado</h1>
                <p>Bienvenido de vuelta, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <p>Trabajando en: <strong><?php echo htmlspecialchars($sucursal_info['nombre'] ?? 'Sin sucursal'); ?></strong></p>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_ventas']; ?></h3>
                        <p>Ventas Hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>S/. <?php echo number_format($stats['total_ingresos'] ?: 0, 2); ?></h3>
                        <p>Total Hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($inventario_sucursal, function($producto) { return $producto['stock'] < 10; })); ?></h3>
                        <p>Productos Bajos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pedidos_count; ?></h3>
                        <p>Pedidos Pendientes</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3>Registrar Venta</h3>
                    <p>Procesar una nueva venta</p>
                    <a href="ventas.php" class="action-btn">Iniciar Venta</a>
                </div>
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3>Control de Inventario</h3>
                    <p>Ver y actualizar stock</p>
                    <a href="inventario.php" class="action-btn">Ver Inventario</a>
                </div>
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Gestionar Pedidos</h3>
                    <p>Ver y actualizar estado de pedidos</p>
                    <a href="pedidos_recibidos.php" class="action-btn">Ver Pedidos</a>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Actividad Reciente en <?php echo htmlspecialchars($sucursal_info['nombre'] ?? 'Sucursal'); ?></h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <i class="fas fa-shopping-cart activity-sale"></i>
                        <div class="activity-info">
                            <p><?php echo $stats['total_ventas']; ?> ventas procesadas hoy</p>
                            <span>Hoy</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <i class="fas fa-box activity-stock"></i>
                        <div class="activity-info">
                            <p><?php echo count($productos); ?> productos disponibles</p>
                            <span>En stock</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <i class="fas fa-user activity-employee"></i>
                        <div class="activity-info">
                            <p><?php echo count($empleados_sucursal); ?> empleados en sucursal</p>
                            <span>Trabajando</span>
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