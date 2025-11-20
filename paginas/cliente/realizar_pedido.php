<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
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

    // Obtener productos con sus promociones activas
    try {
        $stmt = $pdo->prepare("
            SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock,
                   pr.id_promocion, pr.descuento, pr.fecha_inicio, pr.fecha_fin
            FROM productos p
            LEFT JOIN promociones pr ON p.id_producto = pr.id_producto 
                AND pr.activa = 1 
                AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin
            WHERE p.stock > 0 AND p.activo = 1
            ORDER BY p.nombre
        ");
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $productos = [];
    }
} else {
    $productos = [];
    // For guests, get all products
    try {
        $stmt = $pdo->prepare("
            SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock,
                   pr.id_promocion, pr.descuento, pr.fecha_inicio, pr.fecha_fin
            FROM productos p
            LEFT JOIN promociones pr ON p.id_producto = pr.id_producto 
                AND pr.activa = 1 
                AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin
            WHERE p.stock > 0 AND p.activo = 1
            ORDER BY p.nombre
        ");
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $productos = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concelato Gelateria - Realizar Pedido</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Diseño minimalista y limpio */
        .order-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin-top: 2rem;
        }

        .products-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .products-section h2 {
            font-size: 1.5rem;
            color: var(--cliente-text);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .products-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: var(--radius-md);
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
        }

        .product-item:hover {
            background: #f3f4f6;
            border-color: var(--cliente-primary);
        }

        .product-item.has-discount {
            background: linear-gradient(135deg, #fef3c7 0%, #fff 100%);
            border-color: #f59e0b;
        }

        .discount-badge-mini {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .product-icon-mini {
            width: 50px;
            height: 50px;
            background: var(--cliente-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--cliente-text);
            margin-bottom: 0.25rem;
        }

        .product-desc {
            font-size: 0.85rem;
            color: var(--cliente-text-light);
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--cliente-primary);
            margin-right: 1rem;
        }

        .product-price.has-discount {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .original-price {
            font-size: 0.9rem;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .discounted-price {
            color: #f59e0b;
        }

        .btn-add {
            padding: 0.5rem 1rem;
            background: var(--cliente-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-add:hover {
            background: var(--cliente-secondary);
            transform: scale(1.05);
        }

        /* Sidebar del carrito */
        .cart-sidebar {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .cart-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .cart-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .cart-header h3 {
            font-size: 1.25rem;
            color: var(--cliente-text);
            flex: 1;
        }

        .cart-count {
            background: var(--cliente-accent);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
        }

        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1rem;
        }

        .cart-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: var(--cliente-text);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: var(--cliente-primary);
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .qty-btn:hover {
            background: var(--cliente-secondary);
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--cliente-primary);
        }

        .btn-remove {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .table-selector {
            background: #f0f9ff;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            border: 2px solid #0ea5e9;
        }

        .table-selector label {
            display: block;
            font-weight: 600;
            color: var(--cliente-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .table-selector select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-md);
            font-size: 1rem;
            cursor: pointer;
            background: white;
        }

        /* Cupón */
        .coupon-section {
            background: #fef3c7;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            border: 2px solid #f59e0b;
        }

        .coupon-section label {
            display: block;
            font-weight: 600;
            color: var(--cliente-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .coupon-input-group {
            display: flex;
            gap: 0.5rem;
        }

        .coupon-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-md);
            font-size: 1rem;
            text-transform: uppercase;
        }

        .btn-apply-coupon {
            padding: 0.75rem 1rem;
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-apply-coupon:hover {
            background: #d97706;
        }

        .coupon-applied {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #d1fae5;
            border-radius: var(--radius-md);
            color: #065f46;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-remove-coupon {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1rem;
        }

        .cart-summary {
            padding: 1rem;
            background: #f9fafb;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .summary-row.discount {
            color: #f59e0b;
            font-weight: 600;
        }

        .summary-row.total {
            font-size: 1.1rem;
            font-weight: 700;
            padding-top: 0.5rem;
            border-top: 2px solid #e5e7eb;
            margin-top: 0.5rem;
        }

        .cart-total {
            padding: 1rem;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }

        .cart-total-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .cart-total-amount {
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: var(--cliente-accent);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-checkout:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-checkout:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--cliente-text-light);
        }

        .empty-cart i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            max-width: 400px;
            width: 90%;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            color: var(--cliente-text);
            margin-bottom: 0.5rem;
        }

        .modal-body {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--cliente-text);
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-md);
            font-size: 1rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-modal {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-cancel {
            background: #e5e7eb;
            color: var(--cliente-text);
        }

        .btn-confirm {
            background: var(--cliente-primary);
            color: white;
        }

        .btn-confirm:hover {
            background: var(--cliente-secondary);
        }

        @media (max-width: 1024px) {
            .order-layout {
                grid-template-columns: 1fr;
            }

            .cart-sidebar {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="cliente-container">
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1><?php echo $logueado ? 'Realizar Pedido' : 'Ver Productos'; ?></h1>
                <p><?php echo $logueado ? 'Selecciona tus helados favoritos y confirma tu pedido' : 'Explora nuestros productos. Para realizar pedidos, inicia sesión.'; ?></p>
            </div>

            <?php if ($logueado): ?>
            <div class="order-layout">
                <!-- Lista de productos -->
                <div class="products-section">
                    <h2><i class="fas fa-ice-cream"></i> Nuestros Helados</h2>
                    <div class="products-list">
                        <?php foreach ($productos as $producto): 
                            $hasDiscount = !empty($producto['descuento']);
                            $originalPrice = $producto['precio'];
                            $discountedPrice = $hasDiscount ? $originalPrice * (1 - $producto['descuento'] / 100) : $originalPrice;
                        ?>
                        <div class="product-item <?php echo $hasDiscount ? 'has-discount' : ''; ?>" 
                             onclick="showAddModal(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $originalPrice; ?>, <?php echo $producto['stock']; ?>, <?php echo $hasDiscount ? $producto['descuento'] : 0; ?>)">
                            <?php if ($hasDiscount): ?>
                            <div class="discount-badge-mini">-<?php echo floatval($producto['descuento']); ?>%</div>
                            <?php endif; ?>
                            <div class="product-icon-mini">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                <div class="product-desc"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)); ?>...</div>
                            </div>
                            <div class="product-price <?php echo $hasDiscount ? 'has-discount' : ''; ?>">
                                <?php if ($hasDiscount): ?>
                                    <span class="original-price">S/. <?php echo number_format($originalPrice, 2); ?></span>
                                    <span class="discounted-price">S/. <?php echo number_format($discountedPrice, 2); ?></span>
                                <?php else: ?>
                                    S/. <?php echo number_format($originalPrice, 2); ?>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add" onclick="event.stopPropagation(); showAddModal(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $originalPrice; ?>, <?php echo $producto['stock']; ?>, <?php echo $hasDiscount ? $producto['descuento'] : 0; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Carrito lateral -->
                <div class="cart-sidebar">
                    <div class="cart-container">
                        <div class="cart-header">
                            <i class="fas fa-shopping-cart" style="color: var(--cliente-primary); font-size: 1.5rem;"></i>
                            <h3>Mi Pedido</h3>
                            <div class="cart-count" id="cartCount">0</div>
                        </div>

                        <!-- Selector de mesa -->
                        <div class="table-selector">
                            <label><i class="fas fa-chair"></i> Selecciona tu Mesa</label>
                            <select id="tableNumber">
                                <option value="">-- Elige una mesa --</option>
                                <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>">Mesa <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Cupón de descuento -->
                        <div class="coupon-section">
                            <label><i class="fas fa-ticket-alt"></i> ¿Tienes un cupón?</label>
                            <div class="coupon-input-group">
                                <input type="text" id="couponCode" class="coupon-input" placeholder="Código de cupón">
                                <button class="btn-apply-coupon" onclick="applyCoupon()">Aplicar</button>
                            </div>
                            <div id="couponApplied" style="display: none;" class="coupon-applied">
                                <span id="couponInfo"></span>
                                <button class="btn-remove-coupon" onclick="removeCoupon()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="cart-items" id="cartItems">
                            <div class="empty-cart">
                                <i class="fas fa-shopping-basket"></i>
                                <p>Tu carrito está vacío</p>
                            </div>
                        </div>

                        <!-- Resumen de precios -->
                        <div class="cart-summary" id="cartSummary" style="display: none;">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotalAmount">S/. 0.00</span>
                            </div>
                            <div class="summary-row discount" id="promotionDiscountRow" style="display: none;">
                                <span>Descuentos por promoción:</span>
                                <span id="promotionDiscountAmount">-S/. 0.00</span>
                            </div>
                            <div class="summary-row discount" id="couponDiscountRow" style="display: none;">
                                <span>Cupón aplicado:</span>
                                <span id="couponDiscountAmount">-S/. 0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="totalAmount">S/. 0.00</span>
                            </div>
                        </div>

                        <button class="btn-checkout" id="btnCheckout" onclick="processOrder()" disabled>
                            <i class="fas fa-check-circle"></i>
                            Confirmar Pedido
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card-cliente">
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-lock" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem;"></i>
                    <h3>Inicia sesión para realizar pedidos</h3>
                    <p style="margin-bottom: 2rem;">Debes estar registrado para disfrutar de nuestros deliciosos helados</p>
                    <a href="../publico/login.php" class="btn-cliente btn-primary-cliente">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal para agregar producto -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalProductName"></h3>
                <p id="modalProductPrice" style="color: var(--cliente-primary); font-size: 1.25rem; font-weight: 600;"></p>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Unidad</label>
                    <select id="modalUnit" onchange="updateModalPrice()">
                        <option value="litros">Litros</option>
                        <option value="porciones">Porciones (1 porción = 0.1L)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" id="modalQuantity" value="1" min="1">
                </div>
                <p id="modalStockInfo" style="font-size: 0.875rem; color: var(--cliente-text-light);"></p>
            </div>
            <div class="modal-actions">
                <button class="btn-modal btn-cancel" onclick="closeAddModal()">Cancelar</button>
                <button class="btn-modal btn-confirm" onclick="addToCartFromModal()">
                    <i class="fas fa-cart-plus"></i> Agregar
                </button>
            </div>
        </div>
    </div>

    <script>
        <?php if ($logueado): ?>
        let cart = [];
        let currentProduct = null;
        let appliedCoupon = null;

        function showAddModal(id, name, price, stock, discount = 0) {
            currentProduct = { id, name, price, stock, discount };
            const finalPrice = discount > 0 ? price * (1 - discount / 100) : price;
            
            document.getElementById('modalProductName').textContent = name;
            if (discount > 0) {
                document.getElementById('modalProductPrice').innerHTML = `
                    <span style="text-decoration: line-through; color: #9ca3af; font-size: 1rem;">S/. ${price.toFixed(2)}</span>
                    <span style="color: #f59e0b;">S/. ${finalPrice.toFixed(2)} / Litro (-${discount}%)</span>
                `;
            } else {
                document.getElementById('modalProductPrice').textContent = 'S/. ' + price.toFixed(2) + ' / Litro';
            }
            document.getElementById('modalStockInfo').textContent = 'Stock disponible: ' + stock + ' Litros';
            document.getElementById('modalQuantity').value = 1;
            document.getElementById('modalQuantity').max = stock;
            document.getElementById('modalUnit').value = 'litros';
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            currentProduct = null;
        }

        function updateModalPrice() {
            if (!currentProduct) return;
            
            const unit = document.getElementById('modalUnit').value;
            const qtyInput = document.getElementById('modalQuantity');
            const finalPrice = currentProduct.discount > 0 ? 
                currentProduct.price * (1 - currentProduct.discount / 100) : 
                currentProduct.price;
            
            if (unit === 'porciones') {
                qtyInput.max = Math.floor(currentProduct.stock * 10);
                qtyInput.step = 1;
                const pricePerPortion = finalPrice * 0.1;
                if (currentProduct.discount > 0) {
                    document.getElementById('modalProductPrice').innerHTML = `
                        <span style="text-decoration: line-through; color: #9ca3af; font-size: 1rem;">S/. ${(currentProduct.price * 0.1).toFixed(2)}</span>
                        <span style="color: #f59e0b;">S/. ${pricePerPortion.toFixed(2)} / Porción (-${currentProduct.discount}%)</span>
                    `;
                } else {
                    document.getElementById('modalProductPrice').textContent = 'S/. ' + pricePerPortion.toFixed(2) + ' / Porción';
                }
                document.getElementById('modalStockInfo').textContent = 'Stock disponible: ' + Math.floor(currentProduct.stock * 10) + ' Porciones';
            } else {
                qtyInput.max = currentProduct.stock;
                qtyInput.step = 0.1;
                if (currentProduct.discount > 0) {
                    document.getElementById('modalProductPrice').innerHTML = `
                        <span style="text-decoration: line-through; color: #9ca3af; font-size: 1rem;">S/. ${currentProduct.price.toFixed(2)}</span>
                        <span style="color: #f59e0b;">S/. ${finalPrice.toFixed(2)} / Litro (-${currentProduct.discount}%)</span>
                    `;
                } else {
                    document.getElementById('modalProductPrice').textContent = 'S/. ' + currentProduct.price.toFixed(2) + ' / Litro';
                }
                document.getElementById('modalStockInfo').textContent = 'Stock disponible: ' + currentProduct.stock + ' Litros';
            }
        }

        function addToCartFromModal() {
            if (!currentProduct) return;
            
            const unit = document.getElementById('modalUnit').value;
            let quantity = parseFloat(document.getElementById('modalQuantity').value);
            
            if (unit === 'porciones') {
                quantity = quantity * 0.1;
            }
            
            if (quantity <= 0 || quantity > currentProduct.stock) {
                ModalCliente.warning('La cantidad ingresada no es válida. Por favor, verifica el stock disponible.');
                return;
            }
            
            const existingItem = cart.find(item => item.id === currentProduct.id);
            if (existingItem) {
                if (existingItem.quantity + quantity <= currentProduct.stock) {
                    existingItem.quantity += quantity;
                } else {
                    ModalCliente.warning('No hay suficiente stock disponible para agregar más unidades de este producto.');
                    return;
                }
            } else {
                cart.push({
                    id: currentProduct.id,
                    name: currentProduct.name,
                    price: currentProduct.price,
                    discount: currentProduct.discount,
                    quantity: quantity,
                    stock: currentProduct.stock
                });
            }
            
            updateCart();
            closeAddModal();
        }

        function applyCoupon() {
            const code = document.getElementById('couponCode').value.trim().toUpperCase();
            if (!code) {
                ModalCliente.warning('Por favor, ingresa un código de cupón válido.');
                return;
            }

            // Verificar cupón en el servidor
            fetch('/heladeriacg/paginas/cliente/verificar_cupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ codigo: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    appliedCoupon = {
                        codigo: data.cupon.codigo,
                        descuento: parseFloat(data.cupon.descuento),
                        tipo: data.cupon.tipo_descuento
                    };
                    document.getElementById('couponInfo').textContent = `${appliedCoupon.codigo} (-${appliedCoupon.descuento}${appliedCoupon.tipo === 'porcentaje' ? '%' : ' S/.'})`;
                    document.getElementById('couponApplied').style.display = 'flex';
                    document.getElementById('couponCode').value = '';
                    updateCart();
                } else {
                    ModalCliente.error(data.message || 'El cupón ingresado es inválido o ha expirado.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ModalCliente.error('Ocurrió un error al verificar el cupón. Por favor, intenta nuevamente.');
            });
        }

        function removeCoupon() {
            appliedCoupon = null;
            document.getElementById('couponApplied').style.display = 'none';
            updateCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function updateQuantity(index, change) {
            cart[index].quantity += change;

            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            } else if (cart[index].quantity > cart[index].stock) {
                ModalCliente.warning('No hay suficiente stock disponible.');
                cart[index].quantity = cart[index].stock;
            }

            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cartItems');
            const cartCount = document.getElementById('cartCount');
            const btnCheckout = document.getElementById('btnCheckout');
            const cartSummary = document.getElementById('cartSummary');

            cartCount.textContent = cart.length;

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Tu carrito está vacío</p>
                    </div>
                `;
                cartSummary.style.display = 'none';
                btnCheckout.disabled = true;
                return;
            }

            let cartHTML = '';
            let subtotal = 0;
            let promotionDiscount = 0;

            cart.forEach((item, index) => {
                const originalPrice = item.price * item.quantity;
                const discountedPrice = item.discount > 0 ? 
                    originalPrice * (1 - item.discount / 100) : 
                    originalPrice;
                
                if (item.discount > 0) {
                    promotionDiscount += (originalPrice - discountedPrice);
                }
                
                subtotal += discountedPrice;

                cartHTML += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}${item.discount > 0 ? ` <span style="color: #f59e0b;">(-${item.discount}%)</span>` : ''}</div>
                            <div class="cart-item-qty">
                                <button class="qty-btn" onclick="updateQuantity(${index}, -0.1)">-</button>
                                <span style="font-size: 0.875rem; font-weight: 600;">${item.quantity.toFixed(1)}L</span>
                                <button class="qty-btn" onclick="updateQuantity(${index}, 0.1)">+</button>
                            </div>
                        </div>
                        <div>
                            <div class="cart-item-price">S/. ${discountedPrice.toFixed(2)}</div>
                            <button class="btn-remove" onclick="removeFromCart(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = cartHTML;

            // Calcular descuento de cupón
            let couponDiscount = 0;
            if (appliedCoupon) {
                if (appliedCoupon.tipo === 'porcentaje') {
                    couponDiscount = subtotal * (appliedCoupon.descuento / 100);
                } else {
                    couponDiscount = appliedCoupon.descuento;
                }
            }

            const total = Math.max(0, subtotal - couponDiscount);

            // Actualizar resumen
            document.getElementById('subtotalAmount').textContent = 'S/. ' + (subtotal + promotionDiscount).toFixed(2);
            
            if (promotionDiscount > 0) {
                document.getElementById('promotionDiscountRow').style.display = 'flex';
                document.getElementById('promotionDiscountAmount').textContent = '-S/. ' + promotionDiscount.toFixed(2);
            } else {
                document.getElementById('promotionDiscountRow').style.display = 'none';
            }

            if (couponDiscount > 0) {
                document.getElementById('couponDiscountRow').style.display = 'flex';
                document.getElementById('couponDiscountAmount').textContent = '-S/. ' + couponDiscount.toFixed(2);
            } else {
                document.getElementById('couponDiscountRow').style.display = 'none';
            }

            document.getElementById('totalAmount').textContent = 'S/. ' + total.toFixed(2);
            cartSummary.style.display = 'block';
            btnCheckout.disabled = false;
        }

        function processOrder() {
            if (cart.length === 0) {
                ModalCliente.warning('Tu carrito está vacío. Agrega productos antes de confirmar el pedido.');
                return;
            }

            const tableNumber = document.getElementById('tableNumber').value;
            if (!tableNumber) {
                ModalCliente.warning('Por favor, selecciona una mesa antes de confirmar tu pedido.');
                document.getElementById('tableNumber').focus();
                return;
            }

            const orderData = {
                productos: cart,
                metodo_entrega: 'recojo',
                mesa: tableNumber,
                cupon: appliedCoupon ? appliedCoupon.codigo : null
            };

            fetch('/heladeriacg/paginas/cliente/procesar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ModalCliente.success(
                        `Pedido #${data.id_venta}\nMesa: ${tableNumber}\nTotal: S/. ${data.total.toFixed(2)}\n\nTu pedido será preparado pronto.`,
                        '¡Pedido Confirmado!'
                    ).then(() => {
                        cart = [];
                        appliedCoupon = null;
                        updateCart();
                        document.getElementById('tableNumber').value = '';
                        document.getElementById('couponApplied').style.display = 'none';
                        window.location.href = 'estado_pedido.php';
                    });
                } else {
                    ModalCliente.error('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ModalCliente.error('Ocurrió un error al procesar tu pedido. Por favor, intenta nuevamente.');
            });
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });
        <?php endif; ?>

        async function cerrarSesion() {
            const confirmed = await ModalCliente.confirm(
                'Se cerrará tu sesión actual. ¿Deseas continuar?',
                '¿Cerrar Sesión?'
            );
            if (confirmed) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
    <script src="/heladeriacg/js/cliente/modales.js"></script>
</body>
</html>