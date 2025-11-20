<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('empleado');

// Obtener productos para el sistema de ventas
try {
    $stmt_productos = $pdo->prepare("SELECT * FROM productos WHERE activo = 1 AND stock > 0 ORDER BY nombre");
    $stmt_productos->execute();
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener clientes
    $stmt_clientes = $pdo->prepare("SELECT id_cliente, nombre FROM clientes ORDER BY nombre");
    $stmt_clientes->execute();
    $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
    $clientes = [];
    error_log("Error al obtener datos en ventas empleado: " . $e->getMessage());
}

// Procesar venta si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_venta') {
    $productos_venta = $_POST['productos'] ?? [];
    $id_cliente = $_POST['id_cliente'] ?? null;
    $id_vendedor = $_SESSION['id_vendedor'];
    
    if (empty($productos_venta)) {
        $_SESSION['mensaje_error'] = 'No hay productos en el carrito';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Calcular total
            $total = 0;
            $productos_para_insertar = [];
            
            foreach ($productos_venta as $item) {
                $id_producto = $item['id'];
                $cantidad = $item['cantidad'];
                
                // Verificar disponibilidad de stock
                $stmt_stock = $pdo->prepare("SELECT stock, precio FROM productos WHERE id_producto = :id_producto");
                $stmt_stock->bindParam(':id_producto', $id_producto);
                $stmt_stock->execute();
                $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);
                
                if (!$producto || $producto['stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente para " . $producto['nombre'] ?? 'producto desconocido');
                }
                
                $subtotal = $producto['precio'] * $cantidad;
                $total += $subtotal;
                
                $productos_para_insertar[] = [
                    'id_producto' => $id_producto,
                    'cantidad' => $cantidad,
                    'precio_unit' => $producto['precio'],
                    'subtotal' => $subtotal
                ];
            }
            
            // Crear venta
            $stmt_venta = $pdo->prepare("
                INSERT INTO ventas (id_cliente, id_vendedor, total, estado)
                VALUES (:id_cliente, :id_vendedor, :total, 'Procesada')
            ");
            $stmt_venta->bindParam(':id_cliente', $id_cliente);
            $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
            $stmt_venta->bindParam(':total', $total);
            $stmt_venta->execute();
            $id_venta = $pdo->lastInsertId();
            
            // Insertar detalles de venta y actualizar stock
            foreach ($productos_para_insertar as $prod) {
                // Insertar detalle de venta
                $stmt_detalle = $pdo->prepare("
                    INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal)
                    VALUES (:id_venta, :id_producto, :cantidad, :precio_unit, :subtotal)
                ");
                $stmt_detalle->bindParam(':id_venta', $id_venta);
                $stmt_detalle->bindParam(':id_producto', $prod['id_producto']);
                $stmt_detalle->bindParam(':cantidad', $prod['cantidad']);
                $stmt_detalle->bindParam(':precio_unit', $prod['precio_unit']);
                $stmt_detalle->bindParam(':subtotal', $prod['subtotal']);
                $stmt_detalle->execute();
                
                // Actualizar stock
                $stmt_update_stock = $pdo->prepare("
                    UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto
                ");
                $stmt_update_stock->bindParam(':cantidad', $prod['cantidad']);
                $stmt_update_stock->bindParam(':id_producto', $prod['id_producto']);
                $stmt_update_stock->execute();
            }
            
            $pdo->commit();
            
            $_SESSION['mensaje_exito'] = 'Venta procesada exitosamente. Total: S/. ' . number_format($total, 2);
            // Limpiar carrito
            echo '<script>localStorage.removeItem("carrito_venta");</script>';
        } catch(Exception $e) {
            $pdo->rollback();
            $_SESSION['mensaje_error'] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado - Sistema de Ventas</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/modernos_estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="empleado-container">
        <!-- Header con navegación -->
        <header class="empleado-header">
            <div class="header-content-empleado">
                <button class="menu-toggle-empleado" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="empleado-nav">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-empleado">
                    <i class="fas fa-ice-cream"></i>
                    <span>Concelato Empleado</span>
                </div>
                <nav id="empleado-nav" class="empleado-nav">
                    <ul>
                        <li><a href="index.php">
                            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                        </a></li>
                        <li><a href="ventas.php" class="active">
                            <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                        </a></li>
                        <li><a href="inventario.php">
                            <i class="fas fa-boxes"></i> <span>Inventario</span>
                        </a></li>
                        <li><a href="pedidos_recibidos.php">
                            <i class="fas fa-list"></i> <span>Pedidos</span>
                        </a></li>
                        <li><a href="../admin/productos.php">
                            <i class="fas fa-box"></i> <span>Productos</span>
                        </a></li>
                        <li><a href="../admin/clientes.php">
                            <i class="fas fa-user-friends"></i> <span>Clientes</span>
                        </a></li>
                    </ul>
                </nav>
                <button class="logout-btn-empleado" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </button>
            </div>
        </header>

        <main class="empleado-main">
            <div class="welcome-section-empleado">
                <h1>Sistema de Ventas - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Registra ventas de productos de la heladería</p>
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

            <!-- Formulario de venta -->
            <div class="card-empleado">
                <form method="POST" id="formaVenta">
                    <input type="hidden" name="accion" value="crear_venta">

                    <div class="venta-section">
                        <div class="productos-disponibles">
                            <h3>Productos Disponibles</h3>
                            <div id="productosList">
                                <?php foreach ($productos as $producto): ?>
                                <div class="producto-item" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['stock']; ?>)">
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong> - <?php echo htmlspecialchars($producto['sabor']); ?>
                                    <br>
                                    Precio: S/. <?php echo number_format($producto['precio'], 2); ?> | Stock: <?php echo $producto['stock']; ?>L
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="carrito-venta">
                            <h3>Carrito de Venta</h3>
                            <div id="carritoItems">
                                <!-- Los items del carrito se mostrarán aquí -->
                            </div>
                            <div class="total-carrito">
                                Total: <span id="totalCarrito">S/. 0.00</span>
                            </div>

                            <div style="margin-top: 1.5rem;">
                                <label for="id_cliente">Cliente (Opcional):</label>
                                <select name="id_cliente" id="id_cliente">
                                    <option value="">Cliente Público</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id_cliente']; ?>">
                                        <?php echo htmlspecialchars($cliente['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn-finalizar" id="btnFinalizar">
                                <i class="fas fa-check-circle"></i> Finalizar Venta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        let carrito = JSON.parse(localStorage.getItem('carrito_venta')) || [];

        // Cargar carrito desde localStorage
        function cargarCarrito() {
            const carritoDiv = document.getElementById('carritoItems');
            carritoDiv.innerHTML = '';

            if (carrito.length === 0) {
                carritoDiv.innerHTML = '<p>No hay productos en el carrito</p>';
                document.getElementById('totalCarrito').textContent = 'S/. 0.00';
                return;
            }

            let total = 0;
            carrito.forEach((item, index) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'item-carrito';
                itemDiv.innerHTML = `
                    <div>
                        <strong>${item.nombre}</strong><br>
                        Cantidad: ${item.cantidad} x S/. ${item.precio.toFixed(2)} = S/. ${subtotal.toFixed(2)}
                    </div>
                    <div class="acciones-carrito">
                        <button type="button" class="btn-carrito btn-add" onclick="modificarCantidad(${index}, 1)">+</button>
                        <button type="button" class="btn-carrito btn-remove" onclick="quitarDelCarrito(${index})">-</button>
                    </div>
                `;
                carritoDiv.appendChild(itemDiv);
            });

            document.getElementById('totalCarrito').textContent = 'S/. ' + total.toFixed(2);
        }

        function agregarAlCarrito(id, nombre, precio, stock) {
            // Verificar si el producto ya está en el carrito
            const index = carrito.findIndex(item => item.id === id);

            if (index !== -1) {
                // Si ya está, aumentar cantidad si hay stock suficiente
                if (carrito[index].cantidad < stock) {
                    carrito[index].cantidad++;
                } else {
                    alert('No hay suficiente stock disponible');
                    return;
                }
            } else {
                // Si no está, agregar al carrito
                if (stock > 0) {
                    carrito.push({
                        id: id,
                        nombre: nombre,
                        precio: precio,
                        cantidad: 1
                    });
                } else {
                    alert('No hay stock disponible para este producto');
                    return;
                }
            }

            // Guardar en localStorage
            localStorage.setItem('carrito_venta', JSON.stringify(carrito));
            cargarCarrito();

            // Resaltar el producto seleccionado
            const productoItems = document.querySelectorAll('.producto-item');
            productoItems.forEach(item => {
                if (item.textContent.includes(nombre)) {
                    item.classList.add('selected');
                    setTimeout(() => item.classList.remove('selected'), 500);
                }
            });
        }

        function modificarCantidad(index, cambio) {
            if (cambio > 0) {
                // Aumentar cantidad
                if (carrito[index].cantidad < carrito[index].disponible) { // Suponiendo que tenemos stock disponible
                    carrito[index].cantidad++;
                } else {
                    alert('No hay suficiente stock disponible');
                    return;
                }
            } else {
                // Disminuir cantidad
                carrito[index].cantidad--;
                if (carrito[index].cantidad <= 0) {
                    carrito.splice(index, 1);
                }
            }

            localStorage.setItem('carrito_venta', JSON.stringify(carrito));
            cargarCarrito();
        }

        function quitarDelCarrito(index) {
            carrito.splice(index, 1);
            localStorage.setItem('carrito_venta', JSON.stringify(carrito));
            cargarCarrito();
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        // Inicializar carrito
        document.addEventListener('DOMContentLoaded', function() {
            cargarCarrito();

            // Validar antes de enviar el formulario
            document.getElementById('formaVenta').addEventListener('submit', function(e) {
                if (carrito.length === 0) {
                    alert('No hay productos en el carrito');
                    e.preventDefault();
                    return false;
                }

                // Convertir carrito a formato para envío
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'productos';
                input.value = JSON.stringify(carrito);
                this.appendChild(input);
            });
        });

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>