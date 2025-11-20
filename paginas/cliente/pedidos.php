<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
}

// For logged in users, handle order creation
if ($logueado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_pedido') {
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

// Obtener productos disponibles
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Pedidos</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1><?php echo $logueado ? 'Realizar Pedido' : 'Ver Productos Disponibles'; ?></h1>
                <p><?php echo $logueado ? 'Selecciona los productos que deseas comprar' : 'Explora nuestros productos. Para realizar pedidos, inicia sesión.'; ?></p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para realizar pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
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
                                        <?php if ($logueado): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="crear_pedido">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                            <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>" style="width: 60px; margin-right: 5px;">
                                            <button type="submit" class="btn-cliente btn-primary-cliente">
                                                <i class="fas fa-shopping-cart"></i> Pedir
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn-cliente btn-outline-cliente" onclick="showLoginPrompt()">
                                            <i class="fas fa-lock"></i> Pedir
                                        </button>
                                        <?php endif; ?>
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
        function showLoginPrompt() {
            if (confirm('Debes iniciar sesión para realizar un pedido. ¿Deseas ir a la página de inicio de sesión?')) {
                window.location.href = '../publico/login.php';
            }
        }
    </script>
</body>
</html>