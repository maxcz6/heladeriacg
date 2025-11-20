<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener información para mostrar al empleado
try {
    // Obtener productos
    $stmt_productos = $pdo->prepare("
        SELECT p.*, pr.empresa as proveedor_nombre
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        WHERE p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt_productos->execute();
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener ventas recientes
    $stmt_ventas = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        ORDER BY v.fecha DESC
        LIMIT 5
    ");
    $stmt_ventas->execute();
    $ventas_recientes = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
    $ventas_recientes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="empleado-container">
        <!-- Header con navegación -->
        <header class="empleado-header">
            <div class="header-content-empleado">
                <button class="menu-toggle-empleado" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="empleado-nav">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-empleado">
                    <i class="fas fa-ice-cream"></i>
                    <span>Concelato Empleado</span>
                </div>
                <nav id="empleado-nav" class="empleado-nav">
                    <ul>
                        <li><a href="index.php" class="active">
                            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                        </a></li>
                        <li><a href="ventas.php">
                            <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                        </a></li>
                        <li><a href="inventario.php">
                            <i class="fas fa-boxes"></i> <span>Inventario</span>
                        </a></li>
                        <li><a href="pedidos_recibidos.php">
                            <i class="fas fa-list"></i> <span>Pedidos</span>
                        </a></li>
                        <li><a href="productos.php">
                            <i class="fas fa-box"></i> <span>Productos</span>
                        </a></li>
                        <li><a href="clientes.php">
                            <i class="fas fa-user-friends"></i> <span>Clientes</span>
                        </a></li>
                    </ul>
                </nav>
                <button class="logout-btn-empleado" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </button>
            </div>
        </header>

        <main class="empleado-main">
            <div class="welcome-section-empleado">
                <h1>Panel de Empleado - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Aquí puedes gestionar las operaciones diarias</p>
            </div>

            <div class="card-empleado">
                <h2>Productos Disponibles</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sabor</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Proveedor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id_producto']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?>L</td>
                                    <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-empleado btn-primary-empleado" onclick="actualizarStock(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-boxes"></i> Stock
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No hay productos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-empleado">
                <h2>Ventas Recientes</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas_recientes)): ?>
                                <?php foreach ($ventas_recientes as $venta): ?>
                                <tr>
                                    <td><?php echo $venta['id_venta']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Cliente Anónimo'); ?></td>
                                    <td>S/. <?php echo number_format($venta['total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $venta['estado'] === 'Procesada' ? 'active' : ($venta['estado'] === 'Pendiente' ? 'warning' : 'inactive'); ?>">
                                            <?php echo $venta['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-empleado btn-secondary-empleado" onclick="verDetalleVenta(<?php echo $venta['id_venta']; ?>)">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No hay ventas recientes</td>
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
        
        function actualizarStock(id_producto) {
            // Simular actualización de stock
            alert('Funcionalidad de actualización de stock para producto ID: ' + id_producto);
            // Aquí iría la lógica para actualizar el stock
            // window.location.href = 'actualizar_stock.php?id=' + id_producto;
        }
        
        function verDetalleVenta(id_venta) {
            // Simular ver detalle de venta
            alert('Funcionalidad de ver detalle de venta ID: ' + id_venta);
            // Aquí iría la lógica para ver el detalle de la venta
        }
        
        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.getElementById('empleado-nav');
            nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
        });
    </script>
</body>
</html>