<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

$pedidos = [];
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
            
            // Obtener pedidos del cliente con cálculo de descuentos
            $stmt = $pdo->prepare("
                SELECT v.id_venta, v.fecha, v.total, v.estado, v.nota,
                       GROUP_CONCAT(CONCAT(p.nombre, ' (', dv.cantidad, 'L)') SEPARATOR ', ') as productos,
                       COUNT(dv.id_detalle) as total_items
                FROM ventas v
                LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                WHERE v.id_cliente = :id_cliente
                GROUP BY v.id_venta, v.fecha, v.total, v.estado, v.nota
                ORDER BY v.fecha DESC
            ");
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->execute();
            $pedidos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar cada pedido para calcular descuentos
            foreach ($pedidos_raw as $pedido) {
                $id_venta = $pedido['id_venta'];
                $fecha_venta = $pedido['fecha'];
                
                // Calcular subtotal sin descuentos y descuentos de promoción
                $stmt_detalles = $pdo->prepare("
                    SELECT dv.cantidad, dv.precio_unit, dv.subtotal, p.precio as precio_original,
                           pr.descuento as descuento_promocion
                    FROM detalle_ventas dv
                    JOIN productos p ON dv.id_producto = p.id_producto
                    LEFT JOIN promociones pr ON p.id_producto = pr.id_producto 
                        AND pr.activa = 1 
                        AND DATE(:fecha_venta) BETWEEN pr.fecha_inicio AND pr.fecha_fin
                    WHERE dv.id_venta = :id_venta
                ");
                $stmt_detalles->bindParam(':id_venta', $id_venta);
                $stmt_detalles->bindParam(':fecha_venta', $fecha_venta);
                $stmt_detalles->execute();
                $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
                
                $subtotal_original = 0;
                $descuento_promocion_total = 0;
                
                foreach ($detalles as $detalle) {
                    $precio_original = floatval($detalle['precio_original']);
                    $cantidad = floatval($detalle['cantidad']);
                    $descuento_promo = floatval($detalle['descuento_promocion'] ?? 0);
                    
                    $subtotal_original += $precio_original * $cantidad;
                    
                    if ($descuento_promo > 0) {
                        $precio_con_descuento = $precio_original * (1 - $descuento_promo / 100);
                        $descuento_promocion_total += ($precio_original - $precio_con_descuento) * $cantidad;
                    }
                }
                
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
                
                $pedido['subtotal_original'] = $subtotal_original;
                $pedido['descuento_promocion'] = $descuento_promocion_total;
                $pedido['descuento_cupon'] = $descuento_cupon;
                $pedido['cupon_codigo'] = $cupon_codigo;
                
                $pedidos[] = $pedido;
            }
        }
    } catch(PDOException $e) {
        $pedidos = [];
        error_log("Error al obtener pedidos del cliente: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Estado de Pedidos</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .order-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            border-left: 5px solid;
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            opacity: 0.05;
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .order-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        /* Colores según estado - Mejorados */
        .order-card.pendiente {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
        }

        .order-card.pendiente::before {
            background: #f59e0b;
        }

        .order-card.procesada {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
        }

        .order-card.procesada::before {
            background: #10b981;
        }

        .order-card.cancelada {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        }

        .order-card.cancelada::before {
            background: #ef4444;
        }

        .order-card.entregada,
        .order-card.entregado {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        }

        .order-card.entregada::before,
        .order-card.entregado::before {
            background: #3b82f6;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .order-id {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-id::before {
            content: '#';
            color: #9ca3af;
            font-size: 1.25rem;
        }

        .order-status {
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-transform: capitalize;
        }

        .order-status i {
            font-size: 1rem;
        }

        .order-status.pendiente {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .order-status.procesada {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .order-status.cancelada {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .order-status.entregada,
        .order-status.entregado {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }

        .order-info {
            margin-bottom: 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }

        .order-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
        }

        .order-info-item i {
            color: #0ea5e9;
            width: 20px;
            text-align: center;
        }

        .order-products {
            background: rgba(255, 255, 255, 0.8);
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .order-products-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-products-title::before {
            content: '🛍️';
        }

        .order-products-list {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.8;
        }

        .order-pricing {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }

        .price-row.discount {
            color: #059669;
            font-size: 0.9rem;
        }

        .price-row.discount i {
            margin-right: 0.25rem;
        }

        .discount-amount {
            font-weight: 600;
            color: #059669;
        }

        .price-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
            margin: 0.75rem 0;
        }

        .price-row.total {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1f2937;
            padding-top: 0.75rem;
            border-top: 2px solid #e5e7eb;
        }

        .price-row.total span:last-child {
            color: #0ea5e9;
        }

        .order-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-view-details {
            flex: 1;
            min-width: 150px;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);
        }

        .btn-view-details:hover {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(14, 165, 233, 0.3);
        }

        .btn-view-details i {
            font-size: 1.1rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            color: var(--cliente-text);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--cliente-text-light);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1><?php echo $logueado ? 'Estado de Mis Pedidos' : 'Consulta de Pedidos'; ?></h1>
                <p><?php echo $logueado ? 'Consulta el estado de tus pedidos recientes' : 'Inicia sesión para ver el estado de tus pedidos.'; ?></p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para ver tus pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <?php if ($logueado): ?>
                <?php if (!empty($pedidos)): ?>
                <div class="orders-grid">
                    <?php foreach ($pedidos as $pedido): 
                        $estado_class = strtolower($pedido['estado']);
                        $icon = '';
                        switch($estado_class) {
                            case 'pendiente':
                                $icon = 'fa-clock';
                                break;
                            case 'procesada':
                                $icon = 'fa-check-circle';
                                break;
                            case 'cancelada':
                                $icon = 'fa-times-circle';
                                break;
                            case 'entregada':
                                $icon = 'fa-box-check';
                                break;
                            default:
                                $icon = 'fa-info-circle';
                        }
                    ?>
                    <div class="order-card <?php echo $estado_class; ?>">
                        <div class="order-header">
                            <div class="order-id">#<?php echo $pedido['id_venta']; ?></div>
                            <div class="order-status <?php echo $estado_class; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                                <?php echo ucfirst($pedido['estado']); ?>
                            </div>
                        </div>

                        <div class="order-info">
                            <div class="order-info-item">
                                <i class="far fa-calendar"></i>
                                <span><?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?></span>
                            </div>
                            <div class="order-info-item">
                                <i class="far fa-clock"></i>
                                <span><?php echo date('H:i', strtotime($pedido['fecha'])); ?></span>
                            </div>
                            <div class="order-info-item">
                                <i class="fas fa-shopping-bag"></i>
                                <span><?php echo $pedido['total_items']; ?> producto(s)</span>
                            </div>
                        </div>

                        <div class="order-products">
                            <div class="order-products-title">Productos:</div>
                            <div class="order-products-list">
                                <?php echo htmlspecialchars($pedido['productos'] ?: 'Sin productos'); ?>
                            </div>
                        </div>

                        <?php if ($pedido['nota']): ?>
                        <div class="order-info-item" style="margin-bottom: 1rem;">
                            <i class="fas fa-sticky-note"></i>
                            <span><?php echo htmlspecialchars($pedido['nota']); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="order-pricing">
                            <?php if ($pedido['descuento_promocion'] > 0 || $pedido['descuento_cupon'] > 0): ?>
                                <div class="price-row">
                                    <span>Subtotal:</span>
                                    <span>S/. <?php echo number_format($pedido['subtotal_original'], 2); ?></span>
                                </div>
                                
                                <?php if ($pedido['descuento_promocion'] > 0): ?>
                                <div class="price-row discount">
                                    <span><i class="fas fa-tag"></i> Descuento por promoción:</span>
                                    <span class="discount-amount">-S/. <?php echo number_format($pedido['descuento_promocion'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($pedido['descuento_cupon'] > 0): ?>
                                <div class="price-row discount">
                                    <span><i class="fas fa-ticket-alt"></i> Cupón (<?php echo $pedido['cupon_codigo']; ?>):</span>
                                    <span class="discount-amount">-S/. <?php echo number_format($pedido['descuento_cupon'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="price-divider"></div>
                            <?php endif; ?>
                            
                            <div class="price-row total">
                                <span>Total:</span>
                                <span>S/. <?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                        </div>

                        <div class="order-actions">
                            <button class="btn-view-details" onclick="verDetallePedido(<?php echo $pedido['id_venta']; ?>)">
                                <i class="fas fa-eye"></i>
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No tienes pedidos registrados</h3>
                    <p>Cuando realices tu primer pedido, aparecerá aquí</p>
                    <a href="realizar_pedido.php" class="btn-cliente btn-primary-cliente">
                        <i class="fas fa-plus"></i> Hacer mi Primer Pedido
                    </a>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="card-cliente">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Inicia sesión para ver tus pedidos</h3>
                    <p>Debes estar registrado para consultar el estado de tus pedidos</p>
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

        function verDetallePedido(id_pedido) {
            window.location.href = 'detalle_pedido.php?id=' + id_pedido;
        }
    </script>
    <script src="/heladeriacg/js/cliente/modales.js"></script>
</body>
</html>