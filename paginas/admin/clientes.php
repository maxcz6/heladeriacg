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
                // Crear nuevo cliente
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                $correo = trim($_POST['correo']);
                $nota = trim($_POST['nota']);
                
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, dni, telefono, direccion, correo, nota) VALUES (:nombre, :dni, :telefono, :direccion, :correo, :nota)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':nota', $nota);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cliente creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear cliente';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                // Editar cliente existente
                $id_cliente = $_POST['id_cliente'];
                $nombre = trim($_POST['nombre']);
                $dni = trim($_POST['dni']);
                $telefono = trim($_POST['telefono']);
                $direccion = trim($_POST['direccion']);
                $correo = trim($_POST['correo']);
                $nota = trim($_POST['nota']);
                
                $stmt = $pdo->prepare("UPDATE clientes SET nombre = :nombre, dni = :dni, telefono = :telefono, direccion = :direccion, correo = :correo, nota = :nota WHERE id_cliente = :id_cliente");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':nota', $nota);
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
                // Eliminar cliente
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

// Si se está editando un cliente
$cliente_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = :id_cliente");
    $stmt_editar->bindParam(':id_cliente', $id_editar);
    $stmt_editar->execute();
    $cliente_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
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
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Clientes
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="ventas.php"><i class="fas fa-chart-line"></i> Ventas</a></li>
                        <li><a href="empleados.php"><i class="fas fa-user-tie"></i> Empleados</a></li>
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
                <h1>Gestión de Clientes</h1>
                <p>Aquí puedes administrar los clientes de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="clientes-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Cliente
                </button>
                <div class="search-filter">
                    <input type="text" id="searchCliente" placeholder="Buscar cliente..." onkeyup="searchClientes()">
                </div>
            </div>

            <!-- Formulario para crear/editar cliente -->
            <div id="clienteForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Cliente</h2>
                <form id="clienteFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_cliente" id="id_cliente" value="">
                    
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
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion">
                    </div>
                    
                    <div class="form-group">
                        <label for="nota">Nota</label>
                        <textarea id="nota" name="nota" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Cliente</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de clientes -->
            <div class="table-container">
                <table class="clientes-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientesTable">
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo $cliente['id_cliente']; ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['dni']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['correo']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editarCliente(<?php echo $cliente['id_cliente']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $cliente['id_cliente']; ?>, '<?php echo addslashes(htmlspecialchars($cliente['nombre'])); ?>')">
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
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Cliente' : 'Editar Cliente';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('clienteFormulario').reset();
                document.getElementById('id_cliente').value = '';
            }
            
            document.getElementById('clienteForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('clienteForm').style.display = 'none';
        }
        
        function editarCliente(id) {
            // Obtener datos del cliente
            fetch(`obtener_cliente.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cliente = data.cliente;
                    
                    document.getElementById('id_cliente').value = cliente.id_cliente;
                    document.getElementById('nombre').value = cliente.nombre;
                    document.getElementById('dni').value = cliente.dni || '';
                    document.getElementById('telefono').value = cliente.telefono || '';
                    document.getElementById('direccion').value = cliente.direccion || '';
                    document.getElementById('correo').value = cliente.correo || '';
                    document.getElementById('nota').value = cliente.nota || '';
                    
                    document.getElementById('accionForm').value = 'editar';
                    document.getElementById('formTitle').textContent = 'Editar Cliente';
                    document.getElementById('clienteForm').style.display = 'block';
                } else {
                    alert('Error al obtener cliente: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener cliente');
            });
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar al cliente "${nombre}"?`)) {
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
                idInput.name = 'id_cliente';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchClientes() {
            const input = document.getElementById('searchCliente');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#clientesTable tr');
            
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
        <?php if ($cliente_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            editarCliente(<?php echo $cliente_editar['id_cliente']; ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>