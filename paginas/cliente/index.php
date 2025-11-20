<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('cliente');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener productos para mostrar al cliente
try {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.empresa as proveedor_nombre
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        WHERE p.activo = 1 AND p.stock > 0
        ORDER BY p.nombre
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Cliente</title>
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
                        <li><a href="index.php" class="active">
                            <i class="fas fa-home"></i> <span>Inicio</span>
                        </a></li>
                        <li><a href="pedidos.php">
                            <i class="fas fa-shopping-cart"></i> <span>Mis Pedidos</span>
                        </a></li>
                        <li><a href="estado_pedido.php">
                            <i class="fas fa-truck"></i> <span>Estado Pedido</span>
                        </a></li>
                        <li><a href="invitado.php">
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
                <h1>Bienvenido a Concelato Gelateria</h1>
                <p>Disfruta de nuestros deliciosos helados artesanales</p>
            </div>

            <div class="card-cliente">
                <h2>Nuestros Productos</h2>
                <div class="table-container-cliente">
                    <table class="cliente-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sabor</th>
                                <th>Descripción</th>
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
                                    <td><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . (strlen($producto['descripcion']) > 50 ? '...' : ''); ?></td>
                                    <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?>L</td>
                                    <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-cliente btn-primary-cliente" onclick="realizarPedido(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-shopping-cart"></i> Pedir
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No hay productos disponibles</td>
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
        
        function realizarPedido(id_producto) {
            // Simular proceso de pedido
            alert('Funcionalidad de pedido del producto ID: ' + id_producto);
            // Aquí iría la lógica para crear un pedido
            // window.location.href = 'realizar_pedido.php?id=' + id_producto;
        }
        
        // Toggle mobile menu
        document.querySelector('.menu-toggle-cliente').addEventListener('click', function() {
            const nav = document.getElementById('cliente-nav');
            nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
        });
    </script>
</body>
</html>