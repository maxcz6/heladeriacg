<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Para el header
$current_page = 'clientes';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, telefono, correo, direccion) VALUES (:nombre, :telefono, :correo, :direccion)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':direccion', $direccion);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cliente creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear cliente';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                $id_cliente = $_POST['id_cliente'];
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("UPDATE clientes SET nombre = :nombre, telefono = :telefono, correo = :correo, direccion = :direccion WHERE id_cliente = :id_cliente");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':id_cliente', $id_cliente);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cliente actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar cliente';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                $id_cliente = $_POST['id_cliente'];
                $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = :id_cliente");
                $stmt->bindParam(':id_cliente', $id_cliente);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cliente eliminado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar cliente';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener clientes
$stmt_clientes = $pdo->prepare("SELECT * FROM clientes ORDER BY nombre");
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Concelato Gelateria</title>
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
                <a href="empleados.php">
                    <i class="fas fa-users"></i> <span>Empleados</span>
                </a>
                <a href="clientes.php" class="active">
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
            <h1>Gestión de Clientes</h1>
            <p>Administra los clientes registrados en el sistema</p>
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
                <div class="clientes-actions">
                    <button class="action-btn primary" data-action="create" onclick="openModal('modalCliente')">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                    <div class="search-filter">
                        <input 
                            type="search" 
                            id="searchCliente"
                            class="search-input"
                            placeholder="Buscar por nombre o email..."
                            aria-label="Buscar clientes"
                            data-filter-table="tablaClientes">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaClientes" class="tabla-admin" role="table">
                        <thead>
                            <tr role="row">
                                <th role="columnheader" aria-sort="none" onclick="TableSorter.sortTable(this)">
                                    <i class="fas fa-arrows-alt-v"></i> Nombre
                                </th>
                                <th role="columnheader" aria-sort="none">Email</th>
                                <th role="columnheader" aria-sort="none">Teléfono</th>
                                <th role="columnheader" aria-sort="none">Dirección</th>
                                <th role="columnheader" aria-sort="none">Fecha Registro</th>
                                <th role="columnheader" aria-label="Acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                            <tr role="row" tabindex="0">
                                <td><strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cliente['correo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['direccion'] ?? 'N/A'); ?></td>
                                <td><span class="date"><?php echo isset($cliente['fecha_registro']) ? date('d/m/Y', strtotime($cliente['fecha_registro'])) : 'N/A'; ?></span></td>
                                <td>
                                    <button 
                                        class="action-btn edit" 
                                        onclick="editarCliente(<?php echo $cliente['id_cliente']; ?>)"
                                        aria-label="Editar cliente <?php echo htmlspecialchars($cliente['nombre']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        class="action-btn delete" 
                                        onclick="deleteItem(<?php echo $cliente['id_cliente']; ?>, 'cliente', '<?php echo htmlspecialchars($cliente['nombre']); ?>')"
                                        aria-label="Eliminar cliente <?php echo htmlspecialchars($cliente['nombre']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($clientes)): ?>
                    <div class="empty-state">
                        <p>No hay clientes registrados. <a href="#" onclick="openModal('modalCliente')">Crear uno</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Crear/Editar Cliente -->
    <div id="modalCliente" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalClienteTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalClienteTitle">Nuevo Cliente</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalCliente')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCliente" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_cliente" id="id_cliente" value="">

                    <div class="form-group">
                        <label for="nombre">Nombre Completo <span aria-label="requerido">*</span></label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required
                            aria-required="true"
                            placeholder="Ej: María García">
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
                        <label for="direccion">Dirección</label>
                        <input 
                            type="text" 
                            id="direccion" 
                            name="direccion"
                            placeholder="Calle Principal 123, Piso 2">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalCliente')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formCliente" class="btn save">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div id="modalDeleteCliente" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2 id="modalDeleteTitle">Confirmar Eliminación</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalDeleteCliente')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">¿Está seguro de que desea eliminar este cliente?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalDeleteCliente')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="formDelete" method="POST" style="display: inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_cliente" id="deleteClienteId">
                    <button type="submit" class="btn delete">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/heladeriacg/js/admin/script.js"></script>
    <script>
        function editarCliente(id) {
            fetch(`funcionalidades/obtener_cliente.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cli = data.cliente;
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_cliente').value = cli.id_cliente;
                        document.getElementById('nombre').value = cli.nombre || '';
                        document.getElementById('email').value = cli.correo || '';
                        document.getElementById('telefono').value = cli.telefono || '';
                        document.getElementById('direccion').value = cli.direccion || '';
                        document.getElementById('modalClienteTitle').textContent = 'Editar Cliente';
                        openModal('modalCliente');
                    } else {
                        showNotification('Error al obtener cliente', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
        }

        function deleteItem(id, type, name) {
            document.getElementById('deleteClienteId').value = id;
            document.getElementById('deleteMessage').textContent = 
                `¿Está seguro de que desea eliminar al cliente "${name}"? Esta acción no se puede deshacer.`;
            openModal('modalDeleteCliente');
        }

        // Reset form on modal open
        document.getElementById('modalCliente').addEventListener('click', function(e) {
            if (e.target === this || (e.target.closest('.modal-close'))) {
                if (document.getElementById('accionForm').value === 'crear') {
                    document.getElementById('formCliente').reset();
                    document.getElementById('modalClienteTitle').textContent = 'Nuevo Cliente';
                }
            }
        });

        // Form submission
        document.getElementById('formCliente').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    </script>
</body>
</html>