<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nueva sucursal
                $datos = [
                    'nombre' => trim($_POST['nombre']),
                    'direccion' => trim($_POST['direccion']),
                    'telefono' => trim($_POST['telefono']),
                    'correo' => trim($_POST['correo']),
                    'horario' => trim($_POST['horario'])
                ];

                $resultado = crearSucursal($datos);

                if ($resultado['success']) {
                    $mensaje = 'Sucursal creada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = $resultado['message'];
                    $tipo_mensaje = 'error';
                }
                break;

            case 'editar':
                // Editar sucursal existente
                $id_sucursal = $_POST['id_sucursal'];
                $datos = [
                    'nombre' => trim($_POST['nombre']),
                    'direccion' => trim($_POST['direccion']),
                    'telefono' => trim($_POST['telefono']),
                    'correo' => trim($_POST['correo']),
                    'horario' => trim($_POST['horario'])
                ];

                $resultado = actualizarSucursal($id_sucursal, $datos);

                if ($resultado['success']) {
                    $mensaje = 'Sucursal actualizada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = $resultado['message'];
                    $tipo_mensaje = 'error';
                }
                break;

            case 'eliminar':
                // Eliminar (desactivar) sucursal
                $id_sucursal = $_POST['id_sucursal'];

                $resultado = eliminarSucursal($id_sucursal);

                if ($resultado['success']) {
                    $mensaje = 'Sucursal desactivada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = $resultado['message'];
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener todas las sucursales
$sucursales = obtenerSucursales();

// Si se está editando una sucursal
$sucursal_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sucursal_editar = obtenerSucursalPorId($id_editar);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sucursales - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/sucursales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="sucursales.php" class="active">
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

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Gestión de Sucursales</h1>
                <p>Aquí puedes administrar las sucursales de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="sucursales-actions">
                <button class="action-btn primary" onclick="openSucursalModal()" style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); color: white; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Agregar Sucursal
                </button>
                <div class="search-filter">
                    <input type="text" id="searchSucursal" placeholder="Buscar sucursal..." onkeyup="searchSucursales()">
                </div>
            </div>

            <!-- Modal: Crear/Editar Sucursal -->
            <div id="sucursalFormModal" class="sucursal-modal-overlay">
                <div class="sucursal-modal-content">
                    <div class="sucursal-modal-header">
                        <h2 id="modalTitle">Agregar Sucursal</h2>
                        <button class="sucursal-modal-close" onclick="closeSucursalModal()" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="sucursal-modal-body">
                        <form id="sucursalFormulario" method="POST" class="sucursal-form">
                            <input type="hidden" name="accion" id="accionForm" value="crear">
                            <input type="hidden" name="id_sucursal" id="id_sucursal" value="">
                            
                            <!-- Información General -->
                            <div class="form-section">
                                <label class="form-section-title">Información Básica</label>
                                <div class="form-group full">
                                    <label for="nombre">
                                        Nombre de la Sucursal
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="nombre" name="nombre" required aria-required="true" placeholder="Ej: Sucursal Centro">
                                </div>
                                <div class="form-group full">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" name="direccion" placeholder="Ej: Calle Principal 123">
                                </div>
                            </div>

                            <!-- Contacto -->
                            <div class="form-section">
                                <label class="form-section-title">Contacto</label>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="tel" id="telefono" name="telefono" placeholder="Ej: +51 999 999 999">
                                    </div>
                                    <div class="form-group">
                                        <label for="correo">Email</label>
                                        <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Horario -->
                            <div class="form-section">
                                <label class="form-section-title">Horario</label>
                                <div class="form-group full">
                                    <label for="horario">Horario de Atención</label>
                                    <input type="text" id="horario" name="horario" placeholder="Ej: 8:00 AM - 10:00 PM">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="sucursal-modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeSucursalModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" form="sucursalFormulario" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Sucursal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de sucursales -->
            <div class="table-container">
                <table class="sucursales-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Horario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="sucursalesTable">
                        <?php if (!empty($sucursales)): ?>
                            <?php foreach ($sucursales as $sucursal): ?>
                            <tr data-id="<?php echo $sucursal['id_sucursal']; ?>">
                                <td><strong><?php echo htmlspecialchars($sucursal['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sucursal['direccion'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($sucursal['telefono'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($sucursal['correo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($sucursal['horario'] ?? '-'); ?></td>
                                <td>
                                    <button class="action-btn edit" onclick="editarSucursal(<?php echo $sucursal['id_sucursal']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $sucursal['id_sucursal']; ?>, '<?php echo addslashes(htmlspecialchars($sucursal['nombre'])); ?>')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    No hay sucursales registradas. <a href="#" onclick="openSucursalModal(); return false;">Crear una</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Funciones para manejar el modal
        function openSucursalModal() {
            document.getElementById('accionForm').value = 'crear';
            document.getElementById('id_sucursal').value = '';
            document.getElementById('modalTitle').textContent = 'Agregar Sucursal';
            document.getElementById('sucursalFormulario').reset();
            document.getElementById('sucursalFormModal').classList.add('active');
        }
        
        function closeSucursalModal() {
            document.getElementById('sucursalFormModal').classList.remove('active');
        }
        
        // Cerrar modal al hacer click fuera
        document.getElementById('sucursalFormModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSucursalModal();
            }
        });
        
        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSucursalModal();
            }
        });
        
        function editarSucursal(id) {
            fetch(`funcionalidades/obtener_sucursal.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sucursal = data.sucursal;
                        
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_sucursal').value = sucursal.id_sucursal;
                        document.getElementById('nombre').value = sucursal.nombre || '';
                        document.getElementById('direccion').value = sucursal.direccion || '';
                        document.getElementById('telefono').value = sucursal.telefono || '';
                        document.getElementById('correo').value = sucursal.correo || '';
                        document.getElementById('horario').value = sucursal.horario || '';
                        document.getElementById('modalTitle').textContent = 'Editar Sucursal';
                        document.getElementById('sucursalFormModal').classList.add('active');
                    } else {
                        alert('Error al obtener sucursal: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar la sucursal "${nombre}"? Esta acción no se puede deshacer.`)) {
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
                idInput.name = 'id_sucursal';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchSucursales() {
            const input = document.getElementById('searchSucursal');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#sucursalesTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                if (row.cells.length < 2) continue;
                const nombreCell = row.cells[1].textContent.toLowerCase();
                const direccionCell = row.cells[2].textContent.toLowerCase();
                
                if (nombreCell.includes(filter) || direccionCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
</body>
</html>