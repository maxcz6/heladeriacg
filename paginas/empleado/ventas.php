<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Obtener el ID del empleado y sucursal
$stmt_empleado = $pdo->prepare("SELECT id_vendedor, id_sucursal FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado || !$usuario_empleado['id_sucursal']) {
    header('Location: ../publico/login.php');
    exit();
}

$id_vendedor = $usuario_empleado['id_vendedor'];
$id_sucursal = $usuario_empleado['id_sucursal'];

// Obtener productos disponibles en la sucursal
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, i.stock_sucursal as stock
    FROM productos p
    JOIN inventario_sucursal i ON p.id_producto = i.id_producto
    WHERE p.activo = 1 AND i.id_sucursal = :id_sucursal AND i.stock_sucursal > 0
    ORDER BY p.nombre
");
$stmt_productos->bindParam(':id_sucursal', $id_sucursal);
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// Obtener clientes
$stmt_clientes = $pdo->prepare("SELECT id_cliente, nombre FROM clientes ORDER BY nombre");
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Si se está procesando una venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_venta') {
    $productos_venta = json_decode($_POST['productos'], true);
    $id_cliente = $_POST['id_cliente'] ?? null;
    $nota = $_POST['nota'] ?? '';
    $total = floatval($_POST['total']);

    $venta_datos = [
        'id_cliente' => $id_cliente,
        'id_vendedor' => $id_vendedor,
        'id_sucursal' => $id_sucursal,
        'total' => $total,
        'nota' => $nota,
        'productos' => $productos_venta
    ];

    $resultado = registrarVentaSucursal($venta_datos);

    if ($resultado['success']) {
        $mensaje = "Venta registrada exitosamente. ID: " . $resultado['id_venta'];
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al registrar la venta: " . $resultado['message'];
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta - <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .venta-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .productos-section, .carrito-section {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .producto-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .producto-item:hover {
            background-color: #f0f8ff;
            transform: translateY(-2px);
        }
        
        .producto-item .precio {
            font-weight: bold;
            color: #0891b2;
        }
        
        .producto-item .stock {
            font-size: 0.9em;
            color: #666;
        }
        
        .cantidad-input {
            width: 60px;
            padding: 5px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .carrito-items {
            margin: 15px 0;
        }
        
        .carrito-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .total-section {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .btn-procesar {
            background: #0891b2;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 15px;
        }
        
        .btn-procesar:hover {
            background: #0e7490;
        }
    </style>
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?> - Venta
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
            <div class="welcome-section">
                <h1>Registrar Nueva Venta</h1>
                <p>Seleccione los productos para la venta</p>
            </div>

            <?php if (isset($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="venta-container">
                <div class="productos-section">
                    <h2>Productos Disponibles</h2>
                    <div class="productos-grid">
                        <?php foreach ($productos as $producto): ?>
                        <div class="producto-item" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['stock']; ?>)">
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="sabor"><?php echo htmlspecialchars($producto['sabor']); ?></p>
                            <p class="descripcion"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . (strlen($producto['descripcion']) > 50 ? '...' : ''); ?></p>
                            <p class="precio">S/. <?php echo number_format($producto['precio'], 2); ?></p>
                            <p class="stock">Stock: <?php echo $producto['stock']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="carrito-section">
                    <h2>Carrito de Compras</h2>
                    <div class="cliente-section">
                        <label for="id_cliente">Cliente:</label>
                        <select id="id_cliente" name="id_cliente">
                            <option value="">Cliente Contado</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id_cliente']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="carrito-items" id="carritoItems">
                        <!-- Los productos seleccionados se mostrarán aquí -->
                        <p id="mensajeCarrito">El carrito está vacío. Agregue productos usando los botones de los productos.</p>
                    </div>
                    
                    <div class="total-section">
                        <h3>Total: S/. <span id="totalVenta">0.00</span></h3>
                        <div class="nota-section">
                            <label for="nota">Nota (Opcional):</label>
                            <textarea id="nota" name="nota" rows="2" placeholder="Ingrese alguna observación..."></textarea>
                        </div>
                        <button class="btn-procesar" onclick="procesarVenta()">Procesar Venta</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let carrito = [];
        
        function agregarAlCarrito(id, nombre, precio, stock) {
            const cantidad = parseInt(prompt(`¿Cuántas unidades de ${nombre} desea agregar? Stock disponible: ${stock}`, 1));
            
            if (cantidad && cantidad > 0 && cantidad <= stock) {
                // Verificar si el producto ya está en el carrito
                const productoExistente = carrito.find(item => item.id === id);
                
                if (productoExistente) {
                    // Actualizar cantidad si ya existe
                    productoExistente.cantidad += cantidad;
                } else {
                    // Agregar nuevo producto al carrito
                    carrito.push({
                        id: id,
                        nombre: nombre,
                        precio: precio,
                        cantidad: cantidad
                    });
                }
                
                actualizarCarrito();
            } else if (cantidad > stock) {
                alert(`No hay suficiente stock. Disponible: ${stock}`);
            }
        }
        
        function actualizarCarrito() {
            const carritoItems = document.getElementById('carritoItems');
            const mensajeCarrito = document.getElementById('mensajeCarrito');
            const totalVenta = document.getElementById('totalVenta');
            
            if (carrito.length === 0) {
                carritoItems.innerHTML = '<p id="mensajeCarrito">El carrito está vacío. Agregue productos usando los botones de los productos.</p>';
                totalVenta.textContent = '0.00';
                return;
            }
            
            let html = '';
            let total = 0;
            
            carrito.forEach((item, index) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;
                
                html += `
                    <div class="carrito-item">
                        <div>
                            <strong>${item.nombre}</strong><br>
                            ${item.cantidad} x S/. ${item.precio.toFixed(2)} = S/. ${subtotal.toFixed(2)}
                        </div>
                        <div>
                            <button onclick="eliminarDelCarrito(${index})" class="action-btn delete">Eliminar</button>
                        </div>
                    </div>
                `;
            });
            
            carritoItems.innerHTML = html;
            mensajeCarrito.remove(); // Remover el mensaje de carrito vacío
            totalVenta.textContent = total.toFixed(2);
        }
        
        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            actualizarCarrito();
        }
        
        function procesarVenta() {
            if (carrito.length === 0) {
                alert('El carrito está vacío. Agregue productos antes de procesar la venta.');
                return;
            }
            
            const id_cliente = document.getElementById('id_cliente').value;
            const nota = document.getElementById('nota').value;
            const total = parseFloat(document.getElementById('totalVenta').textContent);
            
            // Confirmar la venta
            if (confirm(`¿Confirmar venta por S/. ${total.toFixed(2)}?`)) {
                // Enviar los datos al servidor
                const formData = new FormData();
                formData.append('accion', 'registrar_venta');
                formData.append('productos', JSON.stringify(carrito));
                formData.append('id_cliente', id_cliente);
                formData.append('nota', nota);
                formData.append('total', total);
                
                fetch('procesar_venta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Venta registrada exitosamente. ID: ${data.id_venta}`);
                        // Limpiar carrito
                        carrito = [];
                        actualizarCarrito();
                        document.getElementById('nota').value = '';
                        document.getElementById('id_cliente').value = '';
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al procesar la venta');
                });
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