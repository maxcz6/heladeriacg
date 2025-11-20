<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');  // Include conexion for guests

// Check if user is logged in
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Include clientes_db.php only if user is logged in
if ($logueado) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
}

// Obtener productos para mostrar
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
    <title>Heladería Concelato - <?php echo $logueado ? 'Cliente' : 'Invitado'; ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
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
                <h1>Bienvenido a Concelato Gelateria</h1>
                <p>Disfruta de nuestros deliciosos helados artesanales</p>
                <?php if (!$logueado): ?>
                <p class="guest-notice">Estás navegando como invitado. Para realizar pedidos, inicia sesión o regístrate.</p>
                <?php endif; ?>
            </div>

            <div class="card-cliente">
                <h2>Nuestros Productos</h2>
                
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
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="description">
                                <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)) . (strlen($producto['descripcion']) > 80 ? '...' : ''); ?>
                            </p>
                            <div class="price">S/. <?php echo number_format($producto['precio'], 2); ?></div>
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

        function showLoginPrompt() {
            if (confirm('Debes iniciar sesión para realizar un pedido. ¿Deseas ir a la página de inicio de sesión?')) {
                window.location.href = '../publico/login.php';
            }
        }
    </script>
</body>
</html>