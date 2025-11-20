<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
}

$pedido = null;
$detalle_pedido = [];

if ($logueado) {
    if (!isset($_GET['id'])) {
        header('Location: estado_pedido.php');
        exit();
    }

    $id_pedido = $_GET['id'];
    $id_cliente = $_SESSION['id_cliente'];

    // Obtener detalles del pedido
    try {
        $stmt_pedido = $pdo->prepare("
            SELECT v.*, c.nombre as cliente_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venta = :id_venta AND v.id_cliente = :id_cliente
        ");
        $stmt_pedido->bindParam(':id_venta', $id_pedido);
        $stmt_pedido->bindParam(':id_cliente', $id_cliente);
        $stmt_pedido->execute();
        $pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            $_SESSION['mensaje_error'] = 'Pedido no encontrado o no tienes permiso para verlo';
            header('Location: estado_pedido.php');
            exit();
        }

        // Obtener detalles del pedido
        $stmt_detalle = $pdo->prepare("
            SELECT dv.*, p.nombre as producto_nombre, p.sabor
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = :id_venta
        ");
        $stmt_detalle->bindParam(':id_venta', $id_pedido);
        $stmt_detalle->execute();
        $detalle_pedido = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['mensaje_error'] = 'Error al obtener detalles del pedido';
        header('Location: estado_pedido.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - <?php echo $logueado ? 'Cliente' : 'Invitado'; ?> - Detalle Pedido #<?php echo $pedido ? $pedido['id_venta'] : 'N/A'; ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <header class="cliente-header">
            <div class="header-content-cliente">
                <button class="menu-toggle-cliente" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="cliente-nav">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-cliente">
                    <i class="fas fa-ice-cream"></i>
                    <span>Concelato <?php echo $logueado ? 'Cliente' : 'Invitado'; ?></span>
                </div>
                <nav id="cliente-nav" class="cliente-nav">
                    <ul>
                        <li><a href="index.php">
                            <i class="fas fa-home"></i> <span>Inicio</span>
                        </a></li>
                        <li><a href="pedidos.php">
                            <i class="fas fa-shopping-cart"></i> <span><?php echo $logueado ? 'Mis Pedidos' : 'Realizar Pedidos'; ?></span>
                        </a></li>
                        <li><a href="estado_pedido.php">
                            <i class="fas fa-truck"></i> <span>Estado Pedido</span>
                        </a></li>
                        <li><a href="invitado.php">
                            <i class="fas fa-ice-cream"></i> <span>Nuestros Sabores</span>
                        </a></li>
                    </ul>
                </nav>
                <?php if ($logueado): ?>
                <button class="logout-btn-cliente" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </button>
                <?php else: ?>
                <a href="../publico/login.php" class="btn-cliente btn-primary-cliente">
                    <i class="fas fa-sign-in-alt"></i> <span>Iniciar Sesión</span>
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="cliente-main">
            <?php if ($logueado && $pedido): ?>
            <div class="welcome-section-cliente">
                <h1>Detalle del Pedido #<?php echo $pedido['id_venta']; ?></h1>
                <p>Información detallada del pedido realizado</p>
            </div>

            <div class="card-cliente">
                <h2>Información del Pedido</h2>
                <div class="pedido-info">
                    <div class="info-item">
                        <strong>ID Pedido:</strong>
                        <span>#<?php echo $pedido['id_venta']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Estado:</strong>
                        <span class="status-badge <?php
                            echo $pedido['estado'] === 'Procesada' ? 'active' :
                            ($pedido['estado'] === 'Pendiente' ? 'warning' : 'inactive'); ?>">
                            <?php echo $pedido['estado']; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Total:</strong>
                        <span>S/. <?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Cliente:</strong>
                        <span><?php echo htmlspecialchars($pedido['cliente_nombre'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Observaciones:</strong>
                        <span><?php echo htmlspecialchars($pedido['nota'] ?: 'Ninguna'); ?></span>
                    </div>
                </div>
            </div>

            <div class="card-cliente">
                <h2>Productos del Pedido</h2>
                <div class="table-container-cliente">
                    <table class="cliente-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Sabor</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle_pedido as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['sabor']); ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td>S/. <?php echo number_format($detalle['precio_unit'], 2); ?></td>
                                <td>S/. <?php echo number_format($detalle['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL:</td>
                                <td style="font-weight: bold;">S/. <?php echo number_format($pedido['total'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="welcome-section-cliente">
                <h1>Detalle del Pedido</h1>
                <p>Consulta los detalles de tus pedidos</p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para ver los detalles de tus pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <div class="card-cliente">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Inicia sesión para ver los detalles del pedido</h3>
                    <p>Debes estar registrado para consultar los detalles de tus pedidos</p>
                    <a href="../publico/login.php" class="btn-cliente btn-primary-cliente" style="margin-top: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
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

        // Toggle mobile menu
        document.querySelector('.menu-toggle-cliente').addEventListener('click', function() {
            const nav = document.querySelector('.cliente-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>