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
                // Crear nueva promoción (según estructura real de BD)
                $id_producto = intval($_POST['id_producto']);
                $descuento = floatval($_POST['descuento']);
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $descripcion = trim($_POST['descripcion'] ?? '');
                $tipo_promocion = trim($_POST['tipo_promocion'] ?? 'descuento');
                $activa = isset($_POST['activa']) ? 1 : 0;
                
                if (empty($id_producto) || empty($descuento) || empty($fecha_inicio) || empty($fecha_fin)) {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
                    $tipo_mensaje = 'error';
                } else if ($descuento < 0 || $descuento > 100) {
                    $mensaje = 'El descuento debe estar entre 0 y 100';
                    $tipo_mensaje = 'error';
                } else if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
                    $mensaje = 'La fecha de fin debe ser posterior a la de inicio';
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO promociones (id_producto, descuento, fecha_inicio, fecha_fin, activa, descripcion) 
                            VALUES (:id_producto, :descuento, :fecha_inicio, :fecha_fin, :activa, :descripcion)
                        ");
                        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt->bindParam(':descuento', $descuento);
                        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                        $stmt->bindParam(':fecha_fin', $fecha_fin);
                        $stmt->bindParam(':activa', $activa, PDO::PARAM_INT);
                        $stmt->bindParam(':descripcion', $descripcion);
                        
                        if ($stmt->execute()) {
                            $mensaje = 'Promoción creada exitosamente';
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al crear promoción';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
                break;
                
            case 'editar':
                // Editar promoción existente
                $id_promocion = intval($_POST['id_promocion']);
                $id_producto = intval($_POST['id_producto']);
                $descuento = floatval($_POST['descuento']);
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];
                $descripcion = trim($_POST['descripcion'] ?? '');
                $tipo_promocion = trim($_POST['tipo_promocion'] ?? 'descuento');
                $activa = isset($_POST['activa']) ? 1 : 0;
                
                if (empty($id_producto) || empty($descuento) || empty($fecha_inicio) || empty($fecha_fin)) {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
                    $tipo_mensaje = 'error';
                } else if ($descuento < 0 || $descuento > 100) {
                    $mensaje = 'El descuento debe estar entre 0 y 100';
                    $tipo_mensaje = 'error';
                } else if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
                    $mensaje = 'La fecha de fin debe ser posterior a la de inicio';
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE promociones 
                            SET id_producto = :id_producto, descuento = :descuento, 
                                fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, 
                                activa = :activa, descripcion = :descripcion 
                            WHERE id_promocion = :id_promocion
                        ");
                        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt->bindParam(':descuento', $descuento);
                        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                        $stmt->bindParam(':fecha_fin', $fecha_fin);
                        $stmt->bindParam(':activa', $activa, PDO::PARAM_INT);
                        $stmt->bindParam(':descripcion', $descripcion);
                        $stmt->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
                        
                        if ($stmt->execute()) {
                            $mensaje = 'Promoción actualizada exitosamente';
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al actualizar promoción';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
                break;
                
            case 'eliminar':
                // Eliminar promoción
                $id_promocion = intval($_POST['id_promocion']);
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM promociones WHERE id_promocion = :id_promocion");
                    $stmt->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $mensaje = 'Promoción eliminada exitosamente';
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = 'Error al eliminar promoción';
                        $tipo_mensaje = 'error';
                    }
                } catch(PDOException $e) {
                    $mensaje = 'Error de base de datos: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener promociones con información de productos
try {
    $stmt_promociones = $pdo->prepare("
        SELECT p.id_promocion, p.id_producto, pr.nombre AS producto_nombre, 
               p.descuento, p.fecha_inicio, p.fecha_fin, p.activa, p.descripcion
        FROM promociones p
        LEFT JOIN productos pr ON p.id_producto = pr.id_producto
        ORDER BY p.fecha_inicio DESC
    ");
    $stmt_promociones->execute();
    $promociones = $stmt_promociones->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $promociones = [];
    $mensaje = 'Error al obtener promociones: ' . $e->getMessage();
    $tipo_mensaje = 'error';
}

// Obtener productos para el select
try {
    $stmt_productos = $pdo->prepare("
        SELECT id_producto, nombre, sabor 
        FROM productos 
        WHERE activo = 1 
        ORDER BY nombre
    ");
    $stmt_productos->execute();
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/promociones.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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
                    <a href="promociones.php" class="active">
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
    <div class="admin-container">

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
                <button class="action-btn primary" onclick="openPromocionModal()" style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); color: white; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">
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

            <!-- Modal: Crear/Editar Promoción -->
            <div id="promocionFormModal" class="promocion-modal-overlay">
                <div class="promocion-modal-content">
                    <div class="promocion-modal-header">
                        <h2 id="modalTitle">Agregar Promoción</h2>
                        <button class="promocion-modal-close" onclick="closePromocionModal()" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="promocion-modal-body">
                        <form id="promocionFormulario" method="POST" class="promocion-form">
                            <input type="hidden" name="accion" id="accionForm" value="crear">
                            <input type="hidden" name="id_promocion" id="id_promocion" value="">
                            <input type="hidden" name="tipo_promocion" id="tipo_promocion" value="descuento">
                            
                            <!-- Selección de producto -->
                            <div class="form-section">
                                <label class="form-section-title">Información del Producto</label>
                                <div class="form-group">
                                    <label for="id_producto">
                                        Seleccionar Producto
                                        <span class="required">*</span>
                                    </label>
                                    <select id="id_producto" name="id_producto" required aria-required="true">
                                        <option value="">-- Elige un producto --</option>
                                        <?php foreach ($productos as $producto): ?>
                                        <option value="<?php echo $producto['id_producto']; ?>">
                                            <?php echo htmlspecialchars($producto['nombre'] . ' (' . $producto['sabor'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo de promoción -->
                            <div class="form-section">
                                <label class="form-section-title">Tipo de Promoción</label>
                                <div class="promotion-type-group">
                                    <label class="promotion-type-option selected">
                                        <input type="radio" name="tipoPromo" value="descuento" checked onchange="updatePromoType('descuento')">
                                        <div class="promotion-type-label">
                                            <span class="promotion-type-icon"><i class="fas fa-percentage"></i></span>
                                            <span>Descuento</span>
                                        </div>
                                        <div class="promotion-type-desc">Descuento en porcentaje</div>
                                    </label>
                                    <label class="promotion-type-option">
                                        <input type="radio" name="tipoPromo" value="compre" onchange="updatePromoType('compre')">
                                        <div class="promotion-type-label">
                                            <span class="promotion-type-icon"><i class="fas fa-tags"></i></span>
                                            <span>2x1</span>
                                        </div>
                                        <div class="promotion-type-desc">Compra 2 lleva 1</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Descuento y fechas -->
                            <div class="form-section">
                                <label class="form-section-title">Detalles de la Promoción</label>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="descuento">
                                            Descuento (%)
                                            <span class="required">*</span>
                                        </label>
                                        <input type="number" id="descuento" name="descuento" min="0" max="100" step="0.01" required aria-required="true" placeholder="Ej: 10.5">
                                    </div>
                                    <div class="form-group">
                                        <label for="fecha_inicio">
                                            Fecha Inicio
                                            <span class="required">*</span>
                                        </label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" required aria-required="true">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fecha_fin">
                                            Fecha Fin
                                            <span class="required">*</span>
                                        </label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" required aria-required="true">
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción y estado -->
                            <div class="form-section">
                                <div class="form-group full">
                                    <label for="descripcion">Descripción (Opcional)</label>
                                    <textarea id="descripcion" name="descripcion" placeholder="Ej: Promoción de fin de mes..."></textarea>
                                </div>
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="activa" name="activa" value="1" checked>
                                    <label for="activa">Promoción Activa</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="promocion-modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closePromocionModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" form="promocionFormulario" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Promoción
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de promociones -->
            <div class="table-container">
                <table class="promociones-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descuento (%)</th>
                            <th>Período</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="promocionesTable">
                        <?php if (!empty($promociones)): ?>
                            <?php foreach ($promociones as $promo): ?>
                            <tr data-status="<?php echo $promo['activa'] ? 'activo' : 'inactivo'; ?>" data-id="<?php echo $promo['id_promocion']; ?>">
                                <td><strong><?php echo htmlspecialchars($promo['producto_nombre'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo number_format($promo['descuento'], 2); ?>%</td>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($promo['fecha_inicio'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($promo['fecha_fin'])); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($promo['descripcion'] ?? '-'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $promo['activa'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $promo['activa'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn edit" onclick="editarPromocion(<?php echo $promo['id_promocion']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $promo['id_promocion']; ?>, '<?php echo addslashes(htmlspecialchars($promo['producto_nombre'])); ?>')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    No hay promociones registradas. <a href="#" onclick="showForm('crear'); return false;">Crear una</a>
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
        function openPromocionModal() {
            document.getElementById('accionForm').value = 'crear';
            document.getElementById('id_promocion').value = '';
            document.getElementById('modalTitle').textContent = 'Agregar Promoción';
            document.getElementById('promocionFormulario').reset();
            document.getElementById('activa').checked = true;
            
            // Inicializar fechas
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_inicio').value = hoy;
            
            const unMesDespues = new Date();
            unMesDespues.setMonth(unMesDespues.getMonth() + 1);
            document.getElementById('fecha_fin').value = unMesDespues.toISOString().split('T')[0];
            
            document.getElementById('promocionFormModal').classList.add('active');
        }
        
        function closePromocionModal() {
            document.getElementById('promocionFormModal').classList.remove('active');
        }
        
        function updatePromoType(tipo) {
            document.getElementById('tipo_promocion').value = tipo;
            
            // Actualizar estilos visuales
            const options = document.querySelectorAll('.promotion-type-option');
            options.forEach(opt => opt.classList.remove('selected'));
            
            const selectedInput = document.querySelector(`input[name="tipoPromo"][value="${tipo}"]`);
            if (selectedInput) {
                selectedInput.closest('.promotion-type-option').classList.add('selected');
            }
        }
        
        // Cerrar modal al hacer click fuera
        document.getElementById('promocionFormModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePromocionModal();
            }
        });
        
        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePromocionModal();
            }
        });
        
        function editarPromocion(id) {
            fetch(`funcionalidades/obtener_promocion.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const promo = data.promocion;
                        document.getElementById('accionForm').value = 'editar';
                        document.getElementById('id_promocion').value = promo.id_promocion;
                        document.getElementById('id_producto').value = promo.id_producto || '';
                        document.getElementById('descuento').value = promo.descuento || '';
                        document.getElementById('fecha_inicio').value = promo.fecha_inicio || '';
                        document.getElementById('fecha_fin').value = promo.fecha_fin || '';
                        document.getElementById('descripcion').value = promo.descripcion || '';
                        document.getElementById('activa').checked = promo.activa == 1 ? true : false;
                        document.getElementById('modalTitle').textContent = 'Editar Promoción';
                        document.getElementById('promocionFormModal').classList.add('active');
                    } else {
                        alert('Error al obtener promoción: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar la promoción de "${nombre}"? Esta acción no se puede deshacer.`)) {
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
                if (row.cells.length < 2) continue;
                const productoCell = row.cells[0].textContent.toLowerCase();
                
                if (productoCell.includes(filter)) {
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
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
</body>
</html>