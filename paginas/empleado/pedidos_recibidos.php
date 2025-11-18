<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener pedidos pendientes
$stmt_pedidos = $pdo->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado, v.id_cliente, c.nombre as cliente_nombre
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE v.estado IN ('Pendiente', 'Procesada')
    ORDER BY v.fecha DESC
");
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Recibidos - Concelato Gelateria</title>
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
                    Concelato Gelateria - Pedidos
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
            <div class="orders-section">
                <h1>Pedidos Recibidos</h1>
                
                <div class="orders-filters">
                    <select id="filterStatus" onchange="filterOrders()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente">Pendientes</option>
                        <option value="Procesada">Procesadas</option>
                        <option value="Anulada">Anuladas</option>
                    </select>
                    <input type="text" id="searchOrder" placeholder="Buscar pedido..." onkeyup="searchOrders()">
                </div>

                <div class="orders-list" id="ordersList">
                    <?php if (count($pedidos) > 0): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="order-card" data-status="<?php echo $pedido['estado']; ?>">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h3>ID Pedido: <?php echo htmlspecialchars($pedido['id_venta']); ?></h3>
                                        <p>Cliente: <?php echo htmlspecialchars($pedido['cliente_nombre'] ?: 'Desconocido'); ?></p>
                                        <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></p>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-tag <?php echo strtolower($pedido['estado']) === 'procesada' ? 'delivered' : (strtolower($pedido['estado']) === 'pendiente' ? 'pending' : 'cancelled'); ?>">
                                            <?php echo htmlspecialchars($pedido['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-details">
                                    <p><i class="fas fa-dollar-sign"></i> Total: S/. <?php echo number_format($pedido['total'], 2); ?></p>
                                    <div class="order-actions">
                                        <?php if ($pedido['estado'] === 'Pendiente'): ?>
                                            <button class="action-btn" onclick="updateOrderStatus(<?php echo $pedido['id_venta']; ?>, 'Procesada')">
                                                <i class="fas fa-check"></i> Procesar
                                            </button>
                                            <button class="action-btn cancel" onclick="updateOrderStatus(<?php echo $pedido['id_venta']; ?>, 'Anulada')">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        <?php elseif ($pedido['estado'] === 'Procesada'): ?>
                                            <button class="action-btn finish" onclick="updateOrderStatus(<?php echo $pedido['id_venta']; ?>, 'Finalizada')">
                                                <i class="fas fa-check-circle"></i> Finalizar
                                            </button>
                                        <?php endif; ?>
                                        <button class="action-btn view" onclick="viewOrderDetails(<?php echo $pedido['id_venta']; ?>)">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No hay pedidos recibidos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para ver detalles del pedido -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetailsModal()">&times;</span>
            <h2>Detalles del Pedido</h2>
            <div id="orderDetailsContent">
                <!-- El contenido se cargará dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        function updateOrderStatus(id_venta, nuevo_estado) {
            if (confirm(`¿Estás seguro de que deseas cambiar el estado del pedido ${id_venta} a ${nuevo_estado}?`)) {
                // Enviar la actualización al servidor
                fetch('actualizar_estado_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_venta: id_venta,
                        estado: nuevo_estado
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Estado del pedido actualizado exitosamente');
                        location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error al actualizar estado: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al actualizar estado');
                });
            }
        }

        function viewOrderDetails(id_venta) {
            // Cargar los detalles del pedido
            fetch(`obtener_detalle_pedido.php?id=${id_venta}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pedido = data.pedido;
                    let detallesHtml = `
                        <div class="order-detail-info">
                            <h3>ID Pedido: ${pedido.id_venta}</h3>
                            <p><strong>Cliente:</strong> ${pedido.cliente_nombre || 'Desconocido'}</p>
                            <p><strong>Fecha:</strong> ${pedido.fecha}</p>
                            <p><strong>Estado:</strong> ${pedido.estado}</p>
                            <p><strong>Total:</strong> S/. ${parseFloat(pedido.total).toFixed(2)}</p>
                        </div>
                        <h4>Productos:</h4>
                        <div class="order-products-list">
                    `;
                    
                    if (pedido.productos) {
                        const productos = pedido.productos.split(', ');
                        productos.forEach(producto => {
                            detallesHtml += `<p class="product-item">${producto}</p>`;
                        });
                    }
                    
                    detallesHtml += `</div>`;
                    
                    document.getElementById('orderDetailsContent').innerHTML = detallesHtml;
                    document.getElementById('detailsModal').style.display = 'block';
                } else {
                    alert('Error al obtener detalles del pedido: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener detalles del pedido');
            });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function filterOrders() {
            const filter = document.getElementById('filterStatus').value;
            const orderCards = document.querySelectorAll('.order-card');
            
            for (let i = 0; i < orderCards.length; i++) {
                const orderCard = orderCards[i];
                const status = orderCard.getAttribute('data-status');
                
                if (filter === '' || status === filter) {
                    orderCard.style.display = '';
                } else {
                    orderCard.style.display = 'none';
                }
            }
        }

        function searchOrders() {
            const input = document.getElementById('searchOrder');
            const filter = input.value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            
            for (let i = 0; i < orderCards.length; i++) {
                const orderCard = orderCards[i];
                const orderId = orderCard.querySelector('h3').textContent.toLowerCase();
                
                if (orderId.includes(filter)) {
                    orderCard.style.display = '';
                } else {
                    orderCard.style.display = 'none';
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
            if (event.target === document.getElementById('detailsModal')) {
                closeDetailsModal();
            }
        }
    </script>
</body>
</html>