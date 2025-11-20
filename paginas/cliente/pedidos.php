<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('cliente');

// Obtener productos disponibles para el cliente
try {
    $stmt = $pdo->prepare("
        SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock,
               pr.empresa as proveedor_nombre
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        WHERE p.activo = 1 AND p.stock > 0
        ORDER BY p.nombre
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
    error_log("Error al obtener productos para cliente: " . $e->getMessage());
}

// Manejar creación de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_pedido') {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $id_cliente = $_SESSION['id_cliente'];

    try {
        // Verificar stock disponible
        $stmt_stock = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id_producto");
        $stmt_stock->bindParam(':id_producto', $id_producto);
        $stmt_stock->execute();
        $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);

        if (!$producto || $producto['stock'] < $cantidad) {
            $_SESSION['mensaje_error'] = 'Stock insuficiente para el producto solicitado';
        } else {
            // Registrar pedido (venta pendiente)
            $pdo->beginTransaction();

            // Crear venta con estado Pendiente
            $stmt_venta = $pdo->prepare("
                INSERT INTO ventas (id_cliente, id_vendedor, total, estado, nota)
                VALUES (:id_cliente, NULL, :total, 'Pendiente', 'Pedido del cliente')
            ");
            $total = $producto['precio'] * $cantidad;
            $stmt_venta->bindParam(':id_cliente', $id_cliente);
            $stmt_venta->bindParam(':total', $total);
            $stmt_venta->execute();
            $id_venta = $pdo->lastInsertId();

            // Crear detalle de venta
            $stmt_detalle = $pdo->prepare("
                INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal)
                VALUES (:id_venta, :id_producto, :cantidad, :precio_unit, :subtotal)
            ");
            $subtotal = $producto['precio'] * $cantidad;
            $stmt_detalle->bindParam(':id_venta', $id_venta);
            $stmt_detalle->bindParam(':id_producto', $id_producto);
            $stmt_detalle->bindParam(':cantidad', $cantidad);
            $stmt_detalle->bindParam(':precio_unit', $producto['precio']);
            $stmt_detalle->bindParam(':subtotal', $subtotal);
            $stmt_detalle->execute();

            $pdo->commit();

            $_SESSION['mensaje_exito'] = 'Pedido registrado exitosamente. Será procesado por nuestro equipo.';
        }
    } catch(PDOException $e) {
        $pdo->rollback();
        $_SESSION['mensaje_error'] = 'Error al procesar el pedido: ' . $e->getMessage();
        error_log("Error al crear pedido: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Cliente - Pedidos</title>
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
                        <li><a href="pedidos.php" class="active">
                            <i class="fas fa-shopping-cart"></i> <span>Mis Pedidos</span>
                        </a></li>
                        <li><a href="estado_pedido.php">
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
                <h1>Realizar Pedido</h1>
                <p>Selecciona los productos que deseas comprar</p>
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

            <div class="card-cliente">
                <div class="table-container-cliente">
                    <table class="cliente-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sabor</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Stock Disponible</th>
                                <th>Proveedor</th>
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
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="crear_pedido">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                            <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>" style="width: 60px; margin-right: 5px;">
                                            <button type="submit" class="btn-cliente btn-primary-cliente">
                                                <i class="fas fa-shopping-cart"></i> Pedir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No hay productos disponibles</td>
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
        document.querySelector('.menu-toggle-cliente').addEventListener('click', function() {
            const nav = document.querySelector('.cliente-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>