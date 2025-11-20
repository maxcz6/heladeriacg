<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('empleado');

// Obtener productos con estado de stock
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               pr.empresa as proveedor_nombre,
               CASE 
                   WHEN p.stock < 10 THEN 'bajo'
                   WHEN p.stock <= 30 THEN 'medio'
                   ELSE 'normal'
               END AS estado_stock
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        WHERE p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
    error_log("Error al obtener productos para inventario: " . $e->getMessage());
}

// Procesar actualización de stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_stock') {
    $id_producto = $_POST['id_producto'];
    $nuevo_stock = $_POST['nuevo_stock'];

    try {
        $stmt_update = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id_producto");
        $stmt_update->bindParam(':stock', $nuevo_stock);
        $stmt_update->bindParam(':id_producto', $id_producto);

        if ($stmt_update->execute()) {
            $_SESSION['mensaje_exito'] = 'Stock actualizado exitosamente';
        } else {
            $_SESSION['mensaje_error'] = 'Error al actualizar stock';
        }
    } catch(PDOException $e) {
        $_SESSION['mensaje_error'] = 'Error al actualizar stock: ' . $e->getMessage();
        error_log("Error al actualizar stock: " . $e->getMessage());
    }
    
    // Recargar productos
    $stmt = $pdo->prepare("
        SELECT p.*, 
               pr.empresa as proveedor_nombre,
               CASE 
                   WHEN p.stock < 10 THEN 'bajo'
                   WHEN p.stock <= 30 THEN 'medio'
                   ELSE 'normal'
               END AS estado_stock
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        WHERE p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado - Inventario</title>
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
                        <li><a href="ventas.php">
                            <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                        </a></li>
                        <li><a href="inventario.php" class="active">
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
                <h1>Control de Inventario</h1>
                <p>Administra los productos y niveles de stock</p>
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

            <div class="card-empleado">
                <h2>Productos en Inventario</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sabor</th>
                                <th>Precio</th>
                                <th>Stock Actual</th>
                                <th>Proveedor</th>
                                <th>Estado Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id_producto']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?>L</td>
                                    <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="stock-badge stock-<?php echo $producto['estado_stock']; ?>">
                                            <?php echo $producto['estado_stock'] === 'bajo' ? 'Bajo' : ($producto['estado_stock'] === 'medio' ? 'Medio' : 'Normal'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-empleado btn-primary-empleado" onclick="abrirModalStock(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['stock']; ?>)">
                                            <i class="fas fa-boxes"></i> Actualizar Stock
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No hay productos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal para actualizar stock -->
        <div id="modalStock" class="modal-stock">
            <div class="modal-content-stock">
                <h3>Actualizar Stock</h3>
                <form method="POST">
                    <input type="hidden" name="accion" value="actualizar_stock">
                    <input type="hidden" name="id_producto" id="id_producto_modal">

                    <div class="form-group-stock">
                        <label for="producto_nombre">Producto</label>
                        <input type="text" id="producto_nombre" readonly>
                    </div>

                    <div class="form-group-stock">
                        <label for="stock_actual">Stock Actual</label>
                        <input type="number" id="stock_actual" readonly>
                    </div>

                    <div class="form-group-stock">
                        <label for="nuevo_stock">Nuevo Stock (Litros)</label>
                        <input type="number" id="nuevo_stock" name="nuevo_stock" min="0" required>
                    </div>

                    <div style="margin-top: 1.5rem; text-align: right;">
                        <button type="button" class="btn-empleado btn-secondary-empleado" onclick="cerrarModalStock()">Cancelar</button>
                        <button type="submit" class="btn-empleado btn-primary-empleado">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function abrirModalStock(id, nombre, stock) {
            document.getElementById('id_producto_modal').value = id;
            document.getElementById('producto_nombre').value = nombre;
            document.getElementById('stock_actual').value = stock;
            document.getElementById('nuevo_stock').value = stock;
            document.getElementById('modalStock').style.display = 'block';
        }

        function cerrarModalStock() {
            document.getElementById('modalStock').style.display = 'none';
        }

        // Cerrar modal si se hace clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalStock');
            if (event.target === modal) {
                cerrarModalStock();
            }
        }

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>