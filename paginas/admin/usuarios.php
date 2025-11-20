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
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $email = trim($_POST['email']);
                $rol = trim($_POST['rol']);
                
                if (empty($password)) {
                    $mensaje = 'La contraseña es requerida';
                    $tipo_mensaje = 'error';
                    break;
                }
                
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, id_role) VALUES (:username, :password, :id_role)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':id_role', $rol);

                if ($stmt->execute()) {
                    $id_usuario_creado = $pdo->lastInsertId();

                    // Si se proporciona un correo y hay una entidad relacionada, actualizarla
                    if (!empty($email)) {
                        // Verificar qué tipo de entidad se está asociando al usuario
                        $id_cliente = $_POST['id_cliente'] ?? null;
                        $id_vendedor = $_POST['id_vendedor'] ?? null;

                        if ($id_cliente) {
                            // Actualizar correo del cliente
                            $stmt_cliente = $pdo->prepare("UPDATE clientes SET correo = :correo WHERE id_cliente = :id_cliente");
                            $stmt_cliente->bindParam(':correo', $email);
                            $stmt_cliente->bindParam(':id_cliente', $id_cliente);
                            $stmt_cliente->execute();
                        } elseif ($id_vendedor) {
                            // Actualizar correo del vendedor
                            $stmt_vendedor = $pdo->prepare("UPDATE vendedores SET correo = :correo WHERE id_vendedor = :id_vendedor");
                            $stmt_vendedor->bindParam(':correo', $email);
                            $stmt_vendedor->bindParam(':id_vendedor', $id_vendedor);
                            $stmt_vendedor->execute();
                        }
                    }

                    $mensaje = 'Usuario creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                $id_usuario = $_POST['id_usuario'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $rol = trim($_POST['rol']);
                
                $update_parts = ["username = :username", "id_role = :id_role"];

                if (!empty($_POST['password'])) {
                    $update_parts[] = "password = :password";
                }

                $query = "UPDATE usuarios SET " . implode(", ", $update_parts) . " WHERE id_usuario = :id_usuario";

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':id_role', $rol);
                $stmt->bindParam(':id_usuario', $id_usuario);

                if (!empty($_POST['password'])) {
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt->bindParam(':password', $password_hash);
                }

                if ($stmt->execute()) {
                    // Si se proporciona un correo, intentar actualizar la tabla relacionada
                    if (!empty($email)) {
                        // Verificar a qué tipo de entidad está asociado el usuario
                        $check_usuario = $pdo->prepare("SELECT id_cliente, id_vendedor FROM usuarios WHERE id_usuario = :id_usuario");
                        $check_usuario->bindParam(':id_usuario', $id_usuario);
                        $check_usuario->execute();
                        $usuario_relacion = $check_usuario->fetch(PDO::FETCH_ASSOC);

                        if ($usuario_relacion) {
                            if ($usuario_relacion['id_cliente']) {
                                // Actualizar correo del cliente
                                $stmt_cliente = $pdo->prepare("UPDATE clientes SET correo = :correo WHERE id_cliente = :id_cliente");
                                $stmt_cliente->bindParam(':correo', $email);
                                $stmt_cliente->bindParam(':id_cliente', $usuario_relacion['id_cliente']);
                                $stmt_cliente->execute();
                            } elseif ($usuario_relacion['id_vendedor']) {
                                // Actualizar correo del vendedor
                                $stmt_vendedor = $pdo->prepare("UPDATE vendedores SET correo = :correo WHERE id_vendedor = :id_vendedor");
                                $stmt_vendedor->bindParam(':correo', $email);
                                $stmt_vendedor->bindParam(':id_vendedor', $usuario_relacion['id_vendedor']);
                                $stmt_vendedor->execute();
                            }
                        }
                    }

                    $mensaje = 'Usuario actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                $id_usuario = $_POST['id_usuario'];
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
                $stmt->bindParam(':id_usuario', $id_usuario);
                
                if ($stmt->execute()) {
                    $mensaje = 'Usuario eliminado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar usuario';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener usuarios con información de correo de clientes o vendedores
$stmt_usuarios = $pdo->prepare("
    SELECT u.*, r.nombre as rol_nombre,
           COALESCE(c.nombre, v.nombre) as nombre_relacionado,
           COALESCE(c.correo, v.correo) as correo
    FROM usuarios u
    LEFT JOIN roles r ON u.id_role = r.id_role
    LEFT JOIN clientes c ON u.id_cliente = c.id_cliente
    LEFT JOIN vendedores v ON u.id_vendedor = v.id_vendedor
    ORDER BY u.username
");
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Obtener roles
$stmt_roles = $pdo->prepare("SELECT id_role, nombre FROM roles ORDER BY nombre");
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/usuarios.css">
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
            <h1>Gestión de Usuarios</h1>
            <p>Administra los usuarios con acceso al sistema</p>
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
                    <button class="action-btn primary" data-action="create" onclick="openModal('modalUsuario')">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                    <div class="search-filter">
                        <input 
                            type="search" 
                            id="searchUsuario"
                            class="search-input"
                            placeholder="Buscar por usuario..."
                            aria-label="Buscar usuarios"
                            data-filter-table="tablaUsuarios">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-container">
            <table id="tablaUsuarios" class="usuarios-table" role="table">
                <thead>
                    <tr role="row">
                        <th role="columnheader" aria-sort="none">
                            <i class="fas fa-user"></i> Usuario
                        </th>
                        <th role="columnheader" aria-sort="none">
                            <i class="fas fa-envelope"></i> Email
                        </th>
                        <th role="columnheader" aria-sort="none">
                            <i class="fas fa-shield-alt"></i> Rol
                        </th>
                        <th role="columnheader" aria-label="Acciones">
                            <i class="fas fa-cogs"></i> Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr role="row" tabindex="0">
                            <td data-label="Usuario"><strong><?php echo htmlspecialchars($usuario['username']); ?></strong></td>
                            <td data-label="Email"><?php echo htmlspecialchars($usuario['correo'] ?? 'N/A'); ?></td>
                            <td data-label="Rol"><span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: rgba(8, 145, 178, 0.1); color: var(--primary-dark); font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($usuario['rol_nombre'] ?? 'N/A'); ?></span></td>
                            <td data-label="Acciones">
                                <button 
                                    class="action-btn edit" 
                                    onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)"
                                    title="Editar usuario"
                                    aria-label="Editar usuario <?php echo htmlspecialchars($usuario['username']); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button 
                                    class="action-btn delete" 
                                    onclick="deleteItem(<?php echo $usuario['id_usuario']; ?>, 'usuario', '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                    title="Eliminar usuario"
                                    aria-label="Eliminar usuario <?php echo htmlspecialchars($usuario['username']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">
                            <i class="fas fa-inbox"></i> No hay usuarios registrados. <a href="#" onclick="openModal('modalUsuario')">Crear uno</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal: Crear/Editar Usuario -->
    <div id="modalUsuario" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalUsuarioTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalUsuarioTitle"><i class="fas fa-user-plus"></i> Nuevo Usuario</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalUsuario')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formUsuario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="">

                    <div class="form-group">
                        <label for="username">Nombre de Usuario <span aria-label="requerido">*</span></label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            aria-required="true"
                            placeholder="usuario_admin">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span aria-label="requerido">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            aria-required="true"
                            placeholder="usuario@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña <span aria-label="requerido">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            required
                            aria-required="true"
                            placeholder="Contraseña segura">
                        <small class="form-help">Mínimo 8 caracteres, incluir números y símbolos</small>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol <span aria-label="requerido">*</span></label>
                        <select id="rol" name="rol" required aria-required="true">
                            <option value="">Seleccionar rol</option>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id_role']; ?>">
                                <?php echo htmlspecialchars($rol['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalUsuario')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formUsuario" class="btn save">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div id="modalDeleteUsuario" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2 id="modalDeleteTitle"><i class="fas fa-trash-alt"></i> Confirmar Eliminación</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalDeleteUsuario')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">¿Está seguro de que desea eliminar este usuario?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalDeleteUsuario')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="formDelete" method="POST" style="display: inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_usuario" id="deleteUsuarioId">
                    <button type="submit" class="btn delete">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/heladeriacg/js/admin/script.js"></script>
    <script>
        function editarUsuario(id) {
            fetch(`funcionalidades/obtener_usuario.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const usr = data.usuario;
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_usuario').value = usr.id_usuario;
                        document.getElementById('username').value = usr.username || '';
                        document.getElementById('email').value = usr.correo || '';
                        document.getElementById('password').value = '';
                        document.getElementById('password').required = false;
                        document.getElementById('rol').value = usr.id_role || '';
                        document.getElementById('modalUsuarioTitle').innerHTML = '<i class="fas fa-user-edit"></i> Editar Usuario';
                        openModal('modalUsuario');
                    } else {
                        showNotification('Error al obtener usuario', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
        }

        function deleteItem(id, type, name) {
            document.getElementById('deleteUsuarioId').value = id;
            document.getElementById('deleteMessage').textContent = 
                `¿Está seguro de que desea eliminar al usuario "${name}"? Esta acción no se puede deshacer.`;
            openModal('modalDeleteUsuario');
        }

        // Reset form on modal open
        document.getElementById('modalUsuario').addEventListener('click', function(e) {
            if (e.target === this || (e.target.closest('.modal-close'))) {
                if (document.getElementById('accionForm').value === 'crear') {
                    document.getElementById('formUsuario').reset();
                    document.getElementById('password').required = true;
                    document.getElementById('modalUsuarioTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario';
                }
            }
        });

        // Form submission
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });

        // Search functionality
        const searchInput = document.getElementById('searchUsuario');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#tablaUsuarios tbody tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const usuario = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const rol = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    
                    const matches = usuario.includes(searchValue) || 
                                  email.includes(searchValue) || 
                                  rol.includes(searchValue);
                    
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });
                
                // Show/hide empty state
                const tableBody = document.querySelector('#tablaUsuarios tbody');
                const emptyState = document.querySelector('.empty-state');
                if (emptyState) {
                    if (visibleCount === 0 && searchValue) {
                        emptyState.style.display = 'block';
                        emptyState.innerHTML = '<p><i class="fas fa-search"></i> No se encontraron usuarios que coincidan con "' + this.value + '"</p>';
                    } else if (rows.length === 0) {
                        emptyState.style.display = 'block';
                    } else {
                        emptyState.style.display = 'none';
                    }
                }
            });
        }
    </script>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
    <script src="../../js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>