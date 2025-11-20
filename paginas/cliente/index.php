<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');  // Include conexion for guests

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
}

// Obtener productos para mostrar con sus promociones
try {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.empresa as proveedor_nombre,
               promo.id_promocion, promo.descuento, promo.fecha_inicio, promo.fecha_fin
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        LEFT JOIN promociones promo ON p.id_producto = promo.id_producto 
            AND promo.activa = 1 
            AND CURDATE() BETWEEN promo.fecha_inicio AND promo.fecha_fin
        WHERE p.activo = 1 AND p.stock > 0
        ORDER BY p.nombre
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
}

// Obtener promociones activas
try {
    $stmt_promo = $pdo->prepare("
        SELECT p.*, pr.nombre as producto_nombre
        FROM promociones p
        JOIN productos pr ON p.id_producto = pr.id_producto
        WHERE p.activa = 1 
        AND p.fecha_inicio <= CURDATE() 
        AND p.fecha_fin >= CURDATE()
        ORDER BY p.fecha_fin ASC
    ");
    $stmt_promo->execute();
    $promociones = $stmt_promo->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $promociones = [];
    error_log("Error al obtener promociones: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - <?php echo $logueado ? 'Cliente' : 'Invitado'; ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para la sección de promociones */
        .promo-section {
            margin-bottom: 3rem;
        }
        
        .promo-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .promo-card {
            background: linear-gradient(135deg, #fff 0%, #f0f9ff 100%);
            border: 1px solid var(--cliente-border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .promo-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--cliente-gradient);
        }
        
        .promo-card h3 {
            color: var(--cliente-primary);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }
        
        .promo-card p {
            color: var(--cliente-text-light);
            margin-bottom: 0.5rem;
        }
        
        .promo-card .validity {
            font-size: 0.85rem;
            color: var(--cliente-text);
            font-weight: 500;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .promo-card .validity i {
            color: var(--cliente-accent);
        }

        .discount-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--cliente-accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: var(--shadow-sm);
        }

        /* Estilos para productos con promoción */
        .product-card.has-promo {
            background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
            border-color: #f59e0b;
        }

        .promo-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
            z-index: 1;
        }

        .product-card .price {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .original-price {
            font-size: 0.9rem;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .discounted-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="cliente-container">
        <!-- Header con navegación -->
        <?php include 'includes/navbar.php'; ?>

        <main class="cliente-main">
            <div class="welcome-section-cliente">
                <h1>Bienvenido a Concelato Gelateria</h1>
                <p>Disfruta de nuestros deliciosos helados artesanales</p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para realizar pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <!-- Sección de Promociones Dinámicas -->
            <?php if (!empty($promociones)): ?>
            <div class="promo-section">
                <h2 style="color: var(--cliente-text); font-size: 1.75rem; margin-bottom: 1rem;">Promociones Actuales</h2>
                <div class="promo-cards">
                    <?php foreach ($promociones as $promo): ?>
                    <div class="promo-card">
                        <div class="discount-badge">
                            <?php echo floatval($promo['descuento']); ?>% OFF
                        </div>
                        <h3><?php echo htmlspecialchars($promo['producto_nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($promo['descripcion'] ?: 'Aprovecha este descuento especial'); ?></p>
                        <div class="validity">
                            <i class="far fa-clock"></i>
                            Válido hasta: <?php echo date('d/m/Y', strtotime($promo['fecha_fin'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-cliente">
                <h2>Nuestros Productos</h2>
                
                <?php if (empty($productos)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>No hay productos disponibles en este momento.</span>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($productos as $producto): 
                            $hasDiscount = !empty($producto['descuento']);
                            $originalPrice = $producto['precio'];
                            $discountedPrice = $hasDiscount ? $originalPrice * (1 - $producto['descuento'] / 100) : $originalPrice;
                        ?>
                        <div class="product-card <?php echo $hasDiscount ? 'has-promo' : ''; ?>">
                            <?php if ($hasDiscount): ?>
                            <div class="promo-badge">
                                -<?php echo floatval($producto['descuento']); ?>% OFF
                            </div>
                            <?php endif; ?>
                            <div class="product-icon">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="description">
                                <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)) . (strlen($producto['descripcion']) > 80 ? '...' : ''); ?>
                            </p>
                            <div class="price">
                                <?php if ($hasDiscount): ?>
                                    <span class="original-price">S/. <?php echo number_format($originalPrice, 2); ?></span>
                                    <span class="discounted-price">S/. <?php echo number_format($discountedPrice, 2); ?></span>
                                <?php else: ?>
                                    S/. <?php echo number_format($originalPrice, 2); ?>
                                <?php endif; ?>
                            </div>
                            <div class="stock">
                                <i class="fas fa-box"></i> Stock: <?php echo $producto['stock']; ?>
                            </div>
                            
                            <?php if ($logueado): ?>
                            <button class="order-btn" onclick="realizarPedido(<?php echo $producto['id_producto']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Pedir Ahora
                            </button>
                            <?php else: ?>
                            <button class="order-btn" onclick="showLoginPrompt()">
                                <i class="fas fa-lock"></i> Iniciar Sesión para Pedir
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
        function realizarPedido(id_producto) {
            window.location.href = 'realizar_pedido.php?id=' + id_producto;
        }

        async function showLoginPrompt() {
            const confirmed = await ModalCliente.confirm(
                'Debes iniciar sesión para realizar un pedido. ¿Deseas ir a la página de inicio de sesión?',
                'Iniciar Sesión Requerida'
            );
            if (confirmed) {
                window.location.href = '../publico/login.php';
            }
        }
    </script>
    <script src="/heladeriacg/js/cliente/modales.js"></script>
</body>
</html>