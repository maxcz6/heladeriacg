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
    $raw_productos = $_POST['productos'] ?? '[]';
    $productos_venta = json_decode($raw_productos, true);

    // Verificar si la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($productos_venta)) {
        $productos_venta = [];
    }

    $id_cliente = $_POST['id_cliente'] ?? null;
    $id_usuario = $_SESSION['id_usuario']; // Capturamos el ID del usuario actual
    $tipo_comprobante = $_POST['tipo_comprobante'] ?? 'boleta';

    // Intentamos encontrar el id_vendedor correspondiente al usuario
    try {
        $stmt_vendedor = $pdo->prepare("SELECT id_vendedor FROM vendedores WHERE id_usuario = :id_usuario");
        $stmt_vendedor->bindParam(':id_usuario', $id_usuario);
        $stmt_vendedor->execute();
        $vendedor_data = $stmt_vendedor->fetch(PDO::FETCH_ASSOC);

        if ($vendedor_data) {
            $id_vendedor = $vendedor_data['id_vendedor'];
        } else {
            // Si no hay un vendedor asociado, podríamos crear uno o usar un valor por defecto
            // Por ahora, usarémos el id_usuario como fallback, pero idealmente debería haber un proceso de registro
            $id_vendedor = $id_usuario; // Esto puede necesitar ajustes según el modelo de datos real
        }
    } catch (PDOException $e) {
        // En caso de error, usamos id_usuario como fallback
        $id_vendedor = $id_usuario;
    }

    if (empty($productos_venta)) {
        $_SESSION['mensaje_error'] = 'No hay productos en el carrito';
    } else {
        try {
            $pdo->beginTransaction();

            // Calcular subtotal
            $subtotal = 0;
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

                $subtotal_producto = $producto['precio'] * $cantidad;
                $subtotal += $subtotal_producto;

                $productos_para_insertar[] = [
                    'id_producto' => $id_producto,
                    'cantidad' => $cantidad,
                    'precio_unit' => $producto['precio'],
                    'subtotal' => $subtotal_producto
                ];
            }

            // Calcular subtotal
            $subtotal = 0;
            $productos_para_insertar = [];

            foreach ($productos_venta as $item) {
                $id_producto = $item['id'];
                $cantidad = $item['cantidad'];

                // Verificar disponibilidad de stock
                $stmt_stock = $pdo->prepare("SELECT p.stock, p.precio, pr.nombre as producto_nombre FROM productos p LEFT JOIN productos pr ON p.id_producto = pr.id_producto WHERE p.id_producto = :id_producto");
                $stmt_stock->bindParam(':id_producto', $id_producto);
                $stmt_stock->execute();
                $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);

                if (!$producto || $producto['stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente para " . $producto['producto_nombre'] ?? 'producto desconocido');
                }

                $subtotal_producto = $producto['precio'] * $cantidad;
                $subtotal += $subtotal_producto;

                $productos_para_insertar[] = [
                    'id_producto' => $id_producto,
                    'cantidad' => $cantidad,
                    'precio_unit' => $producto['precio'],
                    'subtotal' => $subtotal_producto
                ];
            }

            // Manejo de descuentos
            $descuento_aplicado = json_decode($_POST['descuento_aplicado'] ?? 'null', true);
            $descuento_monto = 0;

            if ($descuento_aplicado) {
                if ($descuento_aplicado['tipo'] === 'porcentaje') {
                    $descuento_monto = $subtotal * ($descuento_aplicado['valor'] / 100);
                } else { // monto fijo
                    $descuento_monto = min($descuento_aplicado['valor'], $subtotal); // No puede exceder el subtotal
                }

                // Aplicar descuento al subtotal
                $subtotal_despues_descuento = $subtotal - $descuento_monto;
            } else {
                $subtotal_despues_descuento = $subtotal;
            }

            // Calcular IGV y total (IGV se calcula sobre el subtotal después del descuento)
            $igv = $subtotal_despues_descuento * 0.18;
            $total = $subtotal_despues_descuento + $igv;

            // Validar si el cliente existe o es nulo para cliente público
            if ($id_cliente) {
                $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = :id_cliente");
                $stmt_cliente->bindParam(':id_cliente', $id_cliente);
                $stmt_cliente->execute();
                $cliente_existe = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

                if (!$cliente_existe) {
                    $id_cliente = null; // Set to null for public customer if client doesn't exist
                }
            }

            // Handle coupon usage if applicable
            $id_cupon_usado = $_POST['id_cupon_usado'] ?? null;
            if ($id_cupon_usado) {
                // Mark the coupon as used once
                try {
                    $stmt_cupon = $pdo->prepare("UPDATE cupones SET veces_usado = veces_usado + 1 WHERE id_cupon = :id_cupon");
                    $stmt_cupon->bindParam(':id_cupon', $id_cupon_usado);
                    $stmt_cupon->execute();
                } catch (PDOException $e) {
                    error_log("Error al actualizar uso de cupón: " . $e->getMessage());
                }
            }

            // Creamos la venta - primero intentaremos insertar con todas las columnas
            $stmt_venta = null;
            $exito_al_crear_ventas = false;

            try {
                // Primero intentamos asegurar que las columnas existan
                $stmt_check = $pdo->query("SHOW COLUMNS FROM ventas LIKE 'subtotal'");
                $col_subtotal = $stmt_check->fetch();

                $stmt_check = $pdo->query("SHOW COLUMNS FROM ventas LIKE 'igv'");
                $col_igv = $stmt_check->fetch();

                $stmt_check = $pdo->query("SHOW COLUMNS FROM ventas LIKE 'tipo_comprobante'");
                $col_tipo = $stmt_check->fetch();

                $stmt_check = $pdo->query("SHOW COLUMNS FROM ventas LIKE 'descuento'");
                $col_descuento = $stmt_check->fetch();

                // Si no existen, las creamos
                if (!$col_subtotal) {
                    $pdo->exec("ALTER TABLE ventas ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00 AFTER total");
                }
                if (!$col_igv) {
                    $pdo->exec("ALTER TABLE ventas ADD COLUMN igv DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal");
                }
                if (!$col_tipo) {
                    $pdo->exec("ALTER TABLE ventas ADD COLUMN tipo_comprobante VARCHAR(20) DEFAULT 'boleta' AFTER igv");
                }
                if (!$col_descuento) {
                    $pdo->exec("ALTER TABLE ventas ADD COLUMN descuento DECIMAL(10,2) DEFAULT 0.00 AFTER igv");
                }

                // Ahora creamos la venta con todas las columnas que deberían existir
                $stmt_venta = $pdo->prepare("
                    INSERT INTO ventas (id_cliente, id_vendedor, subtotal, descuento, igv, total, estado, tipo_comprobante)
                    VALUES (:id_cliente, :id_vendedor, :subtotal_original, :descuento, :igv, :total, 'Procesada', :tipo_comprobante)
                ");

                if ($id_cliente === null) {
                    $stmt_venta->bindValue(':id_cliente', null, PDO::PARAM_NULL);
                } else {
                    $stmt_venta->bindParam(':id_cliente', $id_cliente);
                }
                $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
                $stmt_venta->bindParam(':subtotal_original', $subtotal); // Original subtotal before discount
                $stmt_venta->bindParam(':descuento', $descuento_monto);
                $stmt_venta->bindParam(':igv', $igv);
                $stmt_venta->bindParam(':total', $total);
                $stmt_venta->bindParam(':tipo_comprobante', $tipo_comprobante);

                $exito_al_crear_ventas = true;

            } catch (PDOException $e) {
                // Si falla por columnas inexistentes, intentamos con columnas básicas
                $stmt_venta = $pdo->prepare("
                    INSERT INTO ventas (id_cliente, id_vendedor, total, estado, tipo_comprobante)
                    VALUES (:id_cliente, :id_vendedor, :total, 'Procesada', :tipo_comprobante)
                ");

                if ($id_cliente === null) {
                    $stmt_venta->bindValue(':id_cliente', null, PDO::PARAM_NULL);
                } else {
                    $stmt_venta->bindParam(':id_cliente', $id_cliente);
                }
                $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
                $stmt_venta->bindParam(':total', $total);
                $stmt_venta->bindParam(':tipo_comprobante', $tipo_comprobante);
            }

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

            $_SESSION['mensaje_exito'] = 'Venta procesada exitosamente. Subtotal: S/. ' . number_format($subtotal, 2) . ', IGV: S/. ' . number_format($igv, 2) . ', Total: S/. ' . number_format($total, 2);
            // Limpiar carrito
            echo '<script>localStorage.removeItem("carrito_venta");</script>';
        } catch(Exception $e) {
            // Rollback only if transaction is active
            try {
                $pdo->rollback();
            } catch (PDOException $rollbackException) {
                // Ignore rollback errors if no transaction is active
            }
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
                        <li><a href="descuentos.php">
                            <i class="fas fa-tags"></i> <span>Descuentos</span>
                        </a></li>
                        <li><a href="clientes.php">
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

                            <!-- Detalles de la venta con impuestos -->
                            <div class="detalles-venta">
                                <div class="venta-linea">
                                    <span>Subtotal:</span>
                                    <span id="subtotalCarrito">S/. 0.00</span>
                                </div>
                                <div class="venta-linea">
                                    <span>IGV (18%):</span>
                                    <span id="igvCarrito">S/. 0.00</span>
                                </div>
                                <div class="venta-linea total-final">
                                    <span>Total (con IGV):</span>
                                    <span id="totalCarrito">S/. 0.00</span>
                                </div>
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

                            <!-- Opciones de comprobante -->
                            <div style="margin-top: 1rem;">
                                <label for="tipo_comprobante">Tipo de Comprobante:</label>
                                <select name="tipo_comprobante" id="tipo_comprobante">
                                    <option value="boleta">Boleta</option>
                                    <option value="factura">Factura</option>
                                </select>
                            </div>

                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn-empleado btn-secondary-empleado" onclick="generarPrevisualizacion()" style="flex: 1; min-width: 120px;">
                                    <i class="fas fa-print"></i> Previsualizar
                                </button>
                                <button type="button" class="btn-empleado btn-primary-empleado" onclick="descargarComprobante()" style="flex: 1; min-width: 120px;">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                                <button type="submit" class="btn-empleado btn-primary-empleado" style="flex: 1; min-width: 120px;">
                                    <i class="fas fa-check-circle"></i> Finalizar Venta
                                </button>
                            </div>

                            <!-- Sección de descuentos -->
                            <div style="margin-top: 1.5rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--empleado-border);">
                                <h3 style="margin-bottom: 1rem; color: var(--empleado-primary);">Aplicar Descuento</h3>

                                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <label for="codigo_cupon" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Código de Cupón (opcional):</label>
                                        <input type="text" id="codigo_cupon" placeholder="Introduce código de cupón..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--empleado-border); border-radius: var(--radius-sm); font-size: 1rem;">
                                    </div>

                                    <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                                        <button type="button" class="btn-empleado btn-secondary-empleado" onclick="aplicarCupon()">
                                            <i class="fas fa-gift"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn-empleado btn-outline-empleado" onclick="quitarDescuento()">
                                            <i class="fas fa-times"></i> Quitar
                                        </button>
                                    </div>
                                </div>

                                <div id="infoDescuento" style="margin-top: 1rem; display: none;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #fef3c7; border-radius: var(--radius-sm); border: 1px solid var(--empleado-warning);">
                                        <span id="textoDescuento">Descuento aplicado: </span>
                                        <span style="font-weight: bold;" id="montoDescuento">-S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        let carrito = JSON.parse(localStorage.getItem('carrito_venta')) || [];

        // Make products data available to JavaScript
        const productos = <?php echo json_encode($productos); ?>;

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

        // Fix for the buttons not working properly - updated function
        function modificarCantidad(index, cambio) {
            if (cambio > 0) {
                // Aumentar cantidad - verificar stock disponible
                // First, let's try to get the max stock from available sources
                let stockMaximo = carrito[index].stock_maximo;

                // If stock_maximo is not available in the cart item, try to get it from the products list
                if (typeof stockMaximo === 'undefined' || stockMaximo === null) {
                    // Look for the product in the global products list
                    const productoOriginal = typeof productos !== 'undefined' ? productos.find(p => p.id_producto == carrito[index].id) : null;
                    if (productoOriginal) {
                        stockMaximo = productoOriginal.stock;
                    } else {
                        // If we don't have stock information, just allow the increase
                        // The validation will happen when the purchase is finalized
                        stockMaximo = Number.MAX_SAFE_INTEGER;
                    }
                }

                if (carrito[index].cantidad < stockMaximo) {
                    carrito[index].cantidad++;
                } else {
                    alert('No hay suficiente stock disponible para este producto (máx: ' + stockMaximo + ')');
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

        // Cargar carrito desde localStorage y calcular totales
        function cargarCarrito() {
            const carritoDiv = document.getElementById('carritoItems');
            carritoDiv.innerHTML = '';

            if (carrito.length === 0) {
                carritoDiv.innerHTML = '<p>No hay productos en el carrito</p>';
                document.getElementById('subtotalCarrito').textContent = 'S/. 0.00';
                document.getElementById('igvCarrito').textContent = 'S/. 0.00';
                document.getElementById('totalCarrito').textContent = 'S/. 0.00';
                return;
            }

            let subtotal = 0;
            carrito.forEach((item, index) => {
                const subtotalItem = item.precio * item.cantidad;
                subtotal += subtotalItem;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'item-carrito';
                itemDiv.innerHTML = `
                    <div>
                        <strong>${item.nombre}</strong><br>
                        Cantidad: ${item.cantidad} x S/. ${item.precio.toFixed(2)} = S/. ${subtotalItem.toFixed(2)}
                    </div>
                    <div class="acciones-carrito">
                        <button type="button" class="btn-carrito btn-add" onclick="modificarCantidad(${index}, 1)">+</button>
                        <button type="button" class="btn-carrito btn-remove" onclick="quitarDelCarrito(${index})">-</button>
                    </div>
                `;
                carritoDiv.appendChild(itemDiv);
            });

            // Calcular IGV y total
            const igv = subtotal * 0.18;
            const total = subtotal + igv;

            // Actualizar valores en pantalla
            document.getElementById('subtotalCarrito').textContent = 'S/. ' + subtotal.toFixed(2);
            document.getElementById('igvCarrito').textContent = 'S/. ' + igv.toFixed(2);
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

        // Fix for the buttons not working in Firefox/IE
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

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function generarPrevisualizacion() {
            if (carrito.length === 0) {
                alert('No hay productos en el carrito para previsualizar');
                return;
            }

            // Calcular totales
            let subtotal = 0;
            carrito.forEach(item => {
                subtotal += item.precio * item.cantidad;
            });

            const igv = subtotal * 0.18;
            const total = subtotal + igv;

            // Obtener tipo de comprobante y cliente
            const tipoComprobante = document.getElementById('tipo_comprobante').value;
            const idCliente = document.getElementById('id_cliente').value;
            const clienteNombre = idCliente ? document.querySelector(`#id_cliente option[value="${idCliente}"]`).text : 'Cliente Público';

            // Crear ventana de previsualización
            const ventana = window.open('', '_blank', 'height=600,width=400');
            ventana.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Previsualización ${tipoComprobante === 'factura' ? 'Factura' : 'Boleta'}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .comprobante { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
                        .encabezado { text-align: center; margin-bottom: 20px; }
                        .cliente-info { margin: 10px 0; }
                        .items { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        .items th, .items td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        .items th { background-color: #f2f2f2; }
                        .totales { margin-top: 15px; text-align: right; }
                        .totales div { margin: 5px 0; }
                        .total-final { font-weight: bold; font-size: 1.1em; }
                    </style>
                </head>
                <body>
                    <div class="comprobante">
                        <div class="encabezado">
                            <h2>HELADERÍA CONCELAITO</h2>
                            <p>RUC: 12345678901</p>
                            <h3>${tipoComprobante === 'factura' ? 'FACTURA' : 'BOLETA'} ELECTRÓNICA</h3>
                            <p>N° 001-0000001</p>
                        </div>

                        <div class="cliente-info">
                            <p><strong>Cliente:</strong> ${clienteNombre}</p>
                            <p><strong>Fecha:</strong> ${new Date().toLocaleDateString()}</p>
                            <p><strong>Hora:</strong> ${new Date().toLocaleTimeString()}</p>
                        </div>

                        <table class="items">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${carrito.map(item => `
                                    <tr>
                                        <td>${item.nombre}</td>
                                        <td>${item.cantidad}</td>
                                        <td>S/. ${item.precio.toFixed(2)}</td>
                                        <td>S/. ${(item.precio * item.cantidad).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>

                        <div class="totales">
                            <div>Subtotal: S/. ${subtotal.toFixed(2)}</div>
                            <div>IGV (18%): S/. ${igv.toFixed(2)}</div>
                            <div class="total-final">TOTAL: S/. ${total.toFixed(2)}</div>
                        </div>

                        <div style="margin-top: 20px; text-align: center; font-size: 0.9em;">
                            <hr>
                            <p>Gracias por su compra</p>
                        </div>
                    </div>
                </body>
                </html>
            `);
            ventana.document.close();
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

                // Añadir información de descuento si existe
                if (window.descuentoAplicado) {
                    const descuentoInput = document.createElement('input');
                    descuentoInput.type = 'hidden';
                    descuentoInput.name = 'descuento_aplicado';
                    descuentoInput.value = JSON.stringify(window.descuentoAplicado);
                    this.appendChild(descuentoInput);

                    // Also add the coupon ID if it's a coupon
                    if (window.descuentoAplicado.id_cupon) {
                        const idCuponInput = document.createElement('input');
                        idCuponInput.type = 'hidden';
                        idCuponInput.name = 'id_cupon_usado';
                        idCuponInput.value = window.descuentoAplicado.id_cupon;
                        this.appendChild(idCuponInput);
                    }
                }

                // También añadir tipo_comprobante al form si no existe
                const tipoInput = this.querySelector('input[name="tipo_comprobante"]');
                if (!tipoInput) {
                    const tipoComprobante = document.createElement('input');
                    tipoComprobante.type = 'hidden';
                    tipoComprobante.name = 'tipo_comprobante';
                    tipoComprobante.value = document.getElementById('tipo_comprobante').value;
                    this.appendChild(tipoComprobante);
                }
            });
        });

        // Download invoice/receipt function
        function descargarComprobante() {
            if (carrito.length === 0) {
                alert('No hay productos en el carrito para generar comprobante');
                return;
            }

            // Calcular totales
            let subtotal = 0;
            carrito.forEach(item => {
                subtotal += item.precio * item.cantidad;
            });

            const igv = subtotal * 0.18;
            const total = subtotal + igv;

            // Obtener tipo de comprobante y cliente
            const tipoComprobante = document.getElementById('tipo_comprobante').value;
            const idCliente = document.getElementById('id_cliente').value;
            const clienteNombre = idCliente ? document.querySelector(`#id_cliente option[value="${idCliente}"]`).text : 'Cliente Público';

            // Crear contenido HTML del comprobante
            const comprobanteHtml = `
<!DOCTYPE html>
<html>
<head>
    <title>${tipoComprobante === 'factura' ? 'Factura' : 'Boleta'} - Heladería Concelato</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; width: 21cm; height: 29.7cm; }
        .comprobante { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .encabezado { text-align: center; margin-bottom: 20px; }
        .cliente-info { margin: 10px 0; }
        .items { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items th, .items td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items th { background-color: #f2f2f2; }
        .totales { margin-top: 15px; text-align: right; }
        .totales div { margin: 5px 0; }
        .total-final { font-weight: bold; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="comprobante">
        <div class="encabezado">
            <h2>HELADERÍA CONCELAITO</h2>
            <p>RUC: 12345678901</p>
            <h3>${tipoComprobante === 'factura' ? 'FACTURA' : 'BOLETA'} ELECTRÓNICA</h3>
            <p>N° 001-0000001</p>
        </div>

        <div class="cliente-info">
            <p><strong>Cliente:</strong> ${clienteNombre}</p>
            <p><strong>Fecha:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Hora:</strong> ${new Date().toLocaleTimeString()}</p>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${carrito.map(item => `
                    <tr>
                        <td>${item.nombre}</td>
                        <td>${item.cantidad}</td>
                        <td>S/. ${item.precio.toFixed(2)}</td>
                        <td>S/. ${(item.precio * item.cantidad).toFixed(2)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>

        <div class="totales">
            <div>Subtotal: S/. ${subtotal.toFixed(2)}</div>
            <div>IGV (18%): S/. ${igv.toFixed(2)}</div>
            <div class="total-final">TOTAL: S/. ${total.toFixed(2)}</div>
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 0.9em;">
            <hr>
            <p>Gracias por su compra</p>
        </div>
    </div>
</body>
</html>`;

            // Crear un blob y descargar como archivo
            const blob = new Blob([comprobanteHtml], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${tipoComprobante}_${Date.now()}.html`;
            document.body.appendChild(a);
            a.click();

            // Limpiar
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 100);
        }

        // Function to validate coupon code
        async function aplicarCupon() {
            const codigoCupon = document.getElementById('codigo_cupon').value.trim();

            if (!codigoCupon) {
                alert('Por favor ingrese un código de cupón');
                return;
            }

            try {
                const response = await fetch('../../conexion/validar_cupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'codigo=' + encodeURIComponent(codigoCupon)
                });

                const result = await response.json();

                if (result.success) {
                    // Apply discount to the cart
                    aplicarDescuento(result.descuento);
                    document.getElementById('codigo_cupon').value = '';
                } else {
                    alert('Cupón inválido: ' + result.message);
                }
            } catch (error) {
                alert('Error al validar el cupón: ' + error.message);
            }
        }

        // Apply discount to cart
        function aplicarDescuento(descuentoData) {
            if (!carrito || carrito.length === 0) {
                alert('No hay productos en el carrito para aplicar descuento');
                return;
            }

            // Store discount information
            window.descuentoAplicado = descuentoData;

            // Show discount info
            const infoDiv = document.getElementById('infoDescuento');
            const textoDescuento = document.getElementById('textoDescuento');
            const montoDescuento = document.getElementById('montoDescuento');

            // Calculate subtotal
            let subtotal = 0;
            carrito.forEach(item => {
                subtotal += item.precio * item.cantidad;
            });

            // Calculate discount amount based on type
            let montoDescuentoValue = 0;
            if (descuentoData.tipo === 'porcentaje') {
                montoDescuentoValue = subtotal * (descuentoData.valor / 100);
            } else {
                // For fixed amount, use the minimum between discount value and subtotal
                montoDescuentoValue = Math.min(descuentoData.valor, subtotal);
            }

            // Update UI
            textoDescuento.textContent = `Descuento aplicado (${descuentoData.nombre}): ${descuentoData.tipo === 'porcentaje' ? descuentoData.valor + '%' : 'S/. ' + descuentoData.valor}`;
            montoDescuento.textContent = '-S/. ' + montoDescuentoValue.toFixed(2);
            infoDiv.style.display = 'block';

            // Reload cart to update totals with discount
            cargarCarrito();
        }

        // Remove discount
        function quitarDescuento() {
            window.descuentoAplicado = null;
            document.getElementById('infoDescuento').style.display = 'none';
            cargarCarrito();
        }

        // Recalculate totals including discount
        function calcularTotalesConDescuento() {
            let subtotal = 0;
            carrito.forEach(item => {
                subtotal += item.precio * item.cantidad;
            });

            let descuento = 0;
            if (window.descuentoAplicado) {
                if (window.descuentoAplicado.tipo === 'porcentaje') {
                    descuento = subtotal * (window.descuentoAplicado.valor / 100);
                } else {
                    descuento = Math.min(window.descuentoAplicado.valor, subtotal);
                }
            }

            const subtotalConDescuento = subtotal - descuento;
            const igv = subtotalConDescuento * 0.18; // IGV is calculated on the discounted amount
            const total = subtotalConDescuento + igv;

            return {
                subtotal: subtotal,
                descuento: descuento,
                subtotalConDescuento: subtotalConDescuento,
                igv: igv,
                total: total
            };
        }

        // Update cargarCarrito function to include discount calculation
        function cargarCarrito() {
            const carritoDiv = document.getElementById('carritoItems');
            carritoDiv.innerHTML = '';

            if (carrito.length === 0) {
                carritoDiv.innerHTML = '<p>No hay productos en el carrito</p>';
                document.getElementById('subtotalCarrito').textContent = 'S/. 0.00';
                document.getElementById('igvCarrito').textContent = 'S/. 0.00';
                document.getElementById('totalCarrito').textContent = 'S/. 0.00';
                return;
            }

            let subtotal = 0;
            carrito.forEach((item, index) => {
                const subtotalItem = item.precio * item.cantidad;
                subtotal += subtotalItem;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'item-carrito';
                itemDiv.innerHTML = `
                    <div class="item-info">
                        <strong>${item.nombre}</strong><br>
                        Cantidad:
                        <button type="button" class="btn-cantidad" onclick="modificarCantidad(${index}, -1)">-</button>
                        ${item.cantidad}
                        <button type="button" class="btn-cantidad" onclick="modificarCantidad(${index}, 1)">+</button>
                        <br>
                        Precio unit.: S/. ${item.precio.toFixed(2)}<br>
                        Subtotal: S/. ${subtotalItem.toFixed(2)}
                    </div>
                    <button type="button" class="btn-eliminar" onclick="quitarDelCarrito(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                carritoDiv.appendChild(itemDiv);
            });

            // Calculate totals with discount
            const totales = calcularTotalesConDescuento();

            // Update displayed totals
            if (window.descuentoAplicado) {
                document.getElementById('subtotalCarrito').innerHTML = `S/. ${totales.subtotal.toFixed(2)} <small style="color: #ef4444;">(-S/. ${totales.descuento.toFixed(2)})</small>`;
            } else {
                document.getElementById('subtotalCarrito').textContent = 'S/. ' + totales.subtotal.toFixed(2);
            }

            document.getElementById('igvCarrito').textContent = 'S/. ' + totales.igv.toFixed(2);
            document.getElementById('totalCarrito').textContent = 'S/. ' + totales.total.toFixed(2);
        }

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>

    <style>
        .detalles-venta {
            border-top: 1px solid var(--empleado-border);
            margin-top: 1.5rem;
            padding: 1rem 0;
        }

        .venta-linea {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            font-size: 0.95rem;
        }

        .venta-linea.total-final {
            font-weight: bold;
            font-size: 1.1rem;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            border-top: 1px solid var(--empleado-border);
        }

        /* Mejora de estilos para modales */
        .modal-stock, .modal-generic {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal-content-stock, .modal-generic-content {
            background-color: var(--empleado-card-bg);
            margin: 15% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 400px;
            max-width: 90%;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--empleado-border);
            animation: slideIn 0.3s ease;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--empleado-border);
        }

        .modal-title {
            margin: 0;
            color: var(--empleado-primary);
            font-size: 1.5rem;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--empleado-text-light);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .modal-close-btn:hover {
            background: var(--empleado-border);
            color: var(--empleado-danger);
        }

        .modal-body {
            margin-bottom: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--empleado-border);
        }

        @media (max-width: 480px) {
            .modal-content-stock, .modal-generic-content {
                margin: 20% auto;
                width: 95%;
                padding: 1.5rem;
            }
        }

        /* Improved Cart Styling */
        .carrito-venta {
            background: var(--empleado-card-bg);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--empleado-border);
            margin-top: 1.5rem;
        }

        .item-carrito {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--empleado-border);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
            background: #f8fafc;
            transition: var(--transition);
        }

        .item-carrito:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            background: #f1f5f9;
        }

        .item-info {
            flex: 1;
        }

        .btn-cantidad {
            background: var(--empleado-primary);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            transition: var(--transition);
        }

        .btn-cantidad:hover {
            background: var(--empleado-primary-dark);
            transform: scale(1.1);
        }

        .btn-eliminar {
            background: var(--empleado-danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-eliminar:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .detalles-venta {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f0f9ff;
            border-radius: var(--radius-md);
            border: 1px solid var(--empleado-border);
        }

        .venta-linea {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-size: 1rem;
        }

        .venta-linea.total-final {
            font-weight: bold;
            font-size: 1.2rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 2px solid var(--empleado-primary);
        }

        .productos-disponibles {
            background: var(--empleado-card-bg);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--empleado-border);
            max-height: 400px;
            overflow-y: auto;
        }

        #productosList {
            max-height: 300px;
            overflow-y: auto;
        }

        .producto-item {
            padding: 1rem;
            border: 1px solid var(--empleado-border);
            border-radius: var(--radius-sm);
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            background: #f9fafb;
        }

        .producto-item:hover {
            background: #f0f9ff;
            border-color: var(--empleado-primary);
            transform: translateX(5px);
        }

        .producto-item.selected {
            background: #bae6fd;
            border-color: var(--empleado-primary);
            animation: highlight 0.5s ease;
        }

        @keyframes highlight {
            0% { background: #bae6fd; }
            100% { background: #f0f9ff; }
        }

        /* Improved Navbar - Always show all options */
        .empleado-nav ul {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.5rem !important;
        }

        .empleado-nav li {
            width: auto !important;
        }

        .empleado-nav a,
        .empleado-nav li a {
            width: auto !important;
            padding: 0.8rem 1.2rem !important;
            border-radius: 8px !important;
            white-space: nowrap !important;
            justify-content: center !important;
        }

        .empleado-nav i {
            margin-right: 0.5rem !important;
        }

        .empleado-nav span {
            display: inline !important;
        }

        .menu-toggle-empleado {
            display: none !important;
        }

        /* Tablet styling */
        @media (max-width: 1024px) {
            .empleado-nav ul {
                flex-wrap: wrap;
                gap: 0.2rem;
            }

            .empleado-nav a,
            .empleado-nav li a {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
            }

            .venta-section {
                grid-template-columns: 1fr;
            }
        }

        /* Mobile styling for nav */
        @media (max-width: 768px) {
            .menu-toggle-empleado {
                display: flex !important;
            }

            .empleado-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(229, 231, 235, 0.8);
                padding: 1rem 0;
                flex-direction: column;
                width: 100%;
                z-index: 999;
                box-shadow: var(--shadow-lg);
            }

            .empleado-nav ul {
                flex-direction: column;
                gap: 0.25rem;
                width: 100%;
            }

            .empleado-nav li {
                width: 100%;
            }

            .empleado-nav a,
            .empleado-nav li a {
                width: 100% !important;
                padding: 1rem 1.5rem !important;
                border-radius: 0 !important;
                justify-content: flex-start !important;
                border-left: 4px solid transparent;
            }

            .empleado-nav a:hover,
            .empleado-nav li a:hover {
                background: rgba(37, 99, 235, 0.1);
                border-left-color: var(--empleado-primary);
            }

            .logout-btn-empleado {
                width: 100%;
                justify-content: flex-start;
                border-radius: 0;
                padding: 1rem 1.5rem;
                margin-top: 1rem;
                border-top: 1px solid var(--empleado-border);
            }
        }

        /* Improved Sales Page Styles */
        .venta-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .productos-disponibles, .carrito-venta {
            background: var(--empleado-card-bg);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--empleado-border);
            height: fit-content;
        }

        .productos-disponibles h3, .carrito-venta h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: var(--empleado-primary);
            font-size: 1.3rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .productos-disponibles h3::after, .carrito-venta h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--empleado-gradient);
            border-radius: 2px;
        }

        .producto-item {
            padding: 1rem;
            border: 2px solid var(--empleado-border);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            background: #f9fafb;
            position: relative;
            overflow: hidden;
        }

        .producto-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .producto-item:hover {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-color: var(--empleado-primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .producto-item:hover::before {
            left: 100%;
        }

        .producto-item.selected {
            background: linear-gradient(135deg, #bae6fd, #a5f3fc);
            border-color: var(--empleado-primary-dark);
            animation: pulse 0.5s ease;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 235, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 235, 0); }
        }

        .item-carrito {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--empleado-border);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
            background: #f8fafc;
            transition: var(--transition);
        }

        .item-carrito:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            background: #f1f5f9;
        }

        .item-info {
            flex: 1;
        }

        .btn-cantidad {
            background: var(--empleado-primary);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.25rem;
        }

        .btn-cantidad:hover {
            background: var(--empleado-primary-dark);
            transform: scale(1.2);
        }

        .btn-eliminar {
            background: var(--empleado-danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-eliminar:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .detalles-venta {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: var(--radius-md);
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .venta-linea {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-size: 1rem;
            border-bottom: 1px dashed rgba(225, 225, 235, 0.8);
        }

        .venta-linea:last-child {
            border-bottom: none;
        }

        .venta-linea.total-final {
            font-weight: bold;
            font-size: 1.2rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 2px solid var(--empleado-primary);
            border-bottom: none;
        }

        select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--empleado-border);
            border-radius: var(--radius-sm);
            background: var(--empleado-card-bg);
            color: var(--empleado-text);
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }

        select:focus {
            border-color: var(--empleado-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-empleado {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            min-height: 44px;
            justify-content: center;
        }

        .btn-primary-empleado {
            background: var(--empleado-gradient);
            color: white;
        }

        .btn-primary-empleado:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary-empleado {
            background: var(--empleado-secondary);
            color: white;
        }

        .btn-secondary-empleado:hover {
            background: #7c3aed;
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-empleado {
            background: transparent;
            border: 2px solid var(--empleado-primary);
            color: var(--empleado-primary);
        }

        .btn-outline-empleado:hover {
            background: var(--empleado-primary);
            color: white;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--empleado-success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--empleado-danger);
        }

        .alert i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments for sales page */
        @media (max-width: 768px) {
            .venta-section {
                grid-template-columns: 1fr;
            }

            .productos-disponibles, .carrito-venta {
                padding: 1rem;
            }

            .item-carrito {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .venta-linea {
                flex-direction: column;
                gap: 0.25rem;
            }

            .venta-linea.total-final {
                text-align: center;
            }
        }
    </style>
</body>
</html>