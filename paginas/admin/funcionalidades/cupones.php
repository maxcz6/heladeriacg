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
                // Crear nuevo cupón
                $codigo = trim($_POST['codigo']);
                $descripcion = trim($_POST['descripcion']);
                $tipo = $_POST['tipo'];
                $valor = $_POST['valor'];
                $uso_maximo = $_POST['uso_maximo'];
                $usos_actuales = 0;
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO cupones (codigo, descripcion, tipo, valor, uso_maximo, usos_actuales, fecha_inicio, fecha_fin, activo) VALUES (:codigo, :descripcion, :tipo, :valor, :uso_maximo, :usos_actuales, :fecha_inicio, :fecha_fin, :activo)");
                $stmt->bindParam(':codigo', $codigo);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':uso_maximo', $uso_maximo);
                $stmt->bindParam(':usos_actuales', $usos_actuales);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':activo', $activo);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cupón creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear cupón';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                // Editar cupón existente
                $id_cupon = $_POST['id_cupon'];
                $codigo = trim($_POST['codigo']);
                $descripcion = trim($_POST['descripcion']);
                $tipo = $_POST['tipo'];
                $valor = $_POST['valor'];
                $uso_maximo = $_POST['uso_maximo'];
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE cupones SET codigo = :codigo, descripcion = :descripcion, tipo = :tipo, valor = :valor, uso_maximo = :uso_maximo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, activo = :activo WHERE id_cupon = :id_cupon");
                $stmt->bindParam(':codigo', $codigo);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':uso_maximo', $uso_maximo);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':activo', $activo);
                $stmt->bindParam(':id_cupon', $id_cupon);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cupón actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar cupón';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar cupón
                $id_cupon = $_POST['id_cupon'];
                
                $stmt = $pdo->prepare("DELETE FROM cupones WHERE id_cupon = :id_cupon");
                $stmt->bindParam(':id_cupon', $id_cupon);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cupón eliminado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar cupón';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener cupones
$stmt_cupones = $pdo->prepare("SELECT * FROM cupones ORDER BY fecha_inicio DESC");
$stmt_cupones->execute();
$cupones = $stmt_cupones->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando un cupón
$cupon_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM cupones WHERE id_cupon = :id_cupon");
    $stmt_editar->bindParam(':id_cupon', $id_editar);
    $stmt_editar->execute();
    $cupon_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cupones - Concelato Gelateria</title>
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
                    Concelato Gelateria - Cupones
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
                <h1>Gestión de Cupones</h1>
                <p>Aquí puedes administrar los cupones y códigos de descuento</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="cupones-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Cupón
                </button>
                <div class="search-filter">
                    <input type="text" id="searchCupon" placeholder="Buscar cupón..." onkeyup="searchCupones()">
                    <select id="filterStatus" onchange="filterCupones()">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>

            <!-- Formulario para crear/editar cupón -->
            <div id="cuponForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Cupón</h2>
                <form id="cuponFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_cupon" id="id_cupon" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo">Código del Cupón</label>
                            <input type="text" id="codigo" name="codigo" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo de Cupón</label>
                            <select id="tipo" name="tipo" required>
                                <option value="porcentaje">Porcentaje de Descuento</option>
                                <option value="monto">Monto de Descuento</option>
                                <option value="producto">Producto Gratis</option>
                                <option value="envio">Envío Gratis</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" id="valor" name="valor" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="uso_maximo">Usos Máximos (0 = ilimitado)</label>
                            <input type="number" id="uso_maximo" name="uso_maximo" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de Fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="activo">Estado</label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                            <label for="activo">Activo</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Cupón</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de cupones -->
            <div class="table-container">
                <table class="cupones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Usos</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuponesTable">
                        <?php foreach ($cupones as $cupon): ?>
                        <tr data-status="<?php echo $cupon['activo'] ? 'activo' : 'inactivo'; ?>">
                            <td><?php echo $cupon['id_cupon']; ?></td>
                            <td><strong><?php echo htmlspecialchars($cupon['codigo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cupon['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($cupon['tipo']); ?></td>
                            <td>
                                <?php 
                                if ($cupon['tipo'] === 'porcentaje') {
                                    echo $cupon['valor'] . '%';
                                } elseif ($cupon['tipo'] === 'monto') {
                                    echo 'S/. ' . $cupon['valor'];
                                } else {
                                    echo $cupon['valor'];
                                }
                                ?>
                            </td>
                            <td><?php echo $cupon['usos_actuales']; ?>/<?php echo $cupon['uso_maximo'] == 0 ? '∞' : $cupon['uso_maximo']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($cupon['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($cupon['fecha_fin'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $cupon['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $cupon['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn edit" onclick="editarCupon(<?php echo $cupon['id_cupon']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $cupon['id_cupon']; ?>, '<?php echo addslashes(htmlspecialchars($cupon['codigo'])); ?>')">
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
        // Inicializar fechas al día de hoy
        document.addEventListener('DOMContentLoaded', function() {
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_inicio').value = hoy;
            
            // Sumar un mes para la fecha de fin
            const unMesDespues = new Date();
            unMesDespues.setMonth(unMesDespues.getMonth() + 1);
            document.getElementById('fecha_fin').value = unMesDespues.toISOString().split('T')[0];
        });
        
        function showForm(accion) {
            document.getElementById('accionForm').value = accion;
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Cupón' : 'Editar Cupón';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('cuponFormulario').reset();
                document.getElementById('activo').checked = true;
                document.getElementById('id_cupon').value = '';
                
                // Inicializar fechas
                const hoy = new Date().toISOString().split('T')[0];
                document.getElementById('fecha_inicio').value = hoy;
                
                const unMesDespues = new Date();
                unMesDespues.setMonth(unMesDespues.getMonth() + 1);
                document.getElementById('fecha_fin').value = unMesDespues.toISOString().split('T')[0];
            }
            
            document.getElementById('cuponForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('cuponForm').style.display = 'none';
        }
        
        function editarCupon(id) {
            // Simular solicitud para editar (en una implementación real, usarías un archivo PHP)
            alert('Funcionalidad de edición de cupón. En una implementación real, se cargarían los datos desde el servidor.');
            showForm('editar');
            document.getElementById('id_cupon').value = id;
        }
        
        function confirmarEliminar(id, codigo) {
            if (confirm(`¿Estás seguro de que deseas eliminar el cupón "${codigo}"?`)) {
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
                idInput.name = 'id_cupon';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchCupones() {
            const input = document.getElementById('searchCupon');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#cuponesTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const codigoCell = row.cells[1].textContent.toLowerCase(); // Código
                const descripcionCell = row.cells[2].textContent.toLowerCase(); // Descripción
                
                if (codigoCell.includes(filter) || descripcionCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function filterCupones() {
            const filter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#cuponesTable tr');
            
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
    </script>
</body>
</html>