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

// Obtener inventario de la sucursal
$stmt_inventario = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, i.stock_sucursal as stock
    FROM productos p
    JOIN inventario_sucursal i ON p.id_producto = i.id_producto
    WHERE i.id_sucursal = :id_sucursal
    ORDER BY p.nombre
");
$stmt_inventario->bindParam(':id_sucursal', $id_sucursal);
$stmt_inventario->execute();
$inventario = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);

// Si se está actualizando el stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_stock') {
    $id_producto = $_POST['id_producto'];
    $nueva_cantidad = $_POST['nueva_cantidad'];
    
    if (actualizarStockSucursal($id_producto, $id_sucursal, $nueva_cantidad)) {
        $mensaje = "Stock actualizado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el stock";
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?></title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .inventario-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .inventario-table th, .inventario-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .inventario-table th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        
        .stock-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .stock-alto {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .stock-medio {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .stock-bajo {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .stock-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?> - Inventario
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="ventas.php"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
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
                <h1>Control de Inventario</h1>
                <p>Administración del inventario en <?php echo htmlspecialchars(obtenerSucursalPorId($id_sucursal)['nombre']); ?></p>
            </div>

            <?php if (isset($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="inventario-actions">
                <div class="search-filter">
                    <input type="text" id="searchInventario" placeholder="Buscar producto..." onkeyup="searchInventario()">
                    <select id="filterStock" onchange="filterInventario()">
                        <option value="">Todos los productos</option>
                        <option value="bajo">Stock bajo (< 10)</option>
                        <option value="medio">Stock medio (10-20)</option>
                        <option value="alto">Stock alto (> 20)</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Sabor</th>
                            <th>Precio</th>
                            <th>Stock Actual</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="inventarioTable">
                        <?php foreach ($inventario as $producto): ?>
                        <tr data-stock="<?php echo $producto['stock']; ?>">
                            <td><?php echo $producto['id_producto']; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                            <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                            <td><?php echo $producto['stock']; ?></td>
                            <td>
                                <span class="stock-status
                                    <?php 
                                    if ($producto['stock'] < 10) echo 'stock-bajo';
                                    elseif ($producto['stock'] < 20) echo 'stock-medio';
                                    else echo 'stock-alto';
                                    ?>">
                                    <?php 
                                    if ($producto['stock'] < 10) echo 'Bajo';
                                    elseif ($producto['stock'] < 20) echo 'Medio';
                                    else echo 'Alto';
                                    ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Actualizar stock de este producto?');">
                                    <input type="hidden" name="accion" value="actualizar_stock">
                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                    <input type="number" name="nueva_cantidad" class="stock-input" placeholder="Cantidad" min="0" required>
                                    <button type="submit" class="action-btn update">Actualizar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function searchInventario() {
            const input = document.getElementById('searchInventario');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#inventarioTable tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const nombreCell = row.cells[1].textContent.toLowerCase(); // Nombre
                const saborCell = row.cells[2].textContent.toLowerCase(); // Sabor

                if (nombreCell.includes(filter) || saborCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function filterInventario() {
            const filter = document.getElementById('filterStock').value;
            const rows = document.querySelectorAll('#inventarioTable tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const stock = parseInt(row.getAttribute('data-stock'));

                let showRow = false;
                if (filter === '') {
                    showRow = true;
                } else if (filter === 'bajo' && stock < 10) {
                    showRow = true;
                } else if (filter === 'medio' && stock >= 10 && stock <= 20) {
                    showRow = true;
                } else if (filter === 'alto' && stock > 20) {
                    showRow = true;
                }

                row.style.display = showRow ? '' : 'none';
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