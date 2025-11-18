<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

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
                
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor) VALUES (:nombre, :sabor, :descripcion, :precio, :stock, :id_proveedor)");
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
                break;
                
            case 'editar':
                // Editar producto existente
                $id_producto = $_POST['id_producto'];
                $nombre = trim($_POST['nombre']);
                $sabor = trim($_POST['sabor']);
                $descripcion = trim($_POST['descripcion']);
                $precio = $_POST['precio'];
                $stock = $_POST['stock'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, sabor = :sabor, descripcion = :descripcion, precio = :precio, stock = :stock, activo = :activo WHERE id_producto = :id_producto");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':sabor', $sabor);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':activo', $activo);
                $stmt->bindParam(':id_producto', $id_producto);
                
                if ($stmt->execute()) {
                    $mensaje = 'Producto actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar producto';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar (desactivar) producto
                $id_producto = $_POST['id_producto'];
                
                $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = :id_producto");
                $stmt->bindParam(':id_producto', $id_producto);
                
                if ($stmt->execute()) {
                    $mensaje = 'Producto desactivado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al desactivar producto';
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

// Obtener productos con estado
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock, p.fecha_registro, p.activo, pr.empresa as proveedor_nombre
    FROM productos p
    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
    ORDER BY p.nombre
");
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando un producto
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM productos WHERE id_producto = :id_producto");
    $stmt_editar->bindParam(':id_producto', $id_editar);
    $stmt_editar->execute();
    $producto_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Productos
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
                        <li><a href="empleados.php"><i class="fas fa-user-tie"></i> Empleados</a></li>
                        <li><a href="reportes.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

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
                <button class="action-btn primary" onclick="location.href='agregar_producto.php'">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
                <div class="search-filter">
                    <input type="text" id="searchProduct" placeholder="Buscar producto..." onkeyup="searchProducts()">
                    <select id="filterStatus" onchange="filterProducts()">
                        <option value="">Todos los productos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>

            <!-- Formulario para crear/editar producto -->
            <div id="productoForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Producto</h2>
                <form id="productoFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_producto" id="id_producto" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Producto</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="sabor">Sabor</label>
                            <input type="text" id="sabor" name="sabor" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio (S/.)</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_proveedor">Proveedor</label>
                            <select id="id_proveedor" name="id_proveedor">
                                <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id_proveedor']; ?>"><?php echo htmlspecialchars($proveedor['empresa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group checkbox-group">
                            <label for="activo">Activo</label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Producto</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de productos -->
            <div class="table-container">
                <table class="productos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Sabor</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Proveedor</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productosTable">
                        <?php foreach ($productos as $producto): ?>
                        <tr data-status="<?php echo $producto['activo'] ? 'activo' : 'inactivo'; ?>">
                            <td><?php echo $producto['id_producto']; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                            <td>S/. <?php echo number_format($producto['precio'], 2); ?></td>
                            <td><?php echo $producto['stock']; ?>L</td>
                            <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $producto['activo'] ? 'Sí' : 'No'; ?>
                                </span>
                            </td>
                            <td>
                                <a class="action-btn edit" href="editar_producto.php?id=<?php echo $producto['id_producto']; ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes(htmlspecialchars($producto['nombre'])); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function showForm(accion) {
            document.getElementById('accionForm').value = accion;
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Producto' : 'Editar Producto';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('productoFormulario').reset();
                document.getElementById('activo').checked = true;
                document.getElementById('id_producto').value = '';
            }
            
            document.getElementById('productoForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('productoForm').style.display = 'none';
        }
        
        function editarProducto(id) {
            // Hacer una petición para obtener los datos del producto
            fetch(`obtener_producto.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const producto = data.producto;
                    
                    document.getElementById('id_producto').value = producto.id_producto;
                    document.getElementById('nombre').value = producto.nombre;
                    document.getElementById('sabor').value = producto.sabor;
                    document.getElementById('precio').value = producto.precio;
                    document.getElementById('stock').value = producto.stock;
                    document.getElementById('descripcion').value = producto.descripcion || '';
                    document.getElementById('activo').checked = producto.activo == 1;
                    
                    // Seleccionar proveedor
                    if (producto.id_proveedor) {
                        document.getElementById('id_proveedor').value = producto.id_proveedor;
                    }
                    
                    document.getElementById('accionForm').value = 'editar';
                    document.getElementById('formTitle').textContent = 'Editar Producto';
                    document.getElementById('productoForm').style.display = 'block';
                } else {
                    alert('Error al obtener producto: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener producto');
            });
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas desactivar el producto "${nombre}"?`)) {
                // Enviar formulario para eliminar
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
        
        function searchProducts() {
            const input = document.getElementById('searchProduct');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#productosTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const productoCell = row.cells[1].textContent.toLowerCase(); // Nombre
                const saborCell = row.cells[2].textContent.toLowerCase(); // Sabor
                
                if (productoCell.includes(filter) || saborCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function filterProducts() {
            const filter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#productosTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const status = row.getAttribute('data-status');
                
                if (filter === '' || status === filter) {
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
        
        // Cargar datos para edición si se pasó un ID
        <?php if ($producto_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            editarProducto(<?php echo $producto_editar['id_producto']; ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>