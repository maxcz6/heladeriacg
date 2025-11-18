<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $turno = $_POST['turno'];
    
    $stmt = $pdo->prepare("INSERT INTO vendedores (nombre, dni, telefono, correo, turno) VALUES (:nombre, :dni, :telefono, :correo, :turno)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':turno', $turno);
    
    if ($stmt->execute()) {
        $id_vendedor = $pdo->lastInsertId();
        
        // Crear usuario para el empleado
        $username = strtolower(str_replace(' ', '', $nombre));
        $password = password_hash('empleado123', PASSWORD_DEFAULT); // Contraseña por defecto
        
        $stmt_usuario = $pdo->prepare("INSERT INTO usuarios (username, password, id_role, id_vendedor) VALUES (:username, :password, 2, :id_vendedor)");
        $stmt_usuario->bindParam(':username', $username);
        $stmt_usuario->bindParam(':password', $password);
        $stmt_usuario->bindParam(':id_vendedor', $id_vendedor);
        $stmt_usuario->execute();
        
        $mensaje = 'Empleado creado exitosamente';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al crear empleado';
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Empleado - Concelato Gelateria</title>
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
                    Concelato Gelateria - Agregar Empleado
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
                <h1>Agregar Nuevo Empleado</h1>
                <p>Complete los detalles del nuevo empleado</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" maxlength="12" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="turno">Turno</label>
                        <select id="turno" name="turno" required>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="location.href='empleados.php'">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Empleado</button>
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