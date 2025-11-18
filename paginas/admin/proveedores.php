<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Manejar operaciones CRUD
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo proveedor
                $empresa = trim($_POST['empresa']);
                $contacto = trim($_POST['contacto']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("INSERT INTO proveedores (empresa, contacto, telefono, correo, direccion) VALUES (:empresa, :contacto, :telefono, :correo, :direccion)");
                $stmt->bindParam(':empresa', $empresa);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
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
                // Editar proveedor existente
                $id_proveedor = $_POST['id_proveedor'];
                $empresa = trim($_POST['empresa']);
                $contacto = trim($_POST['contacto']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $direccion = trim($_POST['direccion']);
                
                $stmt = $pdo->prepare("UPDATE proveedores SET empresa = :empresa, contacto = :contacto, telefono = :telefono, correo = :correo, direccion = :direccion WHERE id_proveedor = :id_proveedor");
                $stmt->bindParam(':empresa', $empresa);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
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
                // Eliminar proveedor
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

// Si se está editando un proveedor
$proveedor_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM proveedores WHERE id_proveedor = :id_proveedor");
    $stmt_editar->bindParam(':id_proveedor', $id_editar);
    $stmt_editar->execute();
    $proveedor_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - Concelato Gelateria</title>
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
                    Concelato Gelateria - Proveedores
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
                <h1>Gestión de Proveedores</h1>
                <p>Aquí puedes administrar los proveedores de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="proveedores-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Proveedor
                </button>
                <div class="search-filter">
                    <input type="text" id="searchProveedor" placeholder="Buscar proveedor..." onkeyup="searchProveedores()">
                </div>
            </div>

            <!-- Formulario para crear/editar proveedor -->
            <div id="proveedorForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Proveedor</h2>
                <form id="proveedorFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_proveedor" id="id_proveedor" value="">
                    
                    <div class="form-group">
                        <label for="empresa">Nombre de la Empresa</label>
                        <input type="text" id="empresa" name="empresa" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contacto">Persona de Contacto</label>
                            <input type="text" id="contacto" name="contacto" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo">
                        </div>
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Proveedor</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de proveedores -->
            <div class="table-container">
                <table class="proveedores-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="proveedoresTable">
                        <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td><?php echo $proveedor['id_proveedor']; ?></td>
                            <td><?php echo htmlspecialchars($proveedor['empresa']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['contacto']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['correo']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['direccion']); ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editarProveedor(<?php echo $proveedor['id_proveedor']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $proveedor['id_proveedor']; ?>, '<?php echo addslashes(htmlspecialchars($proveedor['empresa'])); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function showForm(accion) {
            document.getElementById('accionForm').value = accion;
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Proveedor' : 'Editar Proveedor';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('proveedorFormulario').reset();
                document.getElementById('id_proveedor').value = '';
            }
            
            document.getElementById('proveedorForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('proveedorForm').style.display = 'none';
        }
        
        function editarProveedor(id) {
            // En una implementación real, se obtendrían los datos del proveedor
            // Por ahora, simplemente se muestra el formulario de edición
            alert('Funcionalidad de edición de proveedor. En una implementación real, se cargarían los datos desde el servidor.');
            showForm('editar');
            document.getElementById('id_proveedor').value = id;
        }
        
        function confirmarEliminar(id, empresa) {
            if (confirm(`¿Estás seguro de que deseas eliminar al proveedor "${empresa}"?`)) {
                // Enviar formulario para eliminar
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
                idInput.name = 'id_proveedor';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchProveedores() {
            const input = document.getElementById('searchProveedor');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#proveedoresTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const empresaCell = row.cells[1].textContent.toLowerCase(); // Empresa
                const contactoCell = row.cells[2].textContent.toLowerCase(); // Contacto
                
                if (empresaCell.includes(filter) || contactoCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>