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
    <link rel="stylesheet" href="/heladeriacg/css/empleado/modernos_estilos_empleado.css">
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
                        <li><a href="descuentos.php">
                            <i class="fas fa-tags"></i> <span>Descuentos</span>
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

            <!-- Atajos rápidos -->
            <div class="card-empleado">
                <h2>Acciones Rápidas</h2>
                <div class="quick-actions-grid">
                    <a href="ventas.php" class="quick-action-card">
                        <div class="quick-action-content">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Registrar Venta</h3>
                            <p>Procesa ventas de productos</p>
                        </div>
                    </a>

                    <a href="inventario.php" class="quick-action-card">
                        <div class="quick-action-content">
                            <i class="fas fa-boxes"></i>
                            <h3>Control de Inventario</h3>
                            <p>Gestiona niveles de stock</p>
                        </div>
                    </a>

                    <a href="pedidos_recibidos.php" class="quick-action-card">
                        <div class="quick-action-content">
                            <i class="fas fa-list"></i>
                            <h3>Pedidos Pendientes</h3>
                            <p>Gestiona pedidos recibidos</p>
                        </div>
                    </a>

                    <a href="clientes.php" class="quick-action-card">
                        <div class="quick-action-content">
                            <i class="fas fa-user-friends"></i>
                            <h3>Clientes</h3>
                            <p>Consulta información de clientes</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card-empleado">
                <h2>Estadísticas Rápidas</h2>
                <?php
                // Obtener estadísticas
                $stmt_ventas_hoy = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto FROM ventas WHERE DATE(fecha) = CURDATE()");
                $stmt_ventas_hoy->execute();
                $ventas_hoy = $stmt_ventas_hoy->fetch(PDO::FETCH_ASSOC);

                $stmt_productos_bajo_stock = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock < 10 AND activo = 1");
                $stmt_productos_bajo_stock->execute();
                $productos_bajo_stock = $stmt_productos_bajo_stock->fetch(PDO::FETCH_ASSOC);

                $stmt_pedidos_pendientes = $pdo->prepare("SELECT COUNT(*) as total FROM ventas WHERE estado = 'Pendiente'");
                $stmt_pedidos_pendientes->execute();
                $pedidos_pendientes = $stmt_pedidos_pendientes->fetch(PDO::FETCH_ASSOC);

                // Obtener estadísticas adicionales
                $stmt_ventas_mes = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())");
                $stmt_ventas_mes->execute();
                $ventas_mes = $stmt_ventas_mes->fetch(PDO::FETCH_ASSOC);

                $stmt_clientes_recientes = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE DATE(fecha_registro) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
                $stmt_clientes_recientes->execute();
                $clientes_recientes = $stmt_clientes_recientes->fetch(PDO::FETCH_ASSOC);

                $stmt_productos_totales = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
                $stmt_productos_totales->execute();
                $productos_totales = $stmt_productos_totales->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--empleado-primary), var(--empleado-primary-light));">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $ventas_hoy['total']; ?></h3>
                            <p>Ventas Hoy</p>
                            <span>S/. <?php echo number_format($ventas_hoy['monto'], 2); ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--empleado-warning), #fbbf24);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $productos_bajo_stock['total']; ?></h3>
                            <p>Bajo Stock</p>
                            <span>Productos con menos de 10L</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--empleado-accent), #f59e0b);">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pedidos_pendientes['total']; ?></h3>
                            <p>Pedidos Pendientes</p>
                            <span>Por procesar</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--empleado-success), #34d399);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $clientes_recientes['total']; ?></h3>
                            <p>Nuevos Clientes</p>
                            <span>Últimos 30 días</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos Disponibles -->
            <div class="card-empleado">
                <div class="section-header">
                    <h2>Productos Disponibles</h2>
                    <a href="inventario.php" class="btn-empleado btn-primary-empleado">
                        <i class="fas fa-boxes"></i> Ver Todo el Inventario
                    </a>
                </div>
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

            <!-- Ventas Recientes -->
            <div class="card-empleado">
                <div class="section-header">
                    <h2>Ventas Recientes</h2>
                    <a href="ventas.php" class="btn-empleado btn-primary-empleado">
                        <i class="fas fa-shopping-cart"></i> Registrar Nueva Venta
                    </a>
                </div>
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

    <style>
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .quick-action-card {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-content {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--empleado-primary), var(--empleado-primary-dark));
            border-radius: var(--radius-md);
            color: white;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .quick-action-content i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .quick-action-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }

        .quick-action-content p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .quick-action-card:hover .quick-action-content {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stat-card {
            display: flex;
            align-items: center;
            background: var(--empleado-card-bg);
            border-radius: var(--radius-md);
            padding: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--empleado-border);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            margin: 0 0 0.25rem 0;
            font-size: 1.8rem;
            color: var(--empleado-text);
        }

        .stat-info p {
            margin: 0 0 0.25rem 0;
            font-size: 0.9rem;
            color: var(--empleado-text-light);
            font-weight: 500;
        }

        .stat-info span {
            font-size: 0.8rem;
            color: var(--empleado-text-light);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .section-header a {
                width: 100%;
                text-align: center;
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section-empleado {
                padding: 1.5rem;
            }

            .card-empleado {
                padding: 1.2rem;
            }

            .empleado-table {
                font-size: 0.8rem;
            }

            .empleado-table th,
            .empleado-table td {
                padding: 0.8rem 0.5rem;
            }
        }
    </style>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function actualizarStock(id_producto) {
            // Redirigir a la página de inventario con el producto seleccionado
            window.location.href = 'inventario.php';
        }

        function verDetalleVenta(id_venta) {
            // Aquí iría la lógica para ver el detalle de la venta
            // Por ahora, mostramos una alerta informativa
            alert('Ver detalle de la venta ID: ' + id_venta + '\nEn una implementación completa, se mostraría un modal o se redirigiría a la página de detalles.');
        }

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.getElementById('empleado-nav');
            nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
        });
    </script>
</body>
</html>