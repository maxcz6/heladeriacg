<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
}

$pedidos = [];
if ($logueado) {
    // Obtener pedidos del cliente
    try {
        $stmt = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado, v.nota,
                   GROUP_CONCAT(CONCAT(p.nombre, ' x', dv.cantidad)) as productos,
                   COUNT(dv.id_detalle) as total_items
            FROM ventas v
            LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
            LEFT JOIN productos p ON dv.id_producto = p.id_producto
            WHERE v.id_cliente = :id_cliente
            GROUP BY v.id_venta, v.fecha, v.total, v.estado, v.nota
            ORDER BY v.fecha DESC
        ");
        $stmt->bindParam(':id_cliente', $_SESSION['id_cliente']);
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $pedidos = [];
        error_log("Error al obtener pedidos del cliente: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Estado de Pedidos</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1><?php echo $logueado ? 'Estado de Mis Pedidos' : 'Consulta de Pedidos'; ?></h1>
                <p><?php echo $logueado ? 'Consulta el estado de tus pedidos recientes' : 'Inicia sesión para ver el estado de tus pedidos.'; ?></p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para ver tus pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <?php if ($logueado): ?>
            <div class="card-cliente">
                <div class="table-container-cliente">
                    <table class="cliente-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Productos</th>
                                <th>Total Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Nota</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pedidos)): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id_venta']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['productos']); ?></td>
                                    <td><?php echo $pedido['total_items']; ?></td>
                                    <td>S/. <?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php
                                            echo $pedido['estado'] === 'Procesada' ? 'active' :
                                            ($pedido['estado'] === 'Pendiente' ? 'warning' : 'inactive'); ?>">
                                            <?php echo $pedido['estado']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($pedido['nota'] ?: 'N/A'); ?></td>
                                    <td>
                                        <button class="btn-cliente btn-secondary-cliente" onclick="verDetallePedido(<?php echo $pedido['id_venta']; ?>)">
                                            <i class="fas fa-eye"></i> Detalles
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No tienes pedidos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card-cliente">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Inicia sesión para ver tus pedidos</h3>
                    <p>Debes estar registrado para consultar el estado de tus pedidos</p>
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

        function verDetallePedido(id_pedido) {
            // Mostrar detalles del pedido
            window.location.href = 'detalle_pedido.php?id=' + id_pedido;
        }

        // Toggle mobile menu
        document.querySelector('.menu-toggle-cliente').addEventListener('click', function() {
            const nav = document.querySelector('.cliente-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>