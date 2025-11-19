<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Obtener todas las sucursales
$sucursales = obtenerSucursales();

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo empleado
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $turno = trim($_POST['turno']);
                $id_sucursal = $_POST['id_sucursal'];

                $stmt = $pdo->prepare("INSERT INTO vendedores (nombre, dni, telefono, correo, turno, id_sucursal) VALUES (:nombre, :dni, :telefono, :correo, :turno, :id_sucursal)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':turno', $turno);
                $stmt->bindParam(':id_sucursal', $id_sucursal);

                if ($stmt->execute()) {
                    // Crear usuario para el empleado
                    $id_vendedor = $pdo->lastInsertId();
                    $username = strtolower(str_replace(' ', '', $nombre));
                    $password = password_hash('empleado123', PASSWORD_DEFAULT); // Contraseña por defecto
                    $id_role = 2; // Rol de empleado

                    $stmt_usuario = $pdo->prepare("INSERT INTO usuarios (username, password, id_role, id_vendedor, id_sucursal) VALUES (:username, :password, :id_role, :id_vendedor, :id_sucursal)");
                    $stmt_usuario->bindParam(':username', $username);
                    $stmt_usuario->bindParam(':password', $password);
                    $stmt_usuario->bindParam(':id_role', $id_role);
                    $stmt_usuario->bindParam(':id_vendedor', $id_vendedor);
                    $stmt_usuario->bindParam(':id_sucursal', $id_sucursal);
                    $stmt_usuario->execute();

                    $mensaje = 'Empleado creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear empleado';
                    $tipo_mensaje = 'error';
                }
                break;

            case 'editar':
                // Editar empleado existente
                $id_vendedor = $_POST['id_vendedor'];
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $turno = trim($_POST['turno']);
                $id_sucursal = $_POST['id_sucursal'];

                $stmt = $pdo->prepare("UPDATE vendedores SET nombre = :nombre, dni = :dni, telefono = :telefono, correo = :correo, turno = :turno, id_sucursal = :id_sucursal WHERE id_vendedor = :id_vendedor");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':turno', $turno);
                $stmt->bindParam(':id_sucursal', $id_sucursal);
                $stmt->bindParam(':id_vendedor', $id_vendedor);

                if ($stmt->execute()) {
                    // Actualizar también el usuario
                    $stmt_usuario = $pdo->prepare("UPDATE usuarios SET id_sucursal = :id_sucursal WHERE id_vendedor = :id_vendedor");
                    $stmt_usuario->bindParam(':id_sucursal', $id_sucursal);
                    $stmt_usuario->bindParam(':id_vendedor', $id_vendedor);
                    $stmt_usuario->execute();
                    
                    $mensaje = 'Empleado actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar empleado';
                    $tipo_mensaje = 'error';
                }
                break;

            case 'eliminar':
                // Eliminar empleado (cambiar estado o eliminar)
                $id_vendedor = $_POST['id_vendedor'];

                $stmt = $pdo->prepare("DELETE FROM vendedores WHERE id_vendedor = :id_vendedor");
                $stmt->bindParam(':id_vendedor', $id_vendedor);

                if ($stmt->execute()) {
                    // También eliminar el usuario asociado
                    $stmt_usuario = $pdo->prepare("DELETE FROM usuarios WHERE id_vendedor = :id_vendedor");
                    $stmt_usuario->bindParam(':id_vendedor', $id_vendedor);
                    $stmt_usuario->execute();

                    $mensaje = 'Empleado eliminado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar empleado';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener empleados
$stmt_empleados = $pdo->prepare("
    SELECT v.*, s.nombre as nombre_sucursal
    FROM vendedores v
    LEFT JOIN sucursales s ON v.id_sucursal = s.id_sucursal
    ORDER BY v.nombre
");
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando un empleado
$empleado_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("
        SELECT v.* 
        FROM vendedores v 
        WHERE v.id_vendedor = :id_vendedor
    ");
    $stmt_editar->bindParam(':id_vendedor', $id_editar);
    $stmt_editar->execute();
    $empleado_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar/Editar Empleado - Concelato Gelateria</title>
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
                    Concelato Gelateria - Empleados
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
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
                <h1><?php echo isset($empleado_editar) ? 'Editar Empleado' : 'Agregar Empleado'; ?></h1>
                <p><?php echo isset($empleado_editar) ? 'Modifique los datos del empleado' : 'Complete los datos para crear un nuevo empleado'; ?></p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <!-- Formulario para crear/editar empleado -->
            <div class="form-container">
                <form id="empleadoFormulario" method="POST">
                    <input type="hidden" name="accion" value="<?php echo isset($empleado_editar) ? 'editar' : 'crear'; ?>">
                    <input type="hidden" name="id_vendedor" value="<?php echo $empleado_editar['id_vendedor'] ?? ''; ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empleado_editar['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" maxlength="12" value="<?php echo htmlspecialchars($empleado_editar['dni'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($empleado_editar['telefono'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($empleado_editar['correo'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="turno">Turno</label>
                            <select id="turno" name="turno" required>
                                <option value="Mañana" <?php echo (isset($empleado_editar) && $empleado_editar['turno'] === 'Mañana') ? 'selected' : ''; ?>>Mañana</option>
                                <option value="Tarde" <?php echo (isset($empleado_editar) && $empleado_editar['turno'] === 'Tarde') ? 'selected' : ''; ?>>Tarde</option>
                                <option value="Noche" <?php echo (isset($empleado_editar) && $empleado_editar['turno'] === 'Noche') ? 'selected' : ''; ?>>Noche</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_sucursal">Sucursal</label>
                            <select id="id_sucursal" name="id_sucursal" required>
                                <?php foreach ($sucursales as $sucursal): ?>
                                <option value="<?php echo $sucursal['id_sucursal']; ?>" 
                                    <?php echo (isset($empleado_editar) && $empleado_editar['id_sucursal'] == $sucursal['id_sucursal']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sucursal['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="location.href='empleados.php'">Cancelar</button>
                        <button type="submit" class="btn save"><?php echo isset($empleado_editar) ? 'Actualizar' : 'Crear'; ?> Empleado</button>
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