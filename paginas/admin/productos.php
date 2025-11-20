<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo producto
                $nombre = trim($_POST['nombre']);
                $sabor = trim($_POST['sabor']);
                $descripcion = trim($_POST['descripcion']);
                $precio = $_POST['precio'];
                $stock = $_POST['stock'];
                $id_proveedor = $_POST['id_proveedor'];
                
                if (empty($nombre) || empty($sabor) || empty($precio) || empty($stock)) {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor, activo) VALUES (:nombre, :sabor, :descripcion, :precio, :stock, :id_proveedor, 1)");
                        $stmt->bindParam(':nombre', $nombre);
                        $stmt->bindParam(':sabor', $sabor);
                        $stmt->bindParam(':descripcion', $descripcion);
                        $stmt->bindParam(':precio', $precio);
                        $stmt->bindParam(':stock', $stock);
                        $stmt->bindParam(':id_proveedor', $id_proveedor);
                        
                        if ($stmt->execute()) {
                            $mensaje = 'Producto creado exitosamente';
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al crear producto';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
                break;
                
            case 'editar':
                // Editar producto existente
                $id_producto = $_POST['id_producto'];

                // Verificar si solo se está actualizando stock
                if (isset($_POST['solo_stock'])) {
                    $stock = $_POST['stock'];

                    try {
                        $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id_producto");
                        $stmt->bindParam(':stock', $stock);
                        $stmt->bindParam(':id_producto', $id_producto);

                        if ($stmt->execute()) {
                            $mensaje = 'Stock actualizado exitosamente';
                            $tipo_mensaje = 'success';

                            // Registrar en auditoría
                            registrarAuditoria('productos', 'UPDATE', $id_producto, "Stock actualizado de " . $_POST['stock_anterior'] . " a " . $stock);
                        } else {
                            $mensaje = 'Error al actualizar stock';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $nombre = trim($_POST['nombre']);
                    $sabor = trim($_POST['sabor']);
                    $descripcion = trim($_POST['descripcion']);
                    $precio = $_POST['precio'];
                    $stock = $_POST['stock'];
                    $id_proveedor = $_POST['id_proveedor'];
                    $activo = isset($_POST['activo']) ? 1 : 0;

                    try {
                        $stmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, sabor = :sabor, descripcion = :descripcion, precio = :precio, stock = :stock, id_proveedor = :id_proveedor, activo = :activo WHERE id_producto = :id_producto");
                        $stmt->bindParam(':nombre', $nombre);
                        $stmt->bindParam(':sabor', $sabor);
                        $stmt->bindParam(':descripcion', $descripcion);
                        $stmt->bindParam(':precio', $precio);
                        $stmt->bindParam(':stock', $stock);
                        $stmt->bindParam(':id_proveedor', $id_proveedor);
                        $stmt->bindParam(':activo', $activo);
                        $stmt->bindParam(':id_producto', $id_producto);

                        if ($stmt->execute()) {
                            $mensaje = 'Producto actualizado exitosamente';
                            $tipo_mensaje = 'success';

                            // Registrar en auditoría
                            registrarAuditoria('productos', 'UPDATE', $id_producto, "Producto actualizado: " . $nombre);
                        } else {
                            $mensaje = 'Error al actualizar producto';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
                break;
                
            case 'eliminar':
                // Eliminar (desactivar) producto
                $id_producto = $_POST['id_producto'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = :id_producto");
                    $stmt->bindParam(':id_producto', $id_producto);
                    
                    if ($stmt->execute()) {
                        $mensaje = 'Producto desactivado exitosamente';
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = 'Error al desactivar producto';
                        $tipo_mensaje = 'error';
                    }
                } catch(PDOException $e) {
                    $mensaje = 'Error de base de datos: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener proveedores para el formulario
$stmt_proveedores = $pdo->prepare("SELECT id_proveedor, empresa FROM proveedores ORDER BY empresa");
$stmt_proveedores->execute();
$proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock, p.id_proveedor, p.activo, p.fecha_registro, pr.empresa as proveedor_nombre
    FROM productos p
    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
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
    <title>Gestión de Productos - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="../../css/admin/modal.css">
    <link rel="stylesheet" href="../../css/admin/navbar.css">
    <link rel="stylesheet" href="../../css/admin/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación mejorada y responsiva -->
    <?php include 'includes/navbar.php'; ?>

    <div class="admin-container">

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Gestión de Productos</h1>
                <p>Aquí puedes administrar los productos de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="productos-actions">
                <div class="action-buttons">
                    <button class="action-btn primary" onclick="showForm('crear')">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                    <button class="action-btn secondary" onclick="location.href='operaciones_lote.php'">
                        <i class="fas fa-layer-group"></i> Operaciones por Lote
                    </button>
                </div>
                <div class="search-filter">
                    <input type="text" id="searchProducto" placeholder="Buscar producto..." onkeyup="searchProductos()">
                    <select id="filterStatus" onchange="filterProductos()">
                        <option value="">Todos los productos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                    <select id="filterProveedor" onchange="filterProductos()">
                        <option value="">Todos los proveedores</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id_proveedor']; ?>"><?php echo htmlspecialchars($proveedor['empresa']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterStock" onchange="filterProductos()">
                        <option value="">Todos los stocks</option>
                        <option value="bajo">Bajo Stock (&lt; 10)</option>
                        <option value="medio">Stock Medio (10-30)</option>
                        <option value="alto">Stock Alto (&gt; 30)</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="table-container">
                <table id="tablaProductos" class="productos-table" role="table">
                    <thead>
                        <tr role="row">
                            <th role="columnheader">ID</th>
                            <th role="columnheader">Nombre</th>
                            <th role="columnheader">Sabor</th>
                            <th role="columnheader">Descripción</th>
                            <th role="columnheader">Precio (S/.)</th>
                            <th role="columnheader">Stock (L)</th>
                            <th role="columnheader">Proveedor</th>
                            <th role="columnheader">Estado</th>
                            <th role="columnheader" aria-label="Acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): 
                            $stockCategory = 'medio';
                            $stock = isset($producto['stock']) ? floatval($producto['stock']) : 0;
                            if ($stock < 10) $stockCategory = 'bajo';
                            elseif ($stock > 30) $stockCategory = 'alto';
                            
                            $idProveedor = isset($producto['id_proveedor']) ? $producto['id_proveedor'] : '';
                            $activo = isset($producto['activo']) ? $producto['activo'] : 0;
                        ?>
                        <tr role="row" tabindex="0" 
                            data-status="<?php echo htmlspecialchars($activo); ?>" 
                            data-proveedor="<?php echo htmlspecialchars($idProveedor); ?>" 
                            data-stock="<?php echo htmlspecialchars($stockCategory); ?>"
                            data-id="<?php echo htmlspecialchars($producto['id_producto']); ?>">
                            <td data-label="ID"><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                            <td data-label="Sabor"><?php echo htmlspecialchars($producto['sabor']); ?></td>
                            <td data-label="Descripción"><?php echo htmlspecialchars(isset($producto['descripcion']) && $producto['descripcion'] ? (strlen($producto['descripcion']) > 50 ? substr($producto['descripcion'], 0, 50) . '...' : $producto['descripcion']) : 'N/A'); ?></td>
                            <td data-label="Precio">S/. <?php echo number_format(floatval($producto['precio']), 2); ?></td>
                            <td data-label="Stock"><?php echo htmlspecialchars($producto['stock']); ?>L</td>
                            <td data-label="Proveedor"><?php echo htmlspecialchars(isset($producto['proveedor_nombre']) && $producto['proveedor_nombre'] ? $producto['proveedor_nombre'] : 'N/A'); ?></td>
                            <td data-label="Estado">
                                <span class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <button 
                                    class="action-btn edit" 
                                    onclick="editarProducto(<?php echo $producto['id_producto']; ?>)"
                                    aria-label="Editar producto <?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button 
                                    class="action-btn delete" 
                                    onclick="confirmarEliminar(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>')"
                                    aria-label="Desactivar producto <?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button 
                                    class="action-btn secondary" 
                                    onclick="actualizarStock(<?php echo $producto['id_producto']; ?>, <?php echo $producto['stock']; ?>)"
                                    aria-label="Actualizar stock de <?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($productos)): ?>
            <div style="text-align: center; padding: 20px; background: white; margin-bottom: 24px;">
                <p><i class="fas fa-inbox"></i> No hay productos registrados. <a href="#" onclick="openModal('modalProducto')">Crear uno</a></p>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal para crear/editar producto -->
    <div id="productoForm" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="formTitle">Crear Producto</h2>
                <button class="close-btn" onclick="hideForm()">&times;</button>
            </div>
            <form method="POST" class="producto-form">
                <input type="hidden" id="accionForm" name="accion" value="crear">
                <input type="hidden" id="id_producto" name="id_producto">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Helado de Vainilla">
                    </div>
                    <div class="form-group">
                        <label for="sabor">Sabor *</label>
                        <input type="text" id="sabor" name="sabor" required placeholder="Ej: Vainilla">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio (S/.) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock (Litros) *</label>
                        <input type="number" id="stock" name="stock" min="0" required placeholder="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción del producto..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="id_proveedor">Proveedor *</label>
                        <select id="id_proveedor" name="id_proveedor" required>
                            <option value="">Seleccionar proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?php echo $proveedor['id_proveedor']; ?>">
                                <?php echo htmlspecialchars($proveedor['empresa']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                            Producto Activo
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <button type="button" class="btn btn-secondary" onclick="hideForm()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function showForm(accion) {
            // Limpiar formulario
            document.getElementById('id_producto').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('sabor').value = '';
            document.getElementById('precio').value = '';
            document.getElementById('stock').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('activo').checked = true;
            document.getElementById('id_proveedor').value = '';
            document.getElementById('accionForm').value = accion;

            if (accion === 'crear') {
                document.getElementById('formTitle').textContent = 'Crear Nuevo Producto';
            } else {
                document.getElementById('formTitle').textContent = 'Editar Producto';
            }
            document.getElementById('productoForm').style.display = 'flex';
        }

        function searchProductos() {
            const input = document.getElementById('searchProducto');
            const filter = input.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#tablaProductos tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                // Buscar en nombre, sabor y descripción
                const nombreCell = row.cells[1]?.textContent?.toLowerCase() || '';
                const saborCell = row.cells[2]?.textContent?.toLowerCase() || '';
                const descCell = row.cells[3]?.textContent?.toLowerCase() || '';
                
                const match = !filter || 
                              nombreCell.includes(filter) || 
                              saborCell.includes(filter) || 
                              descCell.includes(filter);
                
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            // Mostrar mensaje si no hay resultados
            updateNoResultsMessage();
        }

        function filterProductos() {
            const statusFilter = document.getElementById('filterStatus').value;
            const proveedorFilter = document.getElementById('filterProveedor').value;
            const stockFilter = document.getElementById('filterStock').value;
            const rows = document.querySelectorAll('#tablaProductos tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const proveedor = row.getAttribute('data-proveedor');
                const stock = row.getAttribute('data-stock');

                const statusMatch = !statusFilter || status == statusFilter;
                const proveedorMatch = !proveedorFilter || proveedor == proveedorFilter;
                const stockMatch = !stockFilter || stock == stockFilter;

                const shouldShow = statusMatch && proveedorMatch && stockMatch;
                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleCount++;
            });

            // Mostrar mensaje si no hay resultados
            updateNoResultsMessage();
        }

        function updateNoResultsMessage() {
            const tbody = document.querySelector('#tablaProductos tbody');
            const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
            
            // Eliminar mensaje anterior si existe
            const existingMsg = tbody.parentElement?.querySelector('.no-results-message');
            if (existingMsg) existingMsg.remove();
            
            if (visibleRows.length === 0) {
                const message = document.createElement('div');
                message.className = 'no-results-message';
                message.textContent = '📭 No hay productos que coincidan con los filtros';
                message.style.cssText = `
                    padding: 20px;
                    text-align: center;
                    color: #6b7280;
                    font-size: 0.95rem;
                    background: white;
                    border-top: 1px solid #e5e7eb;
                `;
                tbody.parentElement.appendChild(message);
            }
        }

        // Combinar búsqueda y filtrado
        function applyAllFilters() {
            searchProductos();
            // Aplicar filtros después de la búsqueda
            setTimeout(() => {
                const rows = document.querySelectorAll('#tablaProductos tbody tr');
                const statusFilter = document.getElementById('filterStatus').value;
                const proveedorFilter = document.getElementById('filterProveedor').value;
                const stockFilter = document.getElementById('filterStock').value;
                
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const status = row.getAttribute('data-status');
                        const proveedor = row.getAttribute('data-proveedor');
                        const stock = row.getAttribute('data-stock');

                        const statusMatch = !statusFilter || status == statusFilter;
                        const proveedorMatch = !proveedorFilter || proveedor == proveedorFilter;
                        const stockMatch = !stockFilter || stock == stockFilter;

                        row.style.display = (statusMatch && proveedorMatch && stockMatch) ? '' : 'none';
                    }
                });
                updateNoResultsMessage();
            }, 0);
        }

        // Agregar event listeners para búsqueda en tiempo real
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchProducto');
            if (searchInput) {
                searchInput.addEventListener('input', applyAllFilters);
            }

            const filterStatus = document.getElementById('filterStatus');
            const filterProveedor = document.getElementById('filterProveedor');
            const filterStock = document.getElementById('filterStock');

            if (filterStatus) filterStatus.addEventListener('change', applyAllFilters);
            if (filterProveedor) filterProveedor.addEventListener('change', applyAllFilters);
            if (filterStock) filterStock.addEventListener('change', applyAllFilters);
        });

        function editarProducto(id) {
            // Obtener datos del producto directamente de la tabla
            const row = document.querySelector(`tr[data-id="${id}"]`);
            
            if (!row) {
                // Si no está en la tabla actual, hacer fetch
                fetch('funcionalidades/obtener_producto.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarProductoEnFormulario(data.producto);
                    } else {
                        alert('Error al obtener producto: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al obtener producto');
                });
            } else {
                // Extraer datos de la tabla
                const producto = {
                    id_producto: id,
                    nombre: row.cells[1].textContent,
                    sabor: row.cells[2].textContent,
                    descripcion: row.cells[3].textContent,
                    precio: row.cells[4].textContent.replace('S/. ', ''),
                    stock: row.cells[5].textContent.replace('L', ''),
                    id_proveedor: row.getAttribute('data-proveedor'),
                    activo: row.getAttribute('data-status') == 1
                };
                cargarProductoEnFormulario(producto);
            }
        }

        function cargarProductoEnFormulario(producto) {
            document.getElementById('id_producto').value = producto.id_producto;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('sabor').value = producto.sabor;
            document.getElementById('precio').value = parseFloat(producto.precio);
            document.getElementById('stock').value = parseInt(producto.stock);
            document.getElementById('descripcion').value = producto.descripcion || '';
            document.getElementById('activo').checked = producto.activo == 1 || producto.activo === true;
            document.getElementById('id_proveedor').value = producto.id_proveedor || '';

            document.getElementById('accionForm').value = 'editar';
            document.getElementById('formTitle').textContent = 'Editar Producto';
            document.getElementById('productoForm').style.display = 'flex';
        }

        function actualizarStock(id, stockActual) {
            const nuevoStock = prompt(`Ingrese el nuevo stock para el producto (actual: ${stockActual}L):`, stockActual);
            if (nuevoStock !== null && !isNaN(nuevoStock) && nuevoStock >= 0) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'editar';
                form.appendChild(accionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_producto';
                idInput.value = id;
                form.appendChild(idInput);

                const stockInput = document.createElement('input');
                stockInput.type = 'hidden';
                stockInput.name = 'stock';
                stockInput.value = nuevoStock;
                form.appendChild(stockInput);

                const soloStockInput = document.createElement('input');
                soloStockInput.type = 'hidden';
                soloStockInput.name = 'solo_stock';
                soloStockInput.value = '1';
                form.appendChild(soloStockInput);

                const stockAnteriorInput = document.createElement('input');
                stockAnteriorInput.type = 'hidden';
                stockAnteriorInput.name = 'stock_anterior';
                stockAnteriorInput.value = stockActual;
                form.appendChild(stockAnteriorInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas desactivar el producto "${nombre}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'eliminar';
                form.appendChild(accionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_producto';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function hideForm() {
            document.getElementById('productoForm').style.display = 'none';
        }

        // Cerrar modal al hacer click fuera
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('productoForm');
            if (event.target === modal) {
                hideForm();
            }
        });
    </script>
    <!-- Script mejorado con accesibilidad -->
    <script src="../../js/admin/script.js"></script>
    <script src="../../js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
    <script src="../../js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>