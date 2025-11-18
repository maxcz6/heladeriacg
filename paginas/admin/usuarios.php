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
                // Crear nuevo usuario
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $id_role = $_POST['id_role'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                // Verificar que el username no exista
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username");
                $stmt_check->bindParam(':username', $username);
                $stmt_check->execute();
                
                if ($stmt_check->fetchColumn() > 0) {
                    $mensaje = 'El nombre de usuario ya existe';
                    $tipo_mensaje = 'error';
                    break;
                }
                
                // Encriptar la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, id_role, activo) VALUES (:username, :password, :id_role, :activo)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':id_role', $id_role);
                $stmt->bindParam(':activo', $activo);
                
                if ($stmt->execute()) {
                    $mensaje = 'Usuario creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                // Editar usuario existente
                $id_usuario = $_POST['id_usuario'];
                $username = trim($_POST['username']);
                $id_role = $_POST['id_role'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE usuarios SET username = :username, id_role = :id_role, activo = :activo WHERE id_usuario = :id_usuario");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':id_role', $id_role);
                $stmt->bindParam(':activo', $activo);
                $stmt->bindParam(':id_usuario', $id_usuario);
                
                if ($stmt->execute()) {
                    $mensaje = 'Usuario actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar usuario (desactivar)
                $id_usuario = $_POST['id_usuario'];
                
                $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = :id_usuario");
                $stmt->bindParam(':id_usuario', $id_usuario);
                
                if ($stmt->execute()) {
                    $mensaje = 'Usuario desactivado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al desactivar usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'cambiar_password':
                // Cambiar contraseña de usuario
                $id_usuario = $_POST['id_usuario'];
                $new_password = $_POST['new_password'];
                
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id_usuario = :id_usuario");
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':id_usuario', $id_usuario);
                
                if ($stmt->execute()) {
                    $mensaje = 'Contraseña actualizada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar contraseña';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener roles
$stmt_roles = $pdo->prepare("SELECT * FROM roles ORDER BY id_role");
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Obtener usuarios
$stmt_usuarios = $pdo->prepare("
    SELECT u.id_usuario, u.username, u.fecha_registro, u.activo, r.nombre as rol_nombre
    FROM usuarios u
    JOIN roles r ON u.id_role = r.id_role
    ORDER BY u.fecha_registro DESC
");
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando un usuario
$usuario_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("
        SELECT u.id_usuario, u.username, u.id_role, u.activo
        FROM usuarios u
        WHERE u.id_usuario = :id_usuario
    ");
    $stmt_editar->bindParam(':id_usuario', $id_editar);
    $stmt_editar->execute();
    $usuario_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Concelato Gelateria</title>
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
                    Concelato Gelateria - Usuarios
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
                        <li><a href="proveedores.php"><i class="fas fa-truck"></i> Proveedores</a></li>
                        <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Gestión de Usuarios</h1>
                <p>Aquí puedes administrar los usuarios y sus roles en el sistema</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="usuarios-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Usuario
                </button>
                <div class="search-filter">
                    <input type="text" id="searchUsuario" placeholder="Buscar usuario..." onkeyup="searchUsuarios()">
                    <select id="filterStatus" onchange="filterUsuarios()">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>

            <!-- Formulario para crear/editar usuario -->
            <div id="usuarioForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Usuario</h2>
                <form id="usuarioFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="">
                    
                    <div class="form-group">
                        <label for="username">Nombre de Usuario</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" id="password-group">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" required>
                            <small>Ingrese una contraseña segura</small>
                        </div>
                        <div class="form-group">
                            <label for="id_role">Rol</label>
                            <select id="id_role" name="id_role" required>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id_role']; ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group checkbox-group">
                            <label for="activo">Activo</label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Usuario</button>
                    </div>
                </form>
                
                <!-- Formulario para cambiar contraseña -->
                <div id="changePasswordForm" style="display: none; margin-top: 20px; padding: 20px; background: rgba(255, 255, 255, 0.3); border-radius: 10px;">
                    <h3>Cambiar Contraseña</h3>
                    <input type="hidden" name="accion" value="cambiar_password">
                    <input type="hidden" name="id_usuario_password" id="id_usuario_password" value="">
                    
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hidePasswordForm()">Cancelar</button>
                        <button type="button" class="btn save" onclick="changePassword()">Guardar Contraseña</button>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="table-container">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTable">
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr data-status="<?php echo $usuario['activo'] ? 'activo' : 'inactivo'; ?>">
                            <td><?php echo $usuario['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $usuario['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn edit" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn password" onclick="showPasswordForm(<?php echo $usuario['id_usuario']; ?>)">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $usuario['id_usuario']; ?>, '<?php echo addslashes(htmlspecialchars($usuario['username'])); ?>')">
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
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Usuario' : 'Editar Usuario';
            
            // Mostrar/ocultar grupo de contraseña según sea necesario
            document.getElementById('password-group').style.display = accion === 'crear' ? 'block' : 'none';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('usuarioFormulario').reset();
                document.getElementById('activo').checked = true;
                document.getElementById('id_usuario').value = '';
            }
            
            document.getElementById('usuarioForm').style.display = 'block';
            document.getElementById('changePasswordForm').style.display = 'none';
        }
        
        function hideForm() {
            document.getElementById('usuarioForm').style.display = 'none';
        }
        
        function hidePasswordForm() {
            document.getElementById('changePasswordForm').style.display = 'none';
        }
        
        function editarUsuario(id) {
            // Simular solicitud para editar (en una implementación real, usarías un archivo PHP)
            alert('Funcionalidad de edición de usuario. En una implementación real, se cargarían los datos desde el servidor.');
            showForm('editar');
            document.getElementById('id_usuario').value = id;
        }
        
        function showPasswordForm(id) {
            document.getElementById('id_usuario_password').value = id;
            document.getElementById('changePasswordForm').style.display = 'block';
            document.getElementById('usuarioForm').style.display = 'block';
        }
        
        function changePassword() {
            const id_usuario = document.getElementById('id_usuario_password').value;
            const new_password = document.getElementById('new_password').value;
            
            if (new_password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            // Enviar solicitud para cambiar contraseña
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'accion=cambiar_password&id_usuario=' + id_usuario + '&new_password=' + encodeURIComponent(new_password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Contraseña actualizada exitosamente');
                    hidePasswordForm();
                } else {
                    alert('Error al actualizar contraseña: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al actualizar contraseña');
            });
        }
        
        function confirmarEliminar(id, username) {
            if (confirm(`¿Estás seguro de que deseas desactivar al usuario "${username}"?`)) {
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
                idInput.name = 'id_usuario';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchUsuarios() {
            const input = document.getElementById('searchUsuario');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#usuariosTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const usernameCell = row.cells[1].textContent.toLowerCase(); // Username
                
                if (usernameCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function filterUsuarios() {
            const filter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#usuariosTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const status = row.getAttribute('data-status');
                
                if (filter === '' || status === filter) {
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
        <?php if ($usuario_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // En una implementación real, se usaría una función para cargar los datos
        });
        <?php endif; ?>
    </script>
</body>
</html>