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
                
            case 'activar_productos':
                if (isset($_POST['ids_productos_activar']) && is_array($_POST['ids_productos_activar'])) {
                    $ids = array_map('intval', $_POST['ids_productos_activar']);
                    $resultado = activarProductosPorLote($ids);
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


// Obtener productos activos para la edición por lotes
$stmt_productos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock, p.id_proveedor, p.activo, p.fecha_registro, pr.empresa as proveedor_nombre
    FROM productos p
    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
    WHERE p.activo = 1
    ORDER BY p.nombre
");
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos inactivos para activar
$stmt_inactivos = $pdo->prepare("
    SELECT p.id_producto, p.nombre, p.sabor, p.stock
    FROM productos p
    WHERE p.activo = 0
    ORDER BY p.nombre
");
$stmt_inactivos->execute();
$productos_inactivos = $stmt_inactivos->fetchAll(PDO::FETCH_ASSOC);

// Obtener proveedores
$proveedores = [];
$stmt = $pdo->prepare("SELECT id_proveedor, empresa FROM proveedores ORDER BY empresa");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// DEBUG: Ver qué campos tenemos en productos
if (!empty($productos)) {
    error_log("Campos en productos: " . print_r(array_keys($productos[0]), true));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operaciones por Lote - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="../../css/admin/navbar.css">
    <link rel="stylesheet" href="../../css/admin/operaciones_lote.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación -->
    <?php include 'includes/navbar.php'; ?>

    <div class="admin-container">

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
                        <table class="batch-table" role="table">
                            <thead>
                                <tr role="row">
                                    <th role="columnheader">ID</th>
                                    <th role="columnheader">Producto</th>
                                    <th role="columnheader">Sabor</th>
                                    <th role="columnheader">Stock Actual</th>
                                    <th role="columnheader">Nuevo Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr role="row" tabindex="0">
                                    <td data-label="ID"><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                                    <td data-label="Producto"><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                    <td data-label="Sabor"><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td data-label="Stock Actual"><?php echo htmlspecialchars($producto['stock']); ?>L</td>
                                    <td data-label="Nuevo Stock">
                                        <input type="number" 
                                               name="productos[<?php echo $producto['id_producto']; ?>]" 
                                               value="<?php echo htmlspecialchars($producto['stock']); ?>" 
                                               min="0"
                                               class="stock-input">
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
                        <table class="batch-table" role="table">
                            <thead>
                                <tr role="row">
                                    <th role="columnheader" class="check-all">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" aria-label="Seleccionar todos">
                                    </th>
                                    <th role="columnheader">ID</th>
                                    <th role="columnheader">Producto</th>
                                    <th role="columnheader">Sabor</th>
                                    <th role="columnheader">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr role="row" tabindex="0">
                                    <td data-label="Seleccionar" class="check-all">
                                        <input type="checkbox" 
                                               name="ids_productos[]" 
                                               value="<?php echo $producto['id_producto']; ?>"
                                               aria-label="Seleccionar <?php echo htmlspecialchars($producto['nombre']); ?>">
                                    </td>
                                    <td data-label="ID"><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                                    <td data-label="Producto"><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                    <td data-label="Sabor"><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                    <td data-label="Stock"><?php echo htmlspecialchars($producto['stock']); ?>L</td>
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
                <h3><i class="fas fa-check-circle"></i> Activar Productos</h3>
                
                <?php if (empty($productos_inactivos)): ?>
                    <p class="no-items">No hay productos inactivos para mostrar.</p>
                <?php else: ?>
                    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas activar los productos seleccionados?')">
                        <input type="hidden" name="accion" value="activar_productos">
                        
                        <div class="table-container">
                            <table class="batch-table" role="table">
                                <thead>
                                    <tr role="row">
                                        <th role="columnheader" class="check-all">
                                            <input type="checkbox" id="selectAllInactive" onclick="toggleSelectAllInactive(this)" aria-label="Seleccionar todos">
                                        </th>
                                        <th role="columnheader">ID</th>
                                        <th role="columnheader">Producto</th>
                                        <th role="columnheader">Sabor</th>
                                        <th role="columnheader">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos_inactivos as $producto): ?>
                                    <tr role="row" tabindex="0">
                                        <td data-label="Seleccionar" class="check-all">
                                            <input type="checkbox" 
                                                   name="ids_productos_activar[]" 
                                                   value="<?php echo $producto['id_producto']; ?>"
                                                   class="inactive-checkbox"
                                                   aria-label="Seleccionar <?php echo htmlspecialchars($producto['nombre']); ?>">
                                        </td>
                                        <td data-label="ID"><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                                        <td data-label="Producto"><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                        <td data-label="Sabor"><?php echo htmlspecialchars($producto['sabor']); ?></td>
                                        <td data-label="Stock"><?php echo htmlspecialchars($producto['stock']); ?>L</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success" style="background: var(--success); color: white;">Activar Seleccionados</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="batch-section">
                <h3><i class="fas fa-file-import"></i> Importar Productos desde CSV</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="importar_productos">
                    
                    <div class="form-group">
                        <label>Instrucciones de Formato</label>
                        <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">
                            El archivo CSV debe contener las siguientes columnas (en este orden):
                            <br><strong>nombre, sabor, descripcion, precio, stock, id_proveedor</strong>
                            <br><small>Si tienes un archivo Excel, guárdalo como CSV: Archivo → Guardar como → CSV (delimitado por comas)</small>
                        </p>
                    </div>

                    <div class="file-upload" id="dropZone">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 12px; display: block;"></i>
                        <p><strong>Arrastra un archivo CSV aquí o haz clic para seleccionar</strong></p>
                        <input type="file" name="archivo_csv" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('fileInput').click()" style="margin-top: 10px;">
                            <i class="fas fa-folder-open"></i> Seleccionar Archivo
                        </button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importar Productos
                        </button>
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

        function toggleSelectAll(source) {
        checkboxes = document.getElementsByName('ids_productos[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }
    
    function toggleSelectAllInactive(source) {
        checkboxes = document.getElementsByName('ids_productos_activar[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
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
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>