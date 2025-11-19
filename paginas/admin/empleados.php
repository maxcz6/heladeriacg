<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Para el header
$current_page = 'empleados';

// Obtener todas las sucursales
$sucursales = obtenerSucursales();

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $rol = trim($_POST['rol']);
                $id_sucursal = $_POST['id_sucursal'];

                $stmt = $pdo->prepare("INSERT INTO vendedores (nombre, telefono, correo, turno, id_sucursal) VALUES (:nombre, :telefono, :correo, :turno, :id_sucursal)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':turno', $rol);
                $stmt->bindParam(':id_sucursal', $id_sucursal);

                if ($stmt->execute()) {
                    $mensaje = 'Empleado creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear empleado';
                    $tipo_mensaje = 'error';
                }
                break;

            case 'editar':
                $id_vendedor = $_POST['id_vendedor'];
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $rol = trim($_POST['rol']);
                $id_sucursal = $_POST['id_sucursal'];

                $stmt = $pdo->prepare("UPDATE vendedores SET nombre = :nombre, telefono = :telefono, correo = :correo, turno = :turno, id_sucursal = :id_sucursal WHERE id_vendedor = :id_vendedor");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':turno', $rol);
                $stmt->bindParam(':id_sucursal', $id_sucursal);
                $stmt->bindParam(':id_vendedor', $id_vendedor);

                if ($stmt->execute()) {
                    $mensaje = 'Empleado actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar empleado';
                    $tipo_mensaje = 'error';
                }
                break;

            case 'eliminar':
                $id_vendedor = $_POST['id_vendedor'];
                $stmt = $pdo->prepare("DELETE FROM vendedores WHERE id_vendedor = :id_vendedor");
                $stmt->bindParam(':id_vendedor', $id_vendedor);

                if ($stmt->execute()) {
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación mejorada y responsiva -->
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
                <a href="empleados.php" class="active">
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

    <!-- Main content -->
    <main class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Gestión de Empleados</h1>
            <p>Administra el equipo de trabajo de la heladería</p>
        </div>

        <!-- Alert messages -->
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="status" aria-live="polite">
            <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($mensaje); ?></span>
        </div>
        <?php endif; ?>

        <!-- Search and Actions Card -->
        <div class="card">
            <div class="card-body">
                <div class="empleados-actions">
                    <button class="action-btn primary" data-action="create" onclick="openModal('modalEmpleado')">
                        <i class="fas fa-plus"></i> Nuevo Empleado
                    </button>
                    <div class="search-filter">
                        <input 
                            type="search" 
                            id="searchEmpleado"
                            class="search-input"
                            placeholder="Buscar por nombre o email..."
                            aria-label="Buscar empleados"
                            data-filter-table="tablaEmpleados">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaEmpleados" class="tabla-admin" role="table">
                        <thead>
                            <tr role="row">
                                <th role="columnheader" aria-sort="none" onclick="TableSorter.sortTable(this)">
                                    <i class="fas fa-arrows-alt-v"></i> Nombre
                                </th>
                                <th role="columnheader" aria-sort="none">Email</th>
                                <th role="columnheader" aria-sort="none">Teléfono</th>
                                <th role="columnheader" aria-sort="none">Rol</th>
                                <th role="columnheader" aria-sort="none">Sucursal</th>
                                <th role="columnheader" aria-label="Acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $empleado): ?>
                            <tr role="row" tabindex="0">
                                <td><strong><?php echo htmlspecialchars($empleado['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($empleado['correo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['telefono'] ?? 'N/A'); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($empleado['turno'] ?? 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($empleado['nombre_sucursal'] ?? 'No asignada'); ?></td>
                                <td>
                                    <button 
                                        class="action-btn edit" 
                                        onclick="editarEmpleado(<?php echo $empleado['id_vendedor']; ?>)"
                                        aria-label="Editar empleado <?php echo htmlspecialchars($empleado['nombre']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        class="action-btn delete" 
                                        onclick="deleteItem(<?php echo $empleado['id_vendedor']; ?>, 'empleado', '<?php echo htmlspecialchars($empleado['nombre']); ?>')"
                                        aria-label="Eliminar empleado <?php echo htmlspecialchars($empleado['nombre']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($empleados)): ?>
                    <div class="empty-state">
                        <p>No hay empleados registrados. <a href="#" onclick="openModal('modalEmpleado')">Crear uno</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Crear/Editar Empleado -->
    <div id="modalEmpleado" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalEmpleadoTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalEmpleadoTitle">Nuevo Empleado</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalEmpleado')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEmpleado" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_vendedor" id="id_vendedor" value="">

                    <div class="form-group">
                        <label for="nombre">Nombre Completo <span aria-label="requerido">*</span></label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required
                            aria-required="true"
                            placeholder="Ej: Juan García">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span aria-label="requerido">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            aria-required="true"
                            placeholder="correo@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input 
                            type="tel" 
                            id="telefono" 
                            name="telefono"
                            placeholder="+51 999 999 999">
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol <span aria-label="requerido">*</span></label>
                        <select id="rol" name="rol" required aria-required="true">
                            <option value="">Seleccionar rol</option>
                            <option value="Mañana">Turno Mañana</option>
                            <option value="Tarde">Turno Tarde</option>
                            <option value="Noche">Turno Noche</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_sucursal">Sucursal <span aria-label="requerido">*</span></label>
                        <select id="id_sucursal" name="id_sucursal" required aria-required="true">
                            <option value="">Seleccionar sucursal</option>
                            <?php foreach ($sucursales as $sucursal): ?>
                            <option value="<?php echo $sucursal['id_sucursal']; ?>">
                                <?php echo htmlspecialchars($sucursal['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalEmpleado')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formEmpleado" class="btn save">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div id="modalDeleteEmpleado" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2 id="modalDeleteTitle">Confirmar Eliminación</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalDeleteEmpleado')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">¿Está seguro de que desea eliminar este empleado?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalDeleteEmpleado')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="formDelete" method="POST" style="display: inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_vendedor" id="deleteEmpleadoId">
                    <button type="submit" class="btn delete">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/heladeriacg/js/admin/script.js"></script>
    <script>
        function editarEmpleado(id) {
            fetch(`funcionalidades/obtener_empleado.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const emp = data.empleado;
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_vendedor').value = emp.id_vendedor;
                        document.getElementById('nombre').value = emp.nombre || '';
                        document.getElementById('email').value = emp.correo || '';
                        document.getElementById('telefono').value = emp.telefono || '';
                        document.getElementById('rol').value = emp.turno || '';
                        document.getElementById('id_sucursal').value = emp.id_sucursal || '';
                        document.getElementById('modalEmpleadoTitle').textContent = 'Editar Empleado';
                        openModal('modalEmpleado');
                    } else {
                        showNotification('Error al obtener empleado', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
        }

        function deleteItem(id, type, name) {
            document.getElementById('deleteEmpleadoId').value = id;
            document.getElementById('deleteMessage').textContent = 
                `¿Está seguro de que desea eliminar al empleado "${name}"? Esta acción no se puede deshacer.`;
            openModal('modalDeleteEmpleado');
        }

        // Reset form on modal open
        document.getElementById('modalEmpleado').addEventListener('click', function(e) {
            if (e.target === this || (e.target.closest('.modal-close'))) {
                if (document.getElementById('accionForm').value === 'crear') {
                    document.getElementById('formEmpleado').reset();
                    document.getElementById('modalEmpleadoTitle').textContent = 'Nuevo Empleado';
                }
            }
        });

        // Form submission
        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    </script>
</body>
</html>