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
                // Crear nuevo empleado
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $turno = trim($_POST['turno']);
                
                $stmt = $pdo->prepare("INSERT INTO vendedores (nombre, dni, telefono, correo, turno) VALUES (:nombre, :dni, :telefono, :correo, :turno)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':turno', $turno);
                
                if ($stmt->execute()) {
                    // Crear usuario para el empleado
                    $id_vendedor = $pdo->lastInsertId();
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
                break;
                
            case 'editar':
                // Editar empleado existente
                $id_vendedor = $_POST['id_vendedor'];
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $correo = trim($_POST['correo']);
                $turno = trim($_POST['turno']);
                
                $stmt = $pdo->prepare("UPDATE vendedores SET nombre = :nombre, dni = :dni, telefono = :telefono, correo = :correo, turno = :turno WHERE id_vendedor = :id_vendedor");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':turno', $turno);
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
$stmt_empleados = $pdo->prepare("SELECT * FROM vendedores ORDER BY nombre");
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando un empleado
$empleado_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM vendedores WHERE id_vendedor = :id_vendedor");
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
    <title>Gestión de Empleados - Concelato Gelateria</title>
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
                <h1>Gestión de Empleados</h1>
                <p>Aquí puedes administrar los empleados de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="empleados-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Empleado
                </button>
                <div class="search-filter">
                    <input type="text" id="searchEmpleado" placeholder="Buscar empleado..." onkeyup="searchEmpleados()">
                </div>
            </div>

            <!-- Formulario para crear/editar empleado -->
            <div id="empleadoForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Empleado</h2>
                <form id="empleadoFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_vendedor" id="id_vendedor" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" maxlength="12">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono">
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo">
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
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Empleado</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de empleados -->
            <div class="table-container">
                <table class="empleados-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Turno</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="empleadosTable">
                        <?php foreach ($empleados as $empleado): ?>
                        <tr>
                            <td><?php echo $empleado['id_vendedor']; ?></td>
                            <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['dni']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['correo']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['turno']); ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editarEmpleado(<?php echo $empleado['id_vendedor']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $empleado['id_vendedor']; ?>, '<?php echo addslashes(htmlspecialchars($empleado['nombre'])); ?>')">
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
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Empleado' : 'Editar Empleado';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('empleadoFormulario').reset();
                document.getElementById('turno').value = 'Mañana';
                document.getElementById('id_vendedor').value = '';
            }
            
            document.getElementById('empleadoForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('empleadoForm').style.display = 'none';
        }
        
        function editarEmpleado(id) {
            // Obtener datos del empleado
            fetch(`obtener_empleado.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const empleado = data.empleado;
                    
                    document.getElementById('id_vendedor').value = empleado.id_vendedor;
                    document.getElementById('nombre').value = empleado.nombre;
                    document.getElementById('dni').value = empleado.dni || '';
                    document.getElementById('telefono').value = empleado.telefono || '';
                    document.getElementById('correo').value = empleado.correo || '';
                    document.getElementById('turno').value = empleado.turno || 'Mañana';
                    
                    document.getElementById('accionForm').value = 'editar';
                    document.getElementById('formTitle').textContent = 'Editar Empleado';
                    document.getElementById('empleadoForm').style.display = 'block';
                } else {
                    alert('Error al obtener empleado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener empleado');
            });
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar al empleado "${nombre}"?`)) {
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
                idInput.name = 'id_vendedor';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchEmpleados() {
            const input = document.getElementById('searchEmpleado');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#empleadosTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const nombreCell = row.cells[1].textContent.toLowerCase(); // Nombre
                const dniCell = row.cells[2].textContent.toLowerCase(); // DNI
                
                if (nombreCell.includes(filter) || dniCell.includes(filter)) {
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
        
        // Cargar datos para edición si se pasó un ID
        <?php if ($empleado_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            editarEmpleado(<?php echo $empleado_editar['id_vendedor']; ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>