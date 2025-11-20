<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $empresa = trim($_POST['empresa']);
                $contacto = trim($_POST['contacto']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("INSERT INTO proveedores (empresa, contacto, correo, telefono, direccion) VALUES (:empresa, :contacto, :correo, :telefono, :direccion)");
                $stmt->bindParam(':empresa', $empresa);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);
                
                if ($stmt->execute()) {
                    $mensaje = 'Proveedor creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear proveedor';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                $id_proveedor = $_POST['id_proveedor'];
                $empresa = trim($_POST['empresa']);
                $contacto = trim($_POST['contacto']);
                $email = trim($_POST['email']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("UPDATE proveedores SET empresa = :empresa, contacto = :contacto, correo = :correo, telefono = :telefono, direccion = :direccion WHERE id_proveedor = :id_proveedor");
                $stmt->bindParam(':empresa', $empresa);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':correo', $email);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':id_proveedor', $id_proveedor);
                
                if ($stmt->execute()) {
                    $mensaje = 'Proveedor actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar proveedor';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                $id_proveedor = $_POST['id_proveedor'];
                $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id_proveedor = :id_proveedor");
                $stmt->bindParam(':id_proveedor', $id_proveedor);
                
                if ($stmt->execute()) {
                    $mensaje = 'Proveedor eliminado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar proveedor';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener proveedores
$stmt_proveedores = $pdo->prepare("SELECT * FROM proveedores ORDER BY empresa");
$stmt_proveedores->execute();
$proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/proveedores.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación -->
    <!-- Header con navegación -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main content -->
    <main class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Gestión de Proveedores</h1>
            <p>Administra los proveedores de productos para la heladería</p>
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
                <div class="proveedores-actions">
                    <button class="action-btn primary" data-action="create" onclick="openModal('modalProveedor')">
                        <i class="fas fa-plus"></i> Nuevo Proveedor
                    </button>
                    <div class="search-filter">
                        <input 
                            type="search" 
                            id="searchProveedor"
                            class="search-input"
                            placeholder="Buscar por empresa o contacto..."
                            aria-label="Buscar proveedores"
                            data-filter-table="tablaProveedores">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-container">
                    <table id="tablaProveedores" class="proveedores-table" role="table">
                        <thead>
                            <tr role="row">
                                <th role="columnheader" aria-sort="none">
                                    <i class="fas fa-building"></i> Empresa
                                </th>
                                <th role="columnheader" aria-sort="none">
                                    <i class="fas fa-user"></i> Contacto
                                </th>
                                <th role="columnheader" aria-sort="none">
                                    <i class="fas fa-envelope"></i> Email
                                </th>
                                <th role="columnheader" aria-sort="none">
                                    <i class="fas fa-phone"></i> Teléfono
                                </th>
                                <th role="columnheader" aria-sort="none">
                                    <i class="fas fa-map-marker-alt"></i> Dirección
                                </th>
                                <th role="columnheader" aria-label="Acciones">
                                    <i class="fas fa-cogs"></i> Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                            <tr role="row" tabindex="0">
                                <td data-label="Empresa"><strong><?php echo htmlspecialchars($proveedor['empresa']); ?></strong></td>
                                <td data-label="Contacto"><?php echo htmlspecialchars($proveedor['contacto'] ?? 'N/A'); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($proveedor['correo'] ?? 'N/A'); ?></td>
                                <td data-label="Teléfono"><?php echo htmlspecialchars($proveedor['telefono'] ?? 'N/A'); ?></td>
                                <td data-label="Dirección"><?php echo htmlspecialchars($proveedor['direccion'] ?? 'N/A'); ?></td>
                                <td data-label="Acciones">
                                    <button 
                                        class="action-btn edit" 
                                        onclick="editarProveedor(<?php echo $proveedor['id_proveedor']; ?>)"
                                        title="Editar proveedor"
                                        aria-label="Editar proveedor <?php echo htmlspecialchars($proveedor['empresa']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        class="action-btn delete" 
                                        onclick="deleteItem(<?php echo $proveedor['id_proveedor']; ?>, 'proveedor', '<?php echo htmlspecialchars($proveedor['empresa']); ?>')"
                                        title="Eliminar proveedor"
                                        aria-label="Eliminar proveedor <?php echo htmlspecialchars($proveedor['empresa']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($proveedores)): ?>
                    <div class="empty-state">
                        <p><i class="fas fa-inbox" style="font-size: 2rem; color: var(--gray); margin-bottom: 10px; display: block;"></i> No hay proveedores registrados. <a href="#" onclick="openModal('modalProveedor')">Crear uno</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Crear/Editar Proveedor -->
    <div id="modalProveedor" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalProveedorTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalProveedorTitle"><i class="fas fa-building"></i> Nuevo Proveedor</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalProveedor')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProveedor" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_proveedor" id="id_proveedor" value="">

                    <div class="form-group">
                        <label for="empresa">Nombre de Empresa <span aria-label="requerido">*</span></label>
                        <input 
                            type="text" 
                            id="empresa" 
                            name="empresa" 
                            required
                            aria-required="true"
                            placeholder="Ej: Distribuidora XYZ">
                    </div>

                    <div class="form-group">
                        <label for="contacto">Persona de Contacto <span aria-label="requerido">*</span></label>
                        <input 
                            type="text" 
                            id="contacto" 
                            name="contacto" 
                            required
                            aria-required="true"
                            placeholder="Ej: Juan Pérez">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span aria-label="requerido">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            aria-required="true"
                            placeholder="correo@empresa.com">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono <span aria-label="requerido">*</span></label>
                        <input 
                            type="tel" 
                            id="telefono" 
                            name="telefono"
                            required
                            aria-required="true"
                            placeholder="+51 999 999 999">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input 
                            type="text" 
                            id="direccion" 
                            name="direccion"
                            placeholder="Calle Principal 456">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalProveedor')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formProveedor" class="btn save">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div id="modalDeleteProveedor" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2 id="modalDeleteTitle"><i class="fas fa-trash-alt"></i> Confirmar Eliminación</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalDeleteProveedor')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">¿Está seguro de que desea eliminar este proveedor?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalDeleteProveedor')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="formDelete" method="POST" style="display: inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_proveedor" id="deleteProveedorId">
                    <button type="submit" class="btn delete">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/heladeriacg/js/admin/script.js"></script>
    <script>
        function editarProveedor(id) {
            fetch(`funcionalidades/obtener_proveedor.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const prov = data.proveedor;
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_proveedor').value = prov.id_proveedor;
                        document.getElementById('empresa').value = prov.empresa || '';
                        document.getElementById('contacto').value = prov.contacto || '';
                        document.getElementById('email').value = prov.correo || '';
                        document.getElementById('telefono').value = prov.telefono || '';
                        document.getElementById('direccion').value = prov.direccion || '';
                        document.getElementById('modalProveedorTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Proveedor';
                        openModal('modalProveedor');
                    } else {
                        showNotification('Error al obtener proveedor', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
        }

        function deleteItem(id, type, name) {
            document.getElementById('deleteProveedorId').value = id;
            document.getElementById('deleteMessage').textContent = 
                `¿Está seguro de que desea eliminar al proveedor "${name}"? Esta acción no se puede deshacer.`;
            openModal('modalDeleteProveedor');
        }

        // Reset form on modal open
        document.getElementById('modalProveedor').addEventListener('click', function(e) {
            if (e.target === this || (e.target.closest('.modal-close'))) {
                if (document.getElementById('accionForm').value === 'crear') {
                    document.getElementById('formProveedor').reset();
                    document.getElementById('modalProveedorTitle').innerHTML = '<i class="fas fa-building"></i> Nuevo Proveedor';
                }
            }
        });

        // Form submission
        document.getElementById('formProveedor').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    </script>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>