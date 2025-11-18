<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $sabor = trim($_POST['sabor']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_proveedor = $_POST['id_proveedor'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor, activo) VALUES (:nombre, :sabor, :descripcion, :precio, :stock, :id_proveedor, :activo)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':sabor', $sabor);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':id_proveedor', $id_proveedor);
    $stmt->bindParam(':activo', $activo);
    
    if ($stmt->execute()) {
        $mensaje = 'Producto creado exitosamente';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al crear producto';
        $tipo_mensaje = 'error';
    }
}

// Obtener proveedores para el formulario
$stmt_proveedores = $pdo->prepare("SELECT id_proveedor, empresa FROM proveedores ORDER BY empresa");
$stmt_proveedores->execute();
$proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Agregar Producto
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
                        <li><a href="empleados.php"><i class="fas fa-user-tie"></i> Empleados</a></li>
                        <li><a href="reportes.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                        <li><a href="promociones.php"><i class="fas fa-percentage"></i> Promociones</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Agregar Nuevo Producto</h1>
                <p>Complete los detalles del nuevo producto</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
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
                            <select id="id_proveedor" name="id_proveedor" required>
                                <option value="">Seleccione un proveedor</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id_proveedor']; ?>"><?php echo htmlspecialchars($proveedor['empresa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group checkbox-group">
                            <label for="activo">Activo</label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                            <label for="activo">¿Producto activo?</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="location.href='productos.php'">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Producto</button>
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
    </script>
</body>
</html>