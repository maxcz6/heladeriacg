<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/admin_functions.php');

$mensaje = '';
$tipo_mensaje = '';

// Manejar operaciones por lotes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'actualizar_stock':
                if (isset($_POST['productos'])) {
                    $productos = [];
                    foreach ($_POST['productos'] as $id => $stock) {
                        if (is_numeric($stock)) {
                            $productos[] = ['id_producto' => $id, 'stock' => $stock];
                        }
                    }
                    
                    $resultado = actualizarStockPorLote($productos);
                    $mensaje = $resultado['message'];
                    $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
                }
                break;
                
            case 'desactivar_productos':
                if (isset($_POST['ids_productos'])) {
                    $ids = array_map('intval', $_POST['ids_productos']);
                    $resultado = desactivarProductosPorLote($ids);
                    $mensaje = $resultado['message'];
                    $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
                }
                break;
                
            case 'importar_productos':
                if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === 0) {
                    $archivo = $_FILES['archivo_csv']['tmp_name'];
                    $nombre_archivo = $_FILES['archivo_csv']['name'];
                    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

                    if ($extension === 'csv') {
                        // Procesar archivo CSV
                        if (($handle = fopen($archivo, "r")) !== FALSE) {
                            $productos = [];
                            $header = fgetcsv($handle, 1000, ",");

                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                if (count($data) >= 6) { // nombre, sabor, descripcion, precio, stock, id_proveedor
                                    $productos[] = [
                                        'nombre' => $data[0],
                                        'sabor' => $data[1],
                                        'descripcion' => $data[2],
                                        'precio' => $data[3],
                                        'stock' => $data[4],
                                        'id_proveedor' => $data[5],
                                        'activo' => 1
                                    ];
                                }
                            }
                            fclose($handle);

                            $resultado = crearProductosPorLote($productos);
                            $mensaje = $resultado['message'];
                            $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
                        } else {
                            $mensaje = 'Error al leer el archivo CSV';
                            $tipo_mensaje = 'error';
                        }
                    } else {
                        // Archivo Excel subido, mostrar mensaje de error amigable
                        $mensaje = 'El sistema solo puede procesar archivos CSV directamente. Por favor, convierte tu archivo Excel a CSV: Archivo → Guardar como → CSV (delimitado por comas)';
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = 'No se subió ningún archivo';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener productos para la edición por lotes
$productos = obtenerProductosDetallados();
$proveedores = [];
$stmt = $pdo->prepare("SELECT id_proveedor, empresa FROM proveedores ORDER BY empresa");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operaciones por Lote - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .batch-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .batch-section h3 {
            margin-top: 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .batch-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .batch-table th,
        .batch-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .batch-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .batch-table input[type="checkbox"] {
            transform: scale(1.2);
        }
        
        .check-all {
            text-align: center;
        }
        
        .file-upload {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin: 15px 0;
        }
        
        .file-upload.dragover {
            border-color: #3498db;
            background: #f8f9ff;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div>
                <button class="menu-toggle" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="admin-nav">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    <span>Concelato Admin</span>
                </div>
                <nav id="admin-nav">
                    <a href="index.php">
                        <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                    </a>
                    <a href="productos.php">
                        <i class="fas fa-box"></i> <span>Productos</span>
                    </a>
                    <a href="ventas.php">
                        <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                    </a>
                    <a href="empleados.php">
                        <i class="fas fa-users"></i> <span>Empleados</span>
                    </a>
                    <a href="clientes.php">
                        <i class="fas fa-user-friends"></i> <span>Clientes</span>
                    </a>
                    <a href="proveedores.php">
                        <i class="fas fa-truck"></i> <span>Proveedores</span>
                    </a>
                    <a href="usuarios.php">
                        <i class="fas fa-user-cog"></i> <span>Usuarios</span>
                    </a>
                    <a href="promociones.php">
                        <i class="fas fa-tag"></i> <span>Promociones</span>
                    </a>
                    <a href="sucursales.php">
                        <i class="fas fa-store"></i> <span>Sucursales</span>
                    </a>
                    <a href="configuracion.php">
                        <i class="fas fa-cog"></i> <span>Configuración</span>
                    </a>
                    <a href="../../conexion/cerrar_sesion.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                    </a>
                </nav>
            </div>
        </header>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Operaciones por Lote</h1>
                <p>Realiza operaciones masivas en productos, clientes y otros datos</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="batch-section">
                <h3><i class="fas fa-boxes"></i> Actualizar Stock de Productos</h3>
                
                <form method="POST">
                    <input type="hidden" name="accion" value="actualizar_stock">
                    
                    <div class="table-container">
                        <table class="batch-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Sabor</th>
                                    <th>Stock Actual</th>
                                    <th>Nuevo Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td>
                                        <input type="number" 
                                               name="productos[<?php echo $producto['id_producto']; ?>]" 
                                               value="<?php echo $producto['stock']; ?>" 
                                               min="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar Stock</button>
                    </div>
                </form>
            </div>

            <div class="batch-section">
                <h3><i class="fas fa-trash-alt"></i> Desactivar Productos</h3>
                
                <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desactivar los productos seleccionados?')">
                    <input type="hidden" name="accion" value="desactivar_productos">
                    
                    <div class="table-container">
                        <table class="batch-table">
                            <thead>
                                <tr>
                                    <th class="check-all">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                    </th>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Sabor</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td class="check-all">
                                        <input type="checkbox" 
                                               name="ids_productos[]" 
                                               value="<?php echo $producto['id_producto']; ?>">
                                    </td>
                                    <td><?php echo $producto['id_producto']; ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Desactivar Seleccionados</button>
                    </div>
                </form>
            </div>

            <div class="batch-section">
                <h3><i class="fas fa-file-import"></i> Importar Productos desde CSV</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="importar_productos">
                    
                    <p>Formato: CSV o Excel convertido a CSV (nombre, sabor, descripcion, precio, stock, id_proveedor). <br>
                    <small>Si subes un archivo Excel, por favor conviértelo a CSV primero: Archivo → Guardar como → CSV (delimitado por comas)</small></p>

                    <div class="file-upload" id="dropZone">
                        <p>Arrastra un archivo CSV aquí o haz clic para seleccionar</p>
                        <input type="file" name="archivo_csv" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('fileInput').click()">Seleccionar Archivo</button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Importar Productos</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="ids_productos[]"]');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
        }

        // Implementar arrastrar y soltar para el archivo CSV
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            dropZone.classList.remove('dragover');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                dropZone.querySelector('p').textContent = files[0].name;
            }
        }

        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                dropZone.querySelector('p').textContent = this.files[0].name;
            }
        });
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
</body>
</html>