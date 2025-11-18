<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener productos con estado de stock
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.precio, p.stock, p.descripcion, p.activo,
           CASE 
               WHEN p.stock > 30 THEN 'Disponible'
               WHEN p.stock BETWEEN 16 AND 30 THEN 'Medio'
               ELSE 'Bajo'
           END AS estado_stock
    FROM productos p
    ORDER BY p.nombre
");
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Concelato Gelateria</title>
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
                    Concelato Gelateria - Inventario
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
            <div class="inventory-section">
                <h1>Control de Inventario</h1>
                
                <div class="inventory-filters">
                    <select id="filterStock" onchange="filterProducts()">
                        <option value="">Todos los productos</option>
                        <option value="Disponible">Disponibles</option>
                        <option value="Medio">Stock Medio</option>
                        <option value="Bajo">Stock Bajo</option>
                    </select>
                    <input type="text" id="searchProduct" placeholder="Buscar producto..." onkeyup="searchProducts()">
                </div>

                <div class="inventory-stats">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo count($productos); ?></h3>
                            <p>Total Productos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($productos, function($p) { return $p['estado_stock'] === 'Bajo'; })); ?></h3>
                            <p>Productos Bajos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($productos, function($p) { return $p['estado_stock'] === 'Medio'; })); ?></h3>
                            <p>Productos Medios</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($productos, function($p) { return $p['estado_stock'] === 'Disponible'; })); ?></h3>
                            <p>Productos Disponibles</p>
                        </div>
                    </div>
                </div>

                <div class="inventory-alerts">
                    <h2>Alertas de Inventario</h2>
                    <div class="alerts-list">
                        <?php foreach ($productos as $producto): ?>
                            <?php if ($producto['estado_stock'] === 'Bajo'): ?>
                                <div class="alert-item">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span><?php echo htmlspecialchars($producto['nombre']); ?>: <?php echo $producto['stock']; ?>L (Bajo stock)</span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="inventory-table">
                    <h2>Productos</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Stock Actual</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productsTable">
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo $producto['stock']; ?>L</td>
                                <td>
                                    <span class="status-badge 
                                        <?php echo $producto['estado_stock'] === 'Disponible' ? 'available' : ($producto['estado_stock'] === 'Medio' ? 'medium' : 'low'); ?>">
                                        <?php echo htmlspecialchars($producto['estado_stock']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="updateStock(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>', <?php echo $producto['stock']; ?>)">
                                        <i class="fas fa-edit"></i> Actualizar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para actualizar stock -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Actualizar Stock</h2>
            <form id="stockForm">
                <div class="form-group">
                    <label for="productName">Producto:</label>
                    <input type="text" id="productName" readonly>
                </div>
                <div class="form-group">
                    <label for="currentStock">Stock Actual:</label>
                    <input type="number" id="currentStock" readonly>
                </div>
                <div class="form-group">
                    <label for="newStock">Nuevo Stock:</label>
                    <input type="number" id="newStock" min="0" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedProductId = null;

        function updateStock(id, name, currentStock) {
            selectedProductId = id;
            document.getElementById('productName').value = name;
            document.getElementById('currentStock').value = currentStock;
            document.getElementById('newStock').value = currentStock;
            document.getElementById('stockModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('stockModal').style.display = 'none';
            selectedProductId = null;
        }

        document.getElementById('stockForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newStock = document.getElementById('newStock').value;
            
            if (selectedProductId && newStock >= 0) {
                // Enviar la actualización al servidor
                fetch('actualizar_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_producto: selectedProductId,
                        nuevo_stock: parseInt(newStock)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Stock actualizado exitosamente');
                        location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error al actualizar stock: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al actualizar stock');
                });
            }
        });

        function filterProducts() {
            const filter = document.getElementById('filterStock').value;
            const rows = document.querySelectorAll('#productsTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const statusCell = row.cells[4].textContent.trim();
                
                if (filter === '' || statusCell === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function searchProducts() {
            const input = document.getElementById('searchProduct');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const productCell = row.cells[0].textContent.toLowerCase();
                
                if (productCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        // Cerrar modal si se hace clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('stockModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>