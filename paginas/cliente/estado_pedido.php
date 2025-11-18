<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('cliente');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener el ID del cliente basado en el usuario
$stmt_cliente = $pdo->prepare("SELECT id_cliente FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_cliente->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_cliente->execute();
$usuario_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

if (!$usuario_cliente) {
    // Si no hay cliente asociado, crear uno
    $stmt_insert = $pdo->prepare("INSERT INTO clientes (nombre, dni, telefono, direccion, correo) VALUES (:nombre, :dni, :telefono, :direccion, :correo)");
    $stmt_insert->bindParam(':nombre', $_SESSION['username']);
    $stmt_insert->bindParam(':dni', '00000000');
    $stmt_insert->bindParam(':telefono', '000000000');
    $stmt_insert->bindParam(':direccion', 'No especificada');
    $stmt_insert->bindParam(':correo', 'cliente@concelato.com');
    $stmt_insert->execute();

    $id_cliente = $pdo->lastInsertId();

    // Actualizar el usuario para asociar con el cliente
    $stmt_update = $pdo->prepare("UPDATE usuarios SET id_cliente = :id_cliente WHERE id_usuario = :id_usuario");
    $stmt_update->bindParam(':id_cliente', $id_cliente);
    $stmt_update->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_update->execute();
} else {
    $id_cliente = $usuario_cliente['id_cliente'];
}

$pedidos_recientes = obtenerPedidosCliente($id_cliente);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Pedido - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/estilos_cliente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="client-container">
        <header class="client-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Cliente
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="pedidos.php"><i class="fas fa-list"></i> Mis Pedidos</a></li>
                        <li><a href="realizar_pedido.php"><i class="fas fa-shopping-cart"></i> Realizar Pedido</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="client-main">
            <div class="welcome-section">
                <h1>Estado de Pedido</h1>
                <p>Consulta el estado actual de tus pedidos</p>
            </div>

            <div class="tracking-section">
                <div class="search-order">
                    <input type="text" id="orderId" placeholder="Ingresa el ID de tu pedido...">
                    <button class="search-btn" onclick="searchOrder()">
                        <i class="fas fa-search"></i> Buscar Pedido
                    </button>
                </div>

                <div class="order-details" id="orderDetails" style="display: none;">
                    <div class="order-header">
                        <h3>ID Pedido: <span id="orderNumber"></span></h3>
                        <span class="status-tag in-progress" id="orderStatus"></span>
                    </div>

                    <div class="order-progress">
                        <div class="progress-step completed">
                            <i class="fas fa-check-circle"></i>
                            <p>Confirmado</p>
                        </div>
                        <div class="progress-step" id="stepPreparacion">
                            <i class="fas fa-clock"></i>
                            <p>En preparación</p>
                        </div>
                        <div class="progress-step" id="stepCamino">
                            <i class="fas fa-truck"></i>
                            <p>En camino</p>
                        </div>
                        <div class="progress-step" id="stepEntregado">
                            <i class="fas fa-check"></i>
                            <p>Entregado</p>
                        </div>
                    </div>

                    <div class="order-info">
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <p>Fecha de pedido</p>
                                <p id="orderDate"></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-shopping-cart"></i>
                            <div>
                                <p>Productos</p>
                                <p id="orderProducts"></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-dollar-sign"></i>
                            <div>
                                <p>Total</p>
                                <p id="orderTotal"></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <p>Entrega</p>
                                <p id="deliveryMethod"></p>
                            </div>
                        </div>
                    </div>

                    <div class="order-products">
                        <h4>Productos:</h4>
                        <ul id="productsList">
                        </ul>
                    </div>
                </div>

                <div class="no-order" id="noOrder">
                    <i class="fas fa-search"></i>
                    <p>Ingresa el ID de tu pedido para ver su estado</p>
                </div>
            </div>

            <div class="recent-orders">
                <h2>Mis Pedidos Recientes</h2>
                <div class="recent-orders-list">
                    <?php if (count($pedidos_recientes) > 0): ?>
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="recent-order-item">
                                <div class="order-info">
                                    <p>ID: <?php echo htmlspecialchars($pedido['id_venta']); ?></p>
                                    <p>Fecha: <?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <span class="status-tag <?php echo strtolower($pedido['estado']) === 'procesada' ? 'delivered' : (strtolower($pedido['estado']) === 'pendiente' ? 'pending' : 'in-progress'); ?>">
                                        <?php
                                        switch(strtolower($pedido['estado'])) {
                                            case 'pendiente': echo 'Pendiente'; break;
                                            case 'procesada': echo 'Procesada'; break;
                                            case 'anulada': echo 'Anulada'; break;
                                            default: echo htmlspecialchars($pedido['estado']); break;
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No tienes pedidos recientes</p>
                    <?php endif; ?>
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

        function searchOrder() {
            const orderId = document.getElementById('orderId').value;

            if (orderId.trim() === '') {
                alert('Por favor, ingresa un ID de pedido válido.');
                return;
            }

            // Hacer una solicitud al servidor para obtener el estado del pedido
            fetch(`obtener_estado_pedido.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la interfaz con la información del pedido
                    document.getElementById('orderNumber').textContent = data.pedido.id_venta;
                    document.getElementById('orderStatus').textContent = 'En preparación';
                    document.getElementById('orderStatus').className = 'status-tag in-progress';
                    document.getElementById('orderDate').textContent = data.pedido.fecha;
                    document.getElementById('orderTotal').textContent = 'S/. ' + parseFloat(data.pedido.total).toFixed(2);
                    document.getElementById('orderProducts').textContent = data.pedido.productos.split(', ').length + ' artículos';
                    document.getElementById('deliveryMethod').textContent = 'Recojo en tienda'; // Por defecto
                    document.getElementById('productsList').innerHTML = data.pedido.productos.replace(/<br>/g, '</li><li>').replace(/, /g, '</li><li>') + '</li>';

                    document.getElementById('orderDetails').style.display = 'block';
                    document.getElementById('noOrder').style.display = 'none';

                    // Actualizar el estado del progreso
                    updateProgress('En preparación');
                } else {
                    alert('Pedido no encontrado');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar el pedido');
            });
        }

        function updateProgress(status) {
            // Actualizar el estado del progreso
            const stepPreparacion = document.getElementById('stepPreparacion');
            const stepCamino = document.getElementById('stepCamino');
            const stepEntregado = document.getElementById('stepEntregado');

            if (status === 'En preparación') {
                stepPreparacion.classList.add('active');
                stepCamino.classList.remove('active');
                stepEntregado.classList.remove('active');
            } else if (status === 'En camino') {
                stepPreparacion.classList.add('completed');
                stepCamino.classList.add('active');
                stepEntregado.classList.remove('active');
            } else if (status === 'Entregado') {
                stepPreparacion.classList.add('completed');
                stepCamino.classList.add('completed');
                stepEntregado.classList.add('active');
            }
        }

        // Simular actualización del estado del pedido
        setInterval(updateOrderStatus, 30000); // Actualizar cada 30 segundos

        function updateOrderStatus() {
            // En una implementación real, esto se haría con AJAX y PHP
            // Por ahora, solo simulamos un cambio de estado
        }
    </script>
</body>
</html>