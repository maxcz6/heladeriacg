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

$productos = obtenerProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido - Concelato Gelateria</title>
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
                        <li><a href="pedidos.php"><i class="fas fa-list"></i> Mis Pedidos</a></li>
                        <li><a href="estado_pedido.php"><i class="fas fa-truck"></i> Estado Pedido</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="client-main">
            <div class="welcome-section">
                <h1>Realizar Pedido</h1>
                <p>Selecciona los productos que deseas agregar a tu pedido</p>
            </div>

            <div class="order-container">
                <div class="products-section">
                    <h2>Productos Disponibles</h2>
                    <div class="products-grid">
                        <?php foreach ($productos as $producto): ?>
                        <div class="product-card" onclick="addToCart(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['stock']; ?>)">
                            <div class="product-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            <p class="price">S/. <?php echo number_format($producto['precio'], 2); ?></p>
                            <p class="stock">Stock: <?php echo $producto['stock']; ?>L</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cart-section">
                    <h2>Carrito de Pedido</h2>
                    <div class="cart-items" id="cartItems">
                        <p class="empty-cart">El carrito está vacío</p>
                    </div>
                    <div class="cart-total">
                        <h3>Total: S/. <span id="totalAmount">0.00</span></h3>
                    </div>
                    <div class="delivery-options">
                        <h3>Método de entrega:</h3>
                        <div class="delivery-methods">
                            <label class="delivery-option">
                                <input type="radio" name="delivery" value="recojo" checked>
                                <span>Recojo en Tienda</span>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="delivery" value="delivery">
                                <span>Delivery</span>
                            </label>
                        </div>
                    </div>
                    <button class="process-btn" onclick="processOrder()">
                        <i class="fas fa-shopping-cart"></i> Confirmar Pedido
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price, stock) {
            // Verificar si el producto ya está en el carrito
            const existingItem = cart.find(item => item.id === id);

            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity += 1;
                } else {
                    alert('No hay suficiente stock disponible.');
                    return;
                }
            } else {
                if (stock > 0) {
                    cart.push({
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1,
                        stock: stock
                    });
                } else {
                    alert('No hay stock disponible de este producto.');
                    return;
                }
            }

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
                alert('No hay suficiente stock disponible.');
                cart[index].quantity = cart[index].stock;
            }

            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cartItems');
            const totalAmount = document.getElementById('totalAmount');

            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="empty-cart">El carrito está vacío</p>';
                totalAmount.textContent = '0.00';
                return;
            }

            let cartHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                cartHTML += `
                    <div class="cart-item">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <p>Precio: S/. ${item.price.toFixed(2)}</p>
                            <p>Cantidad:
                                <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                                ${item.quantity}
                                <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            </p>
                        </div>
                        <div class="item-total">
                            <p>S/. ${itemTotal.toFixed(2)}</p>
                            <button class="remove-btn" onclick="removeFromCart(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = cartHTML;
            totalAmount.textContent = total.toFixed(2);
        }

        function processOrder() {
            if (cart.length === 0) {
                alert('El carrito está vacío. Por favor, agregue productos antes de confirmar.');
                return;
            }

            const deliveryMethod = document.querySelector('input[name="delivery"]:checked').value;

            // Preparar datos del pedido para enviar al servidor
            const orderData = {
                productos: cart,
                metodo_entrega: deliveryMethod,
                total: parseFloat(document.getElementById('totalAmount').textContent)
            };

            // Enviar datos al servidor usando fetch
            fetch('procesar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Pedido confirmado exitosamente.\n\nID del pedido: ${data.id_venta}\nMétodo de entrega: ${deliveryMethod === 'recojo' ? 'Recojo en tienda' : 'Delivery'}\nTotal: S/. ${data.total.toFixed(2)}`);

                    // Limpiar carrito después del pedido
                    cart = [];
                    updateCart();
                } else {
                    alert('Error al confirmar el pedido: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al confirmar el pedido.');
            });
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>