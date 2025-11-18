<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

$mensaje = '';
$tipo_mensaje = '';

// Verificar si se está editando un producto
$id_producto = null;
$producto = null;

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    $stmt_producto = $pdo->prepare("
        SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock, p.id_proveedor, p.activo
        FROM productos p
        WHERE p.id_producto = :id_producto
    ");
    $stmt_producto->bindParam(':id_producto', $id_producto);
    $stmt_producto->execute();
    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        header('Location: productos.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto_post = $_POST['id_producto'];
    $nombre = trim($_POST['nombre']);
    $sabor = trim($_POST['sabor']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_proveedor = $_POST['id_proveedor'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, sabor = :sabor, descripcion = :descripcion, precio = :precio, stock = :stock, id_proveedor = :id_proveedor, activo = :activo WHERE id_producto = :id_producto");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':sabor', $sabor);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':id_proveedor', $id_proveedor);
    $stmt->bindParam(':activo', $activo);
    $stmt->bindParam(':id_producto', $id_producto_post);
    
    if ($stmt->execute()) {
        $mensaje = 'Producto actualizado exitosamente';
        $tipo_mensaje = 'success';
        
        // Actualizar la variable con los nuevos datos
        $producto = [
            'id_producto' => $id_producto_post,
            'nombre' => $nombre,
            'sabor' => $sabor,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'id_proveedor' => $id_proveedor,
            'activo' => $activo
        ];
    } else {
        $mensaje = 'Error al actualizar producto';
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
    <title>
        <?php echo $producto ? 'Editar Producto - Concelato Gelateria' : 'Agregar Producto - Concelato Gelateria'; ?>
    </title>
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
                    Concelato Gelateria - <?php echo $producto ? 'Editar Producto' : 'Agregar Producto'; ?>
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
                <h1><?php echo $producto ? 'Editar Producto' : 'Agregar Nuevo Producto'; ?></h1>
                <p><?php echo $producto ? 'Modifique los detalles del producto' : 'Complete los detalles del nuevo producto'; ?></p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_producto" value="<?php echo $producto ? $producto['id_producto'] : ''; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Producto</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo $producto ? htmlspecialchars($producto['nombre']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="sabor">Sabor</label>
                            <input type="text" id="sabor" name="sabor" value="<?php echo $producto ? htmlspecialchars($producto['sabor']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio (S/.)</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo $producto ? $producto['precio'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" min="0" value="<?php echo $producto ? $producto['stock'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_proveedor">Proveedor</label>
                            <select id="id_proveedor" name="id_proveedor" required>
                                <option value="">Seleccione un proveedor</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id_proveedor']; ?>" <?php echo ($producto && $producto['id_proveedor'] == $proveedor['id_proveedor']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($proveedor['empresa']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group checkbox-group">
                            <label for="activo">Activo</label>
                            <input type="checkbox" id="activo" name="activo" value="1" <?php echo ($producto && $producto['activo'] == 1) ? 'checked' : ''; ?>>
                            <label for="activo">¿Producto activo?</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"><?php echo $producto ? htmlspecialchars($producto['descripcion']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="location.href='productos.php'">Cancelar</button>
                        <button type="submit" class="btn save"><?php echo $producto ? 'Actualizar Producto' : 'Guardar Producto'; ?></button>
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