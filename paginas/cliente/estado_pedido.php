<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('cliente');

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Cliente - Estado de Pedidos</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/estilos_cliente.css">
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
                    <span>Concelato Cliente</span>
                </div>
                <nav id="cliente-nav" class="cliente-nav">
                    <ul>
                        <li><a href="index.php">
                            <i class="fas fa-home"></i> <span>Inicio</span>
                        </a></li>
                        <li><a href="pedidos.php">
                            <i class="fas fa-shopping-cart"></i> <span>Mis Pedidos</span>
                        </a></li>
                        <li><a href="estado_pedido.php" class="active">
                            <i class="fas fa-truck"></i> <span>Estado Pedido</span>
                        </a></li>
                        <li><a href="../publico/index.php">
                            <i class="fas fa-ice-cream"></i> <span>Nuestros Sabores</span>
                        </a></li>
                    </ul>
                </nav>
                <button class="logout-btn-cliente" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </button>
            </div>
        </header>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1>Estado de Mis Pedidos</h1>
                <p>Consulta el estado de tus pedidos recientes</p>
            </div>

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