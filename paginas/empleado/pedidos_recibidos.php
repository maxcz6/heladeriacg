<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('empleado');

// Obtener pedidos pendientes
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, 
               GROUP_CONCAT(CONCAT(p.nombre, ' x', dv.cantidad)) as productos,
               COUNT(dv.id_detalle) as total_items
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
        LEFT JOIN productos p ON dv.id_producto = p.id_producto
        WHERE v.estado IN ('Pendiente', 'Procesando')
        GROUP BY v.id_venta, v.fecha, v.total, v.estado, v.nota, c.nombre
        ORDER BY v.fecha DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $pedidos = [];
    error_log("Error al obtener pedidos pendientes: " . $e->getMessage());
}

// Procesar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado') {
    $id_venta = $_POST['id_venta'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        $stmt_update = $pdo->prepare("UPDATE ventas SET estado = :estado WHERE id_venta = :id_venta");
        $stmt_update->bindParam(':estado', $nuevo_estado);
        $stmt_update->bindParam(':id_venta', $id_venta);
        
        if ($stmt_update->execute()) {
            $_SESSION['mensaje_exito'] = 'Estado del pedido actualizado exitosamente';
        } else {
            $_SESSION['mensaje_error'] = 'Error al actualizar estado del pedido';
        }
    } catch(PDOException $e) {
        $_SESSION['mensaje_error'] = 'Error al actualizar estado: ' . $e->getMessage();
        error_log("Error al actualizar estado del pedido: " . $e->getMessage());
    }
    
    // Recargar pedidos
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, 
               GROUP_CONCAT(CONCAT(p.nombre, ' x', dv.cantidad)) as productos,
               COUNT(dv.id_detalle) as total_items
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
        LEFT JOIN productos p ON dv.id_producto = p.id_producto
        WHERE v.estado IN ('Pendiente', 'Procesando')
        GROUP BY v.id_venta, v.fecha, v.total, v.estado, v.nota, c.nombre
        ORDER BY v.fecha DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado - Pedidos Recibidos</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .estado-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .estado-pendiente {
            background: #fef3c7;
            color: #d97706;
        }
        
        .estado-procesando {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .estado-procesada {
            background: #d1fadf;
            color: #16a34a;
        }
        
        .btn-actualizar {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-procesar {
            background: #f59e0b;
            color: white;
        }
        
        .btn-completar {
            background: #10b981;
            color: white;
        }
        
        .btn-cancelar {
            background: #ef4444;
            color: white;
        }
    </style>
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
                        <li><a href="index.php">
                            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                        </a></li>
                        <li><a href="ventas.php">
                            <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                        </a></li>
                        <li><a href="inventario.php">
                            <i class="fas fa-boxes"></i> <span>Inventario</span>
                        </a></li>
                        <li><a href="pedidos_recibidos.php" class="active">
                            <i class="fas fa-list"></i> <span>Pedidos</span>
                        </a></li>
                        <li><a href="../admin/productos.php">
                            <i class="fas fa-box"></i> <span>Productos</span>
                        </a></li>
                        <li><a href="../admin/clientes.php">
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
                <h1>Pedidos Recibidos</h1>
                <p>Gestiona los pedidos recibidos y actualiza su estado</p>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success" role="status" aria-live="polite">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['mensaje_exito']; ?></span>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-error" role="status" aria-live="polite">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['mensaje_error']; ?></span>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <div class="card-empleado">
                <h2>Pedidos Pendientes</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Total Items</th>
                                <th>Total</th>
                                <th>Estado Actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pedidos)): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id_venta']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['cliente_nombre'] ?: 'Cliente Público'); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['productos']); ?></td>
                                    <td><?php echo $pedido['total_items']; ?></td>
                                    <td>S/. <?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo strtolower($pedido['estado']); ?>">
                                            <?php echo $pedido['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pedido['estado'] === 'Pendiente'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="accion" value="actualizar_estado">
                                                <input type="hidden" name="id_venta" value="<?php echo $pedido['id_venta']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="Procesando">
                                                <button type="submit" class="btn-actualizar btn-procesar" title="Marcar como en proceso">
                                                    <i class="fas fa-cogs"></i> Procesar
                                                </button>
                                            </form>
                                        <?php elseif ($pedido['estado'] === 'Procesando'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="accion" value="actualizar_estado">
                                                <input type="hidden" name="id_venta" value="<?php echo $pedido['id_venta']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="Procesada">
                                                <button type="submit" class="btn-actualizar btn-completar" title="Marcar como completado">
                                                    <i class="fas fa-check"></i> Completar
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline; margin-left: 5px;">
                                                <input type="hidden" name="accion" value="actualizar_estado">
                                                <input type="hidden" name="id_venta" value="<?php echo $pedido['id_venta']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="Anulada">
                                                <button type="submit" class="btn-actualizar btn-cancelar" title="Cancelar pedido" onclick="return confirm('¿Estás seguro de que deseas cancelar este pedido?')">
                                                    <i class="fas fa-ban"></i> Cancelar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No hay pedidos pendientes</td>
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
        
        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>