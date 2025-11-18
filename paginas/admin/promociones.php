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
                // Crear nueva promoción
                $titulo = trim($_POST['titulo']);
                $descripcion = trim($_POST['descripcion']);
                $tipo = $_POST['tipo'];
                $valor = $_POST['valor'];
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO promociones (titulo, descripcion, tipo, valor, fecha_inicio, fecha_fin, activo) VALUES (:titulo, :descripcion, :tipo, :valor, :fecha_inicio, :fecha_fin, :activo)");
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':activo', $activo);
                
                if ($stmt->execute()) {
                    $mensaje = 'Promoción creada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear promoción';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'editar':
                // Editar promoción existente
                $id_promocion = $_POST['id_promocion'];
                $titulo = trim($_POST['titulo']);
                $descripcion = trim($_POST['descripcion']);
                $tipo = $_POST['tipo'];
                $valor = $_POST['valor'];
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $activo = isset($_POST['activo']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE promociones SET titulo = :titulo, descripcion = :descripcion, tipo = :tipo, valor = :valor, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, activo = :activo WHERE id_promocion = :id_promocion");
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':activo', $activo);
                $stmt->bindParam(':id_promocion', $id_promocion);
                
                if ($stmt->execute()) {
                    $mensaje = 'Promoción actualizada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar promoción';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar promoción
                $id_promocion = $_POST['id_promocion'];
                
                $stmt = $pdo->prepare("DELETE FROM promociones WHERE id_promocion = :id_promocion");
                $stmt->bindParam(':id_promocion', $id_promocion);
                
                if ($stmt->execute()) {
                    $mensaje = 'Promoción eliminada exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar promoción';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener promociones
$stmt_promociones = $pdo->prepare("SELECT * FROM promociones ORDER BY fecha_inicio DESC");
$stmt_promociones->execute();
$promociones = $stmt_promociones->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando una promoción
$promocion_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt_editar = $pdo->prepare("SELECT * FROM promociones WHERE id_promocion = :id_promocion");
    $stmt_editar->bindParam(':id_promocion', $id_editar);
    $stmt_editar->execute();
    $promocion_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones - Concelato Gelateria</title>
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
                    Concelato Gelateria - Promociones
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
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
                <h1>Gestión de Promociones</h1>
                <p>Aquí puedes administrar las promociones y descuentos de la heladería</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="promociones-actions">
                <button class="action-btn primary" onclick="showForm('crear')">
                    <i class="fas fa-plus"></i> Agregar Promoción
                </button>
                <div class="search-filter">
                    <input type="text" id="searchPromocion" placeholder="Buscar promoción..." onkeyup="searchPromociones()">
                    <select id="filterStatus" onchange="filterPromociones()">
                        <option value="">Todas</option>
                        <option value="activo">Activas</option>
                        <option value="inactivo">Inactivas</option>
                    </select>
                </div>
            </div>

            <!-- Formulario para crear/editar promoción -->
            <div id="promocionForm" class="form-container" style="display: none;">
                <h2 id="formTitle">Agregar Promoción</h2>
                <form id="promocionFormulario" method="POST">
                    <input type="hidden" name="accion" id="accionForm" value="crear">
                    <input type="hidden" name="id_promocion" id="id_promocion" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título</label>
                            <input type="text" id="titulo" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo de Promoción</label>
                            <select id="tipo" name="tipo" required>
                                <option value="porcentaje">Porcentaje de Descuento</option>
                                <option value="monto">Monto de Descuento</option>
                                <option value="producto">Producto Gratis</option>
                                <option value="compra">2x1 o Promoción de Compra</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" id="valor" name="valor" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="activo">Estado</label>
                            <input type="checkbox" id="activo" name="activo" value="1" checked>
                            <label for="activo">Activo</label>
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
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="hideForm()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Promoción</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de promociones -->
            <div class="table-container">
                <table class="promociones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="promocionesTable">
                        <?php foreach ($promociones as $promocion): ?>
                        <tr data-status="<?php echo $promocion['activo'] ? 'activo' : 'inactivo'; ?>">
                            <td><?php echo $promocion['id_promocion']; ?></td>
                            <td><?php echo htmlspecialchars($promocion['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($promocion['tipo']); ?></td>
                            <td>
                                <?php 
                                if ($promocion['tipo'] === 'porcentaje') {
                                    echo $promocion['valor'] . '%';
                                } elseif ($promocion['tipo'] === 'monto') {
                                    echo 'S/. ' . $promocion['valor'];
                                } else {
                                    echo $promocion['valor'];
                                }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $promocion['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $promocion['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn edit" onclick="editarPromocion(<?php echo $promocion['id_promocion']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $promocion['id_promocion']; ?>, '<?php echo addslashes(htmlspecialchars($promocion['titulo'])); ?>')">
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
            document.getElementById('formTitle').textContent = accion === 'crear' ? 'Agregar Promoción' : 'Editar Promoción';
            
            // Limpiar formulario
            if (accion === 'crear') {
                document.getElementById('promocionFormulario').reset();
                document.getElementById('activo').checked = true;
                document.getElementById('id_promocion').value = '';
                
                // Inicializar fechas
                const hoy = new Date().toISOString().split('T')[0];
                document.getElementById('fecha_inicio').value = hoy;
                
                const unMesDespues = new Date();
                unMesDespues.setMonth(unMesDespues.getMonth() + 1);
                document.getElementById('fecha_fin').value = unMesDespues.toISOString().split('T')[0];
            }
            
            document.getElementById('promocionForm').style.display = 'block';
        }
        
        function hideForm() {
            document.getElementById('promocionForm').style.display = 'none';
        }
        
        function editarPromocion(id) {
            // Simular solicitud para editar (en una implementación real, usarías un archivo PHP)
            alert('Funcionalidad de edición de promoción. En una implementación real, se cargarían los datos desde el servidor.');
            showForm('editar');
            document.getElementById('id_promocion').value = id;
        }
        
        function confirmarEliminar(id, titulo) {
            if (confirm(`¿Estás seguro de que deseas eliminar la promoción "${titulo}"?`)) {
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
                idInput.name = 'id_promocion';
                idInput.value = id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchPromociones() {
            const input = document.getElementById('searchPromocion');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#promocionesTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const tituloCell = row.cells[1].textContent.toLowerCase(); // Título
                
                if (tituloCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function filterPromociones() {
            const filter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#promocionesTable tr');
            
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
        <?php if ($promocion_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // En una implementación real, se usaría una función para cargar los datos
        });
        <?php endif; ?>
    </script>
</body>
</html>