<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener el ID del empleado basado en el usuario
$stmt_empleado = $pdo->prepare("SELECT id_vendedor FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado) {
    // Si no hay empleado asociado, crear uno
    $stmt_insert = $pdo->prepare("INSERT INTO vendedores (nombre, dni, telefono, correo, turno) VALUES (:nombre, :dni, :telefono, :correo, :turno)");
    $stmt_insert->bindParam(':nombre', $_SESSION['username']);
    $stmt_insert->bindParam(':dni', '00000000');
    $stmt_insert->bindParam(':telefono', '000000000');
    $stmt_insert->bindParam(':correo', 'empleado@concelato.com');
    $stmt_insert->bindParam(':turno', 'Mañana');
    $stmt_insert->execute();
    
    $id_vendedor = $pdo->lastInsertId();
    
    // Actualizar el usuario para asociar con el empleado
    $stmt_update = $pdo->prepare("UPDATE usuarios SET id_vendedor = :id_vendedor WHERE id_usuario = :id_usuario");
    $stmt_update->bindParam(':id_vendedor', $id_vendedor);
    $stmt_update->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_update->execute();
} else {
    $id_vendedor = $usuario_empleado['id_vendedor'];
}

$productos = obtenerProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema POS - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - POS
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="inventario.php"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li><a href="pedidos_recibidos.php"><i class="fas fa-list"></i> Pedidos</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="employee-main">
            <div class="pos-section">
                <h1>Sistema POS</h1>
                <div class="pos-container">
                    <div class="products-section">
                        <h2>Productos Disponibles</h2>
                        <div class="products-grid">
                            <?php foreach ($productos as $producto): ?>
                            <div class="product-card" onclick="addToCart(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['stock']; ?>)">
                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p class="price">S/. <?php echo number_format($producto['precio'], 2); ?></p>
                                <p class="stock">Stock: <?php echo $producto['stock']; ?>L</p>
                                <div class="product-icon">
                                    <i class="fas fa-ice-cream"></i>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="cart-section">
                        <h2>Carrito de Ventas</h2>
                        <div class="cart-items" id="cartItems">
                            <p class="empty-cart">El carrito está vacío</p>
                        </div>
                        <div class="cart-total">
                            <h3>Total: S/. <span id="totalAmount">0.00</span></h3>
                        </div>
                        <div class="customer-info">
                            <input type="text" id="customerName" placeholder="Nombre del cliente (opcional)">
                            <input type="text" id="customerDNI" placeholder="DNI del cliente (opcional)">
                        </div>
                        <button class="process-btn" onclick="processSale()">
                            <i class="fas fa-check"></i> Procesar Venta
                        </button>
                        <button class="clear-btn" onclick="clearCart()">
                            <i class="fas fa-trash"></i> Limpiar Carrito
                        </button>
                    </div>
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
        
        function processSale() {
            if (cart.length === 0) {
                alert('El carrito está vacío. Por favor, agregue productos antes de procesar la venta.');
                return;
            }
            
            // Obtener información del cliente (opcional)
            const customerName = document.getElementById('customerName').value || 'Cliente Contado';
            const customerDNI = document.getElementById('customerDNI').value || null;
            
            // Preparar datos de la venta para enviar al servidor
            const saleData = {
                productos: cart,
                total: parseFloat(document.getElementById('totalAmount').textContent),
                cliente_nombre: customerName,
                cliente_dni: customerDNI
            };
            
            // Enviar datos al servidor usando fetch
            fetch('procesar_venta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saleData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Venta procesada exitosamente.\n\nID de venta: ${data.id_venta}\nTotal: S/. ${data.total.toFixed(2)}\n\nGracias por la venta!`);
                    
                    // Limpiar carrito después de la venta
                    cart = [];
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerDNI').value = '';
                    updateCart();
                } else {
                    alert('Error al procesar la venta: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al procesar la venta.');
            });
        }
        
        function clearCart() {
            if (cart.length > 0 && confirm('¿Estás seguro de que deseas limpiar el carrito?')) {
                cart = [];
                updateCart();
            }
        }
        
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>