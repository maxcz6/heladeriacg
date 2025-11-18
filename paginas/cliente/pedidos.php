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

$pedidos = obtenerPedidosCliente($id_cliente);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Concelato Gelateria</title>
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
                        <li><a href="estado_pedido.php"><i class="fas fa-truck"></i> Estado Pedido</a></li>
                        <li><a href="../../paginas/publico/index.php"><i class="fas fa-globe"></i> Público</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="client-main">
            <div class="welcome-section">
                <h1>Mis Pedidos</h1>
                <p>Aquí puedes ver el historial de tus pedidos</p>
            </div>

            <div class="orders-section">
                <div class="search-filter">
                    <input type="text" id="searchOrders" placeholder="Buscar pedido..." onkeyup="searchOrders()">
                    <select id="filterStatus" onchange="filterOrders()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en preparación">En preparación</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="orders-list" id="ordersList">
                    <?php if (count($pedidos) > 0): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <h3>ID Pedido: <?php echo htmlspecialchars($pedido['id_venta']); ?></h3>
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
                                <div class="order-details">
                                    <p><i class="fas fa-calendar"></i> Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></p>
                                    <p><i class="fas fa-shopping-cart"></i> Productos: <?php echo htmlspecialchars($pedido['cantidad_total']); ?></p>
                                    <p><i class="fas fa-dollar-sign"></i> Total: S/. <?php echo number_format($pedido['total'], 2); ?></p>
                                </div>
                                <div class="order-products">
                                    <h4>Productos:</h4>
                                    <ul>
                                        <li><?php echo htmlspecialchars($pedido['productos']); ?></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No tienes pedidos registrados aún</p>
                            <a href="realizar_pedido.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Hacer tu primer pedido
                            </a>
                        </div>
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

        function searchOrders() {
            // Esta función se implementará para filtrar pedidos
            const input = document.getElementById('searchOrders');
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

        function filterOrders() {
            // Esta función se implementará para filtrar por estado
            const filter = document.getElementById('filterStatus').value;
            const orderCards = document.querySelectorAll('.order-card');

            for (let i = 0; i < orderCards.length; i++) {
                const orderCard = orderCards[i];
                const status = orderCard.querySelector('.status-tag').textContent.toLowerCase();

                if (filter === '' || status.includes(filter)) {
                    orderCard.style.display = '';
                } else {
                    orderCard.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>