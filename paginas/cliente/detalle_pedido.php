<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

$pedido = null;
$detalle_pedido = [];
$id_cliente = null;

if ($logueado) {
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

    if (!isset($_GET['id']) || !$id_cliente) {
        header('Location: estado_pedido.php');
        exit();
    }

    $id_pedido = $_GET['id'];

    // Obtener detalles del pedido
    try {
        $stmt_pedido = $pdo->prepare("
            SELECT v.*, c.nombre as cliente_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venta = :id_venta AND v.id_cliente = :id_cliente
        ");
        $stmt_pedido->bindParam(':id_venta', $id_pedido);
        $stmt_pedido->bindParam(':id_cliente', $id_cliente);
        $stmt_pedido->execute();
        $pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            $_SESSION['mensaje_error'] = 'Pedido no encontrado o no tienes permiso para verlo';
            header('Location: estado_pedido.php');
            exit();
        }

        // Obtener detalles del pedido con información de promociones
        $fecha_venta = $pedido['fecha'];
        
        $stmt_detalle = $pdo->prepare("
            SELECT dv.*, p.nombre as producto_nombre, p.sabor, p.precio as precio_original,
                   pr.descuento as descuento_promocion
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            LEFT JOIN promociones pr ON p.id_producto = pr.id_producto 
                AND pr.activa = 1 
                AND DATE(:fecha_venta) BETWEEN pr.fecha_inicio AND pr.fecha_fin
            WHERE dv.id_venta = :id_venta
        ");
        $stmt_detalle->bindParam(':id_venta', $id_pedido);
        $stmt_detalle->bindParam(':fecha_venta', $fecha_venta);
        $stmt_detalle->execute();
        $detalle_pedido = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular totales y descuentos
        $subtotal_original = 0;
        $descuento_promocion_total = 0;
        
        foreach ($detalle_pedido as &$detalle) {
            $precio_original = floatval($detalle['precio_original']);
            $cantidad = floatval($detalle['cantidad']);
            $descuento_promo = floatval($detalle['descuento_promocion'] ?? 0);
            
            // Si el precio unitario en detalle es menor que el original, puede haber descuento
            // Pero usamos la lógica de promociones para ser consistentes
            
            $subtotal_item_original = $precio_original * $cantidad;
            $subtotal_original += $subtotal_item_original;
            
            $detalle['subtotal_original'] = $subtotal_item_original;
            $detalle['descuento_monto'] = 0;
            
            if ($descuento_promo > 0) {
                $precio_con_descuento = $precio_original * (1 - $descuento_promo / 100);
                $descuento_item = ($precio_original - $precio_con_descuento) * $cantidad;
                $descuento_promocion_total += $descuento_item;
                $detalle['descuento_monto'] = $descuento_item;
            }
        }
        unset($detalle); // Romper referencia
        
        $subtotal_con_promo = $subtotal_original - $descuento_promocion_total;
        
        // Extraer información de cupón de la nota
        $cupon_codigo = null;
        $descuento_cupon = 0;
        
        if ($pedido['nota'] && preg_match('/Cupón:\s*([A-Z0-9]+)/i', $pedido['nota'], $matches)) {
            $cupon_codigo = $matches[1];
            
            // Calcular descuento de cupón
            $descuento_cupon = $subtotal_con_promo - floatval($pedido['total']);
            if ($descuento_cupon < 0) $descuento_cupon = 0;
        }
        
        // Pasar variables a la vista
        $pedido['subtotal_original'] = $subtotal_original;
        $pedido['descuento_promocion'] = $descuento_promocion_total;
        $pedido['descuento_cupon'] = $descuento_cupon;
        $pedido['cupon_codigo'] = $cupon_codigo;
    } catch(PDOException $e) {
        $_SESSION['mensaje_error'] = 'Error al obtener detalles del pedido';
        header('Location: estado_pedido.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Detalle Pedido</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .badge-promo {
            background: #10b981;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .price-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #9ca3af;
            font-size: 0.85rem;
        }
        
        .discounted-price {
            color: #059669;
            font-weight: 600;
        }
        
        .summary-row td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        
        .summary-row.discount td {
            color: #059669;
            background-color: #f0fdf4;
        }
        
        .text-right {
            text-align: right;
            font-weight: 600;
        }
        
        .discount-amount {
            color: #059669;
            font-weight: 700;
        }
        
        .total-row td {
            padding: 1rem;
            background-color: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }
        
        .total-amount {
            color: var(--cliente-primary);
            font-size: 1.25rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <?php if ($logueado && $pedido): ?>
            <div class="welcome-section-cliente">
                <h1>Detalle del Pedido #<?php echo $pedido['id_venta']; ?></h1>
                <p>Información detallada del pedido realizado</p>
            </div>

            <div class="card-cliente">
                <h2>Información del Pedido</h2>
                <div class="pedido-info">
                    <div class="info-item">
                        <strong>ID Pedido:</strong>
                        <span>#<?php echo $pedido['id_venta']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Estado:</strong>
                        <span class="status-badge <?php
                            echo $pedido['estado'] === 'Procesada' ? 'active' :
                            ($pedido['estado'] === 'Pendiente' ? 'warning' : 'inactive'); ?>">
                            <?php echo $pedido['estado']; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Total:</strong>
                        <span>S/. <?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Cliente:</strong>
                        <span><?php echo htmlspecialchars($pedido['cliente_nombre'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Observaciones:</strong>
                        <span><?php echo htmlspecialchars($pedido['nota'] ?: 'Ninguna'); ?></span>
                    </div>
                </div>
            </div>

            <div class="card-cliente">
                <h2>Productos del Pedido</h2>
                <div class="table-container-cliente">
                    <table class="cliente-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Sabor</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle_pedido as $detalle): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($detalle['producto_nombre']); ?>
                                    <?php if (isset($detalle['descuento_promocion']) && $detalle['descuento_promocion'] > 0): ?>
                                        <span class="badge-promo">
                                            <i class="fas fa-tag"></i> -<?php echo floatval($detalle['descuento_promocion']); ?>%
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($detalle['sabor']); ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td>
                                    <?php if (isset($detalle['descuento_promocion']) && $detalle['descuento_promocion'] > 0): ?>
                                        <div class="price-container">
                                            <span class="original-price">S/. <?php echo number_format($detalle['precio_original'], 2); ?></span>
                                            <span class="discounted-price">S/. <?php echo number_format($detalle['precio_unit'], 2); ?></span>
                                        </div>
                                    <?php else: ?>
                                        S/. <?php echo number_format($detalle['precio_unit'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td>S/. <?php echo number_format($detalle['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php if ($pedido['descuento_promocion'] > 0 || $pedido['descuento_cupon'] > 0): ?>
                                <tr class="summary-row">
                                    <td colspan="4" class="text-right">Subtotal:</td>
                                    <td>S/. <?php echo number_format($pedido['subtotal_original'], 2); ?></td>
                                </tr>
                                
                                <?php if ($pedido['descuento_promocion'] > 0): ?>
                                <tr class="summary-row discount">
                                    <td colspan="4" class="text-right">
                                        <i class="fas fa-tag"></i> Descuento por promoción:
                                    </td>
                                    <td class="discount-amount">-S/. <?php echo number_format($pedido['descuento_promocion'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($pedido['descuento_cupon'] > 0): ?>
                                <tr class="summary-row discount">
                                    <td colspan="4" class="text-right">
                                        <i class="fas fa-ticket-alt"></i> Cupón (<?php echo $pedido['cupon_codigo']; ?>):
                                    </td>
                                    <td class="discount-amount">-S/. <?php echo number_format($pedido['descuento_cupon'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <tr class="total-row">
                                <td colspan="4" class="text-right">TOTAL:</td>
                                <td class="total-amount">S/. <?php echo number_format($pedido['total'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="welcome-section-cliente">
                <h1>Detalle del Pedido</h1>
                <p>Consulta los detalles de tus pedidos</p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para ver los detalles de tus pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <div class="card-cliente">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Inicia sesión para ver los detalles del pedido</h3>
                    <p>Debes estar registrado para consultar los detalles de tus pedidos</p>
                    <a href="../publico/login.php" class="btn-cliente btn-primary-cliente" style="margin-top: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>