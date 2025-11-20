<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

$id_cliente = null;

// Get id_cliente if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
    
    // Obtener el ID del cliente basado en el usuario
    try {
        $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM usuarios WHERE id_usuario = :id_usuario");
        $stmt_cliente->bindParam(':id_usuario', $_SESSION['id_usuario']);
        $stmt_cliente->execute();
        $usuario_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario_cliente && $usuario_cliente['id_cliente']) {
            $id_cliente = $usuario_cliente['id_cliente'];
        }
    } catch(PDOException $e) {
        error_log("Error al obtener id_cliente: " . $e->getMessage());
    }
}

// For logged in users, handle order creation
if ($logueado && $id_cliente && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_pedido') {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    try {
        // Verificar stock y precio disponible
        // FIX: Added 'precio' to the select clause
        $stmt_stock = $pdo->prepare("SELECT stock, precio FROM productos WHERE id_producto = :id_producto");
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
                VALUES (:id_cliente, NULL, :total, 'Pendiente', 'Pedido rápido desde web')
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

            $_SESSION['mensaje_exito'] = '¡Pedido realizado con éxito! Tu orden #' . $id_venta . ' está pendiente.';
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
    <title>Heladería Concelato - Realizar Pedido Rápido</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos adicionales para el formulario en tarjeta */
        .quick-order-form {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            align-items: center;
            justify-content: center;
        }
        
        .qty-input {
            width: 60px;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            text-align: center;
            font-size: 1rem;
        }
        
        .btn-quick-order {
            background: var(--cliente-primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .btn-quick-order:hover {
            background: var(--cliente-secondary);
            transform: translateY(-2px);
        }

        .flavor-tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1><?php echo $logueado ? 'Pedido Rápido' : 'Nuestros Productos'; ?></h1>
                <p><?php echo $logueado ? 'Compra tus helados favoritos con un solo clic' : 'Explora nuestra variedad de sabores'; ?></p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para realizar pedidos, inicia sesión.</p>
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
                <?php if (empty($productos)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>No hay productos disponibles en este momento.</span>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($productos as $producto): ?>
                        <div class="product-card">
                            <div class="product-icon">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <span class="flavor-tag"><?php echo htmlspecialchars($producto['sabor']); ?></span>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="description">
                                <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)) . (strlen($producto['descripcion']) > 80 ? '...' : ''); ?>
                            </p>
                            <div class="price">S/. <?php echo number_format($producto['precio'], 2); ?></div>
                            <div class="stock">
                                <i class="fas fa-box"></i> Stock: <?php echo $producto['stock']; ?>
                            </div>
                            
                            <?php if ($logueado): ?>
                            <form method="POST" class="quick-order-form" onsubmit="return confirmOrder(event, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>');">
                                <input type="hidden" name="accion" value="crear_pedido">
                                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                <input type="number" name="cantidad" value="1" min="0.5" max="<?php echo floatval($producto['stock']); ?>" step="0.5" class="qty-input" title="Cantidad">
                                <button type="submit" class="btn-quick-order">
                                    <i class="fas fa-shopping-bag"></i> Pedir
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn-cliente btn-outline-cliente" onclick="showLoginPrompt()" style="margin-top: 1rem; width: 100%;">
                                <i class="fas fa-lock"></i> Iniciar Sesión
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        async function showLoginPrompt() {
            const confirmed = await ModalCliente.confirm(
                'Debes iniciar sesión para realizar un pedido. ¿Deseas ir a la página de inicio de sesión?',
                'Iniciar Sesión Requerida'
            );
            if (confirmed) {
                window.location.href = '../publico/login.php';
            }
        }

        async function confirmOrder(event, productName) {
            event.preventDefault();
            const confirmed = await ModalCliente.confirm(
                `¿Estás seguro de que deseas pedir ${productName}?`,
                'Confirmar Pedido'
            );
            if (confirmed) {
                event.target.submit();
            }
            return false;
        }
    </script>
    <script src="/heladeriacg/js/cliente/modales.js"></script>
</body>
</html>