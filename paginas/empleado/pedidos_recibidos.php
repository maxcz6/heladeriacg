<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Obtener el ID del empleado y sucursal
$stmt_empleado = $pdo->prepare("SELECT id_vendedor, id_sucursal FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado || !$usuario_empleado['id_sucursal']) {
    header('Location: ../publico/login.php');
    exit();
}

$id_vendedor = $usuario_empleado['id_vendedor'];
$id_sucursal = $usuario_empleado['id_sucursal'];

// Obtener pedidos (ventas pendientes) para esta sucursal
$stmt_pedidos = $pdo->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado, v.nota, 
           c.nombre as cliente_nombre
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE v.id_sucursal = :id_sucursal AND v.estado != 'Anulada'
    ORDER BY v.fecha DESC
");
$stmt_pedidos->bindParam(':id_sucursal', $id_sucursal);
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

// Si se está actualizando el estado de un pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado') {
    $id_venta = $_POST['id_venta'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        $stmt = $pdo->prepare("UPDATE ventas SET estado = :estado WHERE id_venta = :id_venta");
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id_venta', $id_venta);
        
        if ($stmt->execute()) {
            $mensaje = "Estado del pedido actualizado exitosamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar el estado del pedido";
            $tipo_mensaje = "error";
        }
    } catch(PDOException $e) {
        $mensaje = "Error de base de datos: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Recibidos - <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .pedidos-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .pedidos-table th, .pedidos-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .pedidos-table th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        
        .estado-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .estado-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .estado-procesada {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .estado-anulada {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .detalle-pedido {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 6px;
        }
        
        .accion-form {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?> - Pedidos
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="ventas.php"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
                        <li><a href="inventario.php"><i class="fas fa-boxes"></i> Inventario</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="employee-main">
            <div class="welcome-section">
                <h1>Pedidos Recibidos</h1>
                <p>Gestión de pedidos en <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?></p>
            </div>

            <?php if (isset($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="pedidos-actions">
                <div class="search-filter">
                    <input type="text" id="searchPedido" placeholder="Buscar pedido..." onkeyup="searchPedidos()">
                    <select id="filterEstado" onchange="filterPedidos()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente">Pendientes</option>
                        <option value="Procesada">Procesadas</option>
                        <option value="Anulada">Anuladas</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table class="pedidos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosTable">
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr data-estado="<?php echo $pedido['estado']; ?>">
                            <td><?php echo $pedido['id_venta']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($pedido['cliente_nombre'] ?: 'Cliente Contado'); ?></td>
                            <td>S/. <?php echo number_format($pedido['total'], 2); ?></td>
                            <td>
                                <span class="estado-badge
                                    <?php 
                                    if ($pedido['estado'] === 'Pendiente') echo 'estado-pendiente';
                                    elseif ($pedido['estado'] === 'Procesada') echo 'estado-procesada';
                                    elseif ($pedido['estado'] === 'Anulada') echo 'estado-anulada';
                                    ?>">
                                    <?php echo htmlspecialchars($pedido['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn view" onclick="verDetalle(<?php echo $pedido['id_venta']; ?>)">
                                    <i class="fas fa-eye"></i> Detalle
                                </button>
                                <?php if ($pedido['estado'] !== 'Anulada'): ?>
                                <form method="POST" class="accion-form" onsubmit="return confirm('¿Actualizar estado del pedido?');">
                                    <input type="hidden" name="accion" value="actualizar_estado">
                                    <input type="hidden" name="id_venta" value="<?php echo $pedido['id_venta']; ?>">
                                    <select name="nuevo_estado" required onchange="this.form.submit()">
                                        <option value="">Cambiar estado...</option>
                                        <?php if ($pedido['estado'] !== 'Procesada'): ?>
                                        <option value="Procesada" <?php echo ($pedido['estado'] === 'Procesada') ? 'selected' : ''; ?>>Procesada</option>
                                        <?php endif; ?>
                                        <?php if ($pedido['estado'] !== 'Anulada'): ?>
                                        <option value="Anulada" <?php echo ($pedido['estado'] === 'Anulada') ? 'selected' : ''; ?>>Anulada</option>
                                        <?php endif; ?>
                                    </select>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal para ver detalle de pedido -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetalleModal()">&times;</span>
            <h2>Detalle del Pedido</h2>
            <div id="detalleContent">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        function verDetalle(id_venta) {
            // Cargar los detalles del pedido
            fetch(`../admin/obtener_detalle_venta.php?id=${id_venta}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const venta = data.venta;
                    const detalle = data.detalle;

                    let detalleHtml = `
                        <div class="venta-detail-info">
                            <h3>Detalle del Pedido #${venta.id_venta}</h3>
                            <p><strong>Cliente:</strong> ${venta.cliente_nombre || 'Cliente Contado'}</p>
                            <p><strong>Vendedor:</strong> ${venta.vendedor_nombre || 'Desconocido'}</p>
                            <p><strong>Fecha:</strong> ${venta.fecha}</p>
                            <p><strong>Estado:</strong> ${venta.estado}</p>
                            <p><strong>Total:</strong> S/. ${parseFloat(venta.total).toFixed(2)}</p>
                            <p><strong>Nota:</strong> ${venta.nota || 'Sin notas'}</p>
                        </div>
                        <h4>Productos:</h4>
                        <div class="venta-products-list">
                    `;

                    detalle.forEach(item => {
                        detalleHtml += `<div class="product-item">${item.producto_nombre} - ${item.cantidad} x S/.${parseFloat(item.precio_unit).toFixed(2)} = S/.${parseFloat(item.subtotal).toFixed(2)}</div>`;
                    });

                    detalleHtml += `</div>`;

                    document.getElementById('detalleContent').innerHTML = detalleHtml;
                    document.getElementById('detalleModal').style.display = 'block';
                } else {
                    alert('Error al obtener detalles del pedido: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener detalles del pedido');
            });
        }

        function closeDetalleModal() {
            document.getElementById('detalleModal').style.display = 'none';
        }

        function searchPedidos() {
            const input = document.getElementById('searchPedido');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#pedidosTable tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const idCell = row.cells[0].textContent.toLowerCase();
                const clienteCell = row.cells[2].textContent.toLowerCase();

                if (idCell.includes(filter) || clienteCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function filterPedidos() {
            const filter = document.getElementById('filterEstado').value;
            const rows = document.querySelectorAll('#pedidosTable tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const estado = row.getAttribute('data-estado');

                if (filter === '' || estado === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        // Cerrar modal si se hace clic fuera de él
        window.onclick = function(event) {
            if (event.target === document.getElementById('detalleModal')) {
                closeDetalleModal();
            }
        }
    </script>
</body>
</html>