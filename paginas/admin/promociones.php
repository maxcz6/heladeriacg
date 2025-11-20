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
                $id_promocion = intval($_POST['id_promocion'] ?? 0);
                $id_producto = intval($_POST['id_producto'] ?? 0);
                $descuento = floatval($_POST['descuento'] ?? 0);
                $fecha_inicio = $_POST['fecha_inicio'] ?? '';
                $fecha_fin = $_POST['fecha_fin'] ?? '';
                $descripcion = trim($_POST['descripcion'] ?? '');
                $activa = isset($_POST['activa']) ? 1 : 0;

                if (empty($id_promocion) || empty($id_producto) || $descuento === '') {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
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

            case 'crear_cupon':
                // Crear nuevo cupón
                $prefijo = strtoupper(trim($_POST['prefijo_cupon']));
                $tipo_descuento = trim($_POST['tipo_descuento_cupon']);
                $valor_descuento = floatval($_POST['valor_descuento_cupon']);
                $monto_minimo = floatval($_POST['monto_minimo_cupon'] ?? 0);
                $fecha_inicio = $_POST['fecha_inicio_cupon'];
                $fecha_fin = $_POST['fecha_fin_cupon'];
                $usos_maximos = !empty($_POST['usos_maximos_cupon']) ? intval($_POST['usos_maximos_cupon']) : null;
                $usos_por_cliente = intval($_POST['usos_por_cliente_cupon'] ?? 1);
                $descripcion = trim($_POST['descripcion_cupon'] ?? '');
                $id_usuario = $_SESSION['id_usuario'];

                if (empty($prefijo) || empty($tipo_descuento) || empty($valor_descuento)) {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
                    $tipo_mensaje = 'error';
                } else if ($valor_descuento <= 0) {
                    $mensaje = 'El valor del descuento debe ser mayor a 0';
                    $tipo_mensaje = 'error';
                } else if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
                    $mensaje = 'La fecha de vencimiento debe ser posterior a la de inicio';
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        // Generar código único
                        $codigo = '';
                        $existe = true;
                        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        
                        while ($existe) {
                            $codigo = $prefijo;
                            for ($i = 0; $i < 6; $i++) {
                                $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
                            }
                            
                            // Verificar si el código ya existe
                            $stmt_check = $pdo->prepare("SELECT COUNT(*) as cantidad FROM cupones WHERE codigo = :codigo");
                            $stmt_check->bindParam(':codigo', $codigo);
                            $stmt_check->execute();
                            $resultado = $stmt_check->fetch(PDO::FETCH_ASSOC);
                            $existe = $resultado['cantidad'] > 0;
                        }
                        // Insertar cupón (usar activo según checkbox)
                        $activo = isset($_POST['activo_cupon']) ? 1 : 0;
                        $stmt = $pdo->prepare("
                            INSERT INTO cupones (
                                codigo, descripcion, tipo_descuento, valor_descuento,
                                monto_minimo, fecha_inicio, fecha_fin, usos_maximos,
                                usos_por_cliente, creado_por, activo, usos_actuales
                            ) VALUES (
                                :codigo, :descripcion, :tipo_descuento, :valor_descuento,
                                :monto_minimo, :fecha_inicio, :fecha_fin, :usos_maximos,
                                :usos_por_cliente, :creado_por, :activo, 0
                            )
                        ");
                        
                        $stmt->bindParam(':codigo', $codigo);
                        $stmt->bindParam(':descripcion', $descripcion);
                        $stmt->bindParam(':tipo_descuento', $tipo_descuento);
                        $stmt->bindParam(':valor_descuento', $valor_descuento);
                        $stmt->bindParam(':monto_minimo', $monto_minimo);
                        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                        $stmt->bindParam(':fecha_fin', $fecha_fin);
                        $stmt->bindParam(':usos_maximos', $usos_maximos, PDO::PARAM_INT);
                        $stmt->bindParam(':usos_por_cliente', $usos_por_cliente, PDO::PARAM_INT);
                        $stmt->bindParam(':creado_por', $id_usuario, PDO::PARAM_INT);
                        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
                        
                        if ($stmt->execute()) {
                            $mensaje = "Cupón creado exitosamente con código: <strong>$codigo</strong>";
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al crear cupón';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
                break;

            case 'eliminar_cupon':
                // Eliminar cupón
                $id_cupon = intval($_POST['id_cupon']);
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM cupones WHERE id_cupon = :id_cupon");
                    $stmt->bindParam(':id_cupon', $id_cupon, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $mensaje = 'Cupón eliminado exitosamente';
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = 'Error al eliminar cupón';
                        $tipo_mensaje = 'error';
                    }
                } catch(PDOException $e) {
                    $mensaje = 'Error de base de datos: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
                break;
            case 'editar_cupon':
                // Editar cupón existente
                $id_cupon = intval($_POST['id_cupon'] ?? 0);
                $tipo_descuento = trim($_POST['tipo_descuento_cupon'] ?? 'porcentaje');
                $valor_descuento = floatval($_POST['valor_descuento_cupon'] ?? 0);
                $monto_minimo = floatval($_POST['monto_minimo_cupon'] ?? 0);
                $fecha_inicio = $_POST['fecha_inicio_cupon'] ?? null;
                $fecha_fin = $_POST['fecha_fin_cupon'] ?? null;
                $usos_maximos = !empty($_POST['usos_maximos_cupon']) ? intval($_POST['usos_maximos_cupon']) : null;
                $usos_por_cliente = intval($_POST['usos_por_cliente_cupon'] ?? 1);
                $descripcion = trim($_POST['descripcion_cupon'] ?? '');
                $activo = isset($_POST['activo_cupon']) ? 1 : 0;

                if (empty($id_cupon) || empty($tipo_descuento) || $valor_descuento <= 0) {
                    $mensaje = 'Todos los campos requeridos deben estar completos';
                    $tipo_mensaje = 'error';
                } else if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
                    $mensaje = 'La fecha de vencimiento debe ser posterior a la de inicio';
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE cupones SET descripcion = :descripcion, tipo_descuento = :tipo_descuento, valor_descuento = :valor_descuento, monto_minimo = :monto_minimo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, usos_maximos = :usos_maximos, usos_por_cliente = :usos_por_cliente, activo = :activo WHERE id_cupon = :id_cupon");
                        $stmt->bindParam(':descripcion', $descripcion);
                        $stmt->bindParam(':tipo_descuento', $tipo_descuento);
                        $stmt->bindParam(':valor_descuento', $valor_descuento);
                        $stmt->bindParam(':monto_minimo', $monto_minimo);
                        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                        $stmt->bindParam(':fecha_fin', $fecha_fin);
                        $stmt->bindParam(':usos_maximos', $usos_maximos, PDO::PARAM_INT);
                        $stmt->bindParam(':usos_por_cliente', $usos_por_cliente, PDO::PARAM_INT);
                        $stmt->bindParam(':id_cupon', $id_cupon, PDO::PARAM_INT);
                        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);

                        if ($stmt->execute()) {
                            $mensaje = 'Cupón actualizado exitosamente';
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al actualizar cupón';
                            $tipo_mensaje = 'error';
                        }
                    } catch(PDOException $e) {
                        $mensaje = 'Error de base de datos: ' . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
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
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/promociones.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
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

            <!-- TABS DE NAVEGACIÓN -->
            <div class="promo-tabs-container">
                <button id="tab-promociones" class="promo-tab-btn active" onclick="mostrarTabPromo('promociones')">
                    <i class="fas fa-tag"></i> Promociones
                </button>
                <button id="tab-cupones" class="promo-tab-btn" onclick="mostrarTabPromo('cupones')">
                    <i class="fas fa-ticket-alt"></i> Cupones
                </button>
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
            <div id="seccion-promociones" class="promo-section active">
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
            </div>

            <!-- SECCIÓN DE CUPONES (Inicialmente oculta) -->
            <div id="seccion-cupones" class="promo-section" style="display: none;">
                <div class="cupones-header" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
                    <button class="action-btn primary" onclick="abrirModalCupon()" style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); color: white; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus"></i> Agregar Cupón
                    </button>
                    <input type="text" id="buscar-cupon" placeholder="Buscar cupón..." onkeyup="buscarCupones()" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; flex: 1; min-width: 200px;">
                </div>
                <div class="table-container">
                    <table class="cupones-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Descuento</th>
                                <th>Período</th>
                                <th>Usos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-cupones">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                                    Cargando cupones...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL PARA CREAR/EDITAR CUPÓN -->
    <div id="cuponFormModal" class="promocion-modal-overlay">
        <div class="promocion-modal-content">
            <div class="promocion-modal-header">
                <h2 id="cuponModalTitle">Agregar Cupón</h2>
                <button class="promocion-modal-close" onclick="cerrarModalCupon()" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="promocion-modal-body">
                <form id="cuponFormulario" method="POST" class="promocion-form">
                    <input type="hidden" name="accion" id="accionCupon" value="crear_cupon">
                    <input type="hidden" name="id_cupon" id="id_cupon_edit" value="">
                    
                    <div class="form-section">
                        <label class="form-section-title">Información del Cupón</label>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prefijo_cupon">
                                    Prefijo del Código (ej: NOEL, VERANO)
                                    <span class="required">*</span>
                                </label>
                                <input type="text" id="prefijo_cupon" name="prefijo_cupon" placeholder="NOEL" maxlength="10" style="text-transform: uppercase;" required>
                                <small style="color: #666;">El código se generará automáticamente</small>
                            </div>
                            <div class="form-group">
                                <label for="tipo_descuento_cupon">
                                    Tipo de Descuento
                                    <span class="required">*</span>
                                </label>
                                <select id="tipo_descuento_cupon" name="tipo_descuento_cupon" required>
                                    <option value="porcentaje">Porcentaje (%)</option>
                                    <option value="monto_fijo">Monto Fijo (S/.)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="valor_descuento_cupon">
                                    Valor del Descuento
                                    <span class="required">*</span>
                                </label>
                                <input type="number" id="valor_descuento_cupon" name="valor_descuento_cupon" step="0.01" min="0.01" placeholder="15" required>
                            </div>
                            <div class="form-group">
                                <label for="monto_minimo_cupon">
                                    Monto Mínimo de Compra (S/.)
                                    <span class="required">*</span>
                                </label>
                                <input type="number" id="monto_minimo_cupon" name="monto_minimo_cupon" step="0.01" min="0" value="0" placeholder="25" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <label class="form-section-title">Fechas y Límites</label>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_inicio_cupon">
                                    Fecha de Inicio
                                    <span class="required">*</span>
                                </label>
                                <input type="date" id="fecha_inicio_cupon" name="fecha_inicio_cupon" required>
                            </div>
                            <div class="form-group">
                                <label for="fecha_fin_cupon">
                                    Fecha de Vencimiento
                                    <span class="required">*</span>
                                </label>
                                <input type="date" id="fecha_fin_cupon" name="fecha_fin_cupon" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="usos_maximos_cupon">
                                    Usos Máximos (Dejar en blanco para ilimitado)
                                </label>
                                <input type="number" id="usos_maximos_cupon" name="usos_maximos_cupon" min="1" placeholder="100">
                            </div>
                            <div class="form-group">
                                <label for="usos_por_cliente_cupon">
                                    Usos por Cliente
                                    <span class="required">*</span>
                                </label>
                                <input type="number" id="usos_por_cliente_cupon" name="usos_por_cliente_cupon" min="1" value="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-group full">
                            <label for="descripcion_cupon">Descripción</label>
                            <textarea id="descripcion_cupon" name="descripcion_cupon" placeholder="Ej: Cupón de Navidad con 20% de descuento..."></textarea>
                        </div>
                        <div class="form-group checkbox-group" style="margin-top:10px;">
                            <input type="checkbox" id="activo_cupon" name="activo_cupon" value="1" checked>
                            <label for="activo_cupon">Cupón Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="promocion-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCupon()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" form="cuponFormulario" class="btn btn-primary" onclick="guardarCupon()">
                    <i class="fas fa-save"></i> Guardar Cupón
                </button>
            </div>
        </div>
    </div>

    <script>
        // ========== CONFIGURACIÓN GLOBAL ==========
        const ANIMACION_DURACION = 300;
        let modalActual = null;

        // ========== UTILIDADES ==========
        
        /**
         * Muestra una notificación toast
         */
        function mostrarNotificacion(mensaje, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${tipo}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${mensaje}</span>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), ANIMACION_DURACION);
            }, 3000);
        }

        /**
         * Valida un formulario
         */
        function validarFormulario(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('[required]');
            let valido = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.closest('.form-group').classList.add('error');
                    valido = false;
                } else {
                    input.closest('.form-group').classList.remove('error');
                }
            });

            return valido;
        }

        /**
         * Abre un modal con animación
         */
        function abrirModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modalActual = modalId;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        /**
         * Cierra un modal con animación
         */
        function cerrarModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.remove('active');
            document.body.style.overflow = '';
            modalActual = null;
        }

        // ========== FUNCIONES PARA PROMOCIONES ==========
        
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
            
            abrirModal('promocionFormModal');
        }
        
        function closePromocionModal() {
            cerrarModal('promocionFormModal');
        }
        
        function updatePromoType(tipo) {
            document.getElementById('tipo_promocion').value = tipo;
            
            // Actualizar estilos visuales con animación
            const options = document.querySelectorAll('.promotion-type-option');
            options.forEach(opt => {
                opt.classList.remove('selected');
                opt.style.transform = 'scale(1)';
            });
            
            const selectedInput = document.querySelector(`input[name="tipoPromo"][value="${tipo}"]`);
            if (selectedInput) {
                const option = selectedInput.closest('.promotion-type-option');
                option.classList.add('selected');
                option.style.transform = 'scale(1.02)';
            }
        }
        
        // Cerrar modal al hacer click fuera
        document.addEventListener('DOMContentLoaded', function() {
            const promocionModal = document.getElementById('promocionFormModal');
            const cuponModal = document.getElementById('cuponFormModal');

            if (promocionModal) {
                promocionModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closePromocionModal();
                    }
                });
            }

            if (cuponModal) {
                cuponModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        cerrarModalCupon();
                    }
                });
            }
        });
        
        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalActual) {
                if (modalActual === 'promocionFormModal') {
                    closePromocionModal();
                } else if (modalActual === 'cuponFormModal') {
                    cerrarModalCupon();
                }
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
            const filter = input ? input.value.toLowerCase() : '';
            // Detectar pestaña activa por la clase en el botón (más fiable que style)
            const tabCupones = document.getElementById('tab-cupones');
            const cuponesActive = tabCupones && tabCupones.classList.contains('active');
            if (cuponesActive) {
                buscarCupones(filter);
                return;
            }

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
            const tabCupones = document.getElementById('tab-cupones');
            const cuponesActive = tabCupones && tabCupones.classList.contains('active');
            if (cuponesActive) {
                // Filtrar cupones por estado (activo/inactivo)
                filterCuponesByStatus(filter);
                return;
            }

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

        // ========== FUNCIONES PARA CUPONES ==========

        function mostrarTabPromo(tab) {
            const seccionPromos = document.getElementById('seccion-promociones');
            const seccionCupones = document.getElementById('seccion-cupones');
            const tabPromos = document.getElementById('tab-promociones');
            const tabCupones = document.getElementById('tab-cupones');

            if (tab === 'promociones') {
                seccionPromos.style.display = 'block';
                seccionCupones.style.display = 'none';
                tabPromos.classList.add('active');
                tabCupones.classList.remove('active');
            } else {
                seccionPromos.style.display = 'none';
                seccionCupones.style.display = 'block';
                tabPromos.classList.remove('active');
                tabCupones.classList.add('active');
                cargarCupones();
            }
        }

        function abrirModalCupon() {
            document.getElementById('accionCupon').value = 'crear_cupon';
            document.getElementById('id_cupon_edit').value = '';
            document.getElementById('cuponModalTitle').textContent = 'Agregar Cupón';
            document.getElementById('cuponFormulario').reset();

            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_inicio_cupon').value = hoy;

            const unMesDespues = new Date();
            unMesDespues.setMonth(unMesDespues.getMonth() + 1);
            document.getElementById('fecha_fin_cupon').value = unMesDespues.toISOString().split('T')[0];

            // Marcar cupón como activo por defecto al crear
            const activoEl = document.getElementById('activo_cupon');
            if (activoEl) activoEl.checked = true;

            abrirModal('cuponFormModal');
        }

        function cerrarModalCupon() {
            cerrarModal('cuponFormModal');
        }

        function cargarCupones() {
            const tbody = document.getElementById('tbody-cupones');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i> Cargando...</td></tr>';

            fetch('funcionalidades/obtener_cupones.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.cupones.length > 0) {
                        tbody.innerHTML = data.cupones.map((cupon, idx) => `
                            <tr data-id="${cupon.id_cupon}" data-estado="${cupon.activo ? 'activo' : 'inactivo'}" style="animation: slideInLeft ${ANIMACION_DURACION}ms ease forwards; animation-delay: ${idx * 50}ms;">
                                <td><strong>${cupon.codigo}</strong></td>
                                <td><small>${cupon.tipo_descuento === 'porcentaje' ? '📊 %' : '💵 Monto'}</small></td>
                                <td><strong>${cupon.valor_descuento}</strong></td>
                                <td><small>${new Date(cupon.fecha_inicio).toLocaleDateString('es-PE')} - ${new Date(cupon.fecha_fin).toLocaleDateString('es-PE')}</small></td>
                                <td><small>${cupon.usos_actuales}/${cupon.usos_maximos || '∞'}</small></td>
                                <td>
                                    <span class="status-badge ${cupon.activo ? 'active' : 'inactive'}">
                                        ${cupon.activo ? '✓ Activo' : '✗ Inactivo'}
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn edit" onclick="editarCupon(${cupon.id_cupon})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmarEliminarCupon(${cupon.id_cupon}, '${cupon.codigo}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                            <div style="margin-bottom: 10px; font-size: 2rem;">📭</div>
                            No hay cupones. <a href="#" onclick="abrirModalCupon(); return false;" style="color: #0891b2; text-decoration: underline;">Crear uno ahora</a>
                        </td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #ef4444;">Error al cargar cupones</td></tr>';
                });
        }

        /**
         * Buscar/filtrar cupones. Si se pasa un filtro se usa, sino lee el input #buscar-cupon
         */
        function buscarCupones(filter = null) {
            const f = (filter !== null) ? filter : (document.getElementById('buscar-cupon') ? document.getElementById('buscar-cupon').value.toLowerCase() : '');
            const rows = document.querySelectorAll('#tbody-cupones tr');
            let conteo = 0;

            rows.forEach(row => {
                // Ignorar filas de estado (mensajes)
                if (!row.cells || row.cells.length === 0) return;
                const codigo = row.cells[0]?.textContent.toLowerCase() || '';
                const descripcion = row.textContent.toLowerCase();

                if (codigo.includes(f) || descripcion.includes(f)) {
                    row.style.display = '';
                    conteo++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Mostrar mensaje si no hay resultados
            if (conteo === 0 && f) {
                const tbody = document.getElementById('tbody-cupones');
                if (!document.getElementById('sin-resultados')) {
                    tbody.innerHTML += '<tr id="sin-resultados"><td colspan="7" style="text-align: center; padding: 20px; color: #999;">No se encontraron cupones</td></tr>';
                }
            } else {
                const sinResultados = document.getElementById('sin-resultados');
                if (sinResultados) sinResultados.remove();
            }
        }

        function filterCuponesByStatus(filter) {
            const rows = document.querySelectorAll('#tbody-cupones tr');
            rows.forEach(row => {
                if (!row.getAttribute) return;
                const estado = row.getAttribute('data-estado');
                if (!estado) return;
                if (filter === '' || estado === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function guardarCupon() {
            const form = document.getElementById('cuponFormulario');
            
            // Validar campos requeridos
            const prefijo = document.getElementById('prefijo_cupon').value.trim();
            const tipo = document.getElementById('tipo_descuento_cupon').value;
            const valor = parseFloat(document.getElementById('valor_descuento_cupon').value);

            if (!prefijo || !tipo || !valor || valor <= 0) {
                mostrarNotificacion('Por favor completa todos los campos requeridos', 'error');
                return;
            }

            const formData = new FormData(form);
            // Usar el valor actual del campo hidden (crear_cupon o editar_cupon)
            const accionActual = document.getElementById('accionCupon').value || 'crear_cupon';
            formData.append('accion', accionActual);

            // Desactivar botón durante envío
            const btn = document.querySelector('button[form="cuponFormulario"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            fetch('promociones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const accion = document.getElementById('accionCupon').value;
                if (accion === 'crear_cupon') {
                    mostrarNotificacion('✓ Cupón creado exitosamente', 'success');
                } else {
                    mostrarNotificacion('✓ Cupón actualizado exitosamente', 'success');
                }
                
                cerrarModalCupon();
                setTimeout(() => {
                    cargarCupones();
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cupón';
                }, ANIMACION_DURACION);
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error al guardar cupón', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cupón';
            });
        }

        function editarCupon(id) {
            fetch(`funcionalidades/obtener_cupon.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cupon = data.cupon;
                        // Seguridad: verificar existencia de elementos antes de asignar
                        const setIf = (id, value) => {
                            const el = document.getElementById(id);
                            if (!el) return;
                            el.value = (value === null || typeof value === 'undefined') ? '' : value;
                        };

                        document.getElementById('accionCupon').value = 'editar_cupon';
                        setIf('id_cupon_edit', cupon.id_cupon || '');
                        // Prefijo (primeros 4 caracteres del código) si existe
                        setIf('prefijo_cupon', cupon.codigo ? cupon.codigo.substring(0, 4) : '');
                        setIf('tipo_descuento_cupon', cupon.tipo_descuento || 'porcentaje');
                        setIf('valor_descuento_cupon', cupon.valor_descuento ?? '');
                        setIf('monto_minimo_cupon', cupon.monto_minimo ?? '');

                        // Normalizar fechas para inputs type=date (YYYY-MM-DD)
                        const parseDateForInput = (d) => {
                            if (!d) return '';
                            // Si ya viene en formato YYYY-MM-DD
                            if (/^\d{4}-\d{2}-\d{2}$/.test(d)) return d;
                            // Intentar crear Date y formatear
                            const dt = new Date(d);
                            if (isNaN(dt)) return '';
                            return dt.toISOString().split('T')[0];
                        };

                        setIf('fecha_inicio_cupon', parseDateForInput(cupon.fecha_inicio));
                        setIf('fecha_fin_cupon', parseDateForInput(cupon.fecha_fin));

                        setIf('usos_maximos_cupon', cupon.usos_maximos ?? '');
                        setIf('usos_por_cliente_cupon', cupon.usos_por_cliente ?? 1);
                        setIf('descripcion_cupon', cupon.descripcion ?? '');

                        // Establecer checkbox de activo si existe
                        const activoElEdit = document.getElementById('activo_cupon');
                        if (activoElEdit) activoElEdit.checked = (cupon.activo == 1);

                        document.getElementById('cuponModalTitle').textContent = '✏️ Editar Cupón';
                        abrirModal('cuponFormModal');
                        mostrarNotificacion('Cupón cargado. Realiza los cambios y guarda', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNotificacion('Error al cargar cupón', 'error');
                });
        }

        function confirmarEliminarCupon(id, codigo) {
            // Crear modal personalizado de confirmación
            const modal = document.createElement('div');
            modal.className = 'promocion-modal-overlay active';
            modal.innerHTML = `
                <div class="promocion-modal-content" style="max-width: 400px;">
                    <div class="promocion-modal-header">
                        <h2 style="color: #ef4444;">⚠️ Confirmar Eliminación</h2>
                        <button class="promocion-modal-close" onclick="this.closest('.promocion-modal-overlay').remove();">×</button>
                    </div>
                    <div class="promocion-modal-body">
                        <p style="font-size: 1rem; color: #6b7280; margin-bottom: 20px;">
                            ¿Estás seguro de que deseas eliminar el cupón <strong>"${codigo}"</strong>? 
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <div class="promocion-modal-footer" style="gap: 10px;">
                        <button class="btn btn-secondary" onclick="this.closest('.promocion-modal-overlay').remove();" style="flex: 1;">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button class="btn btn-primary" onclick="eliminarCuponConfirmado(${id}); this.closest('.promocion-modal-overlay').remove();" style="flex: 1;">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function eliminarCuponConfirmado(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'eliminar_cupon';
            form.appendChild(accionInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_cupon';
            idInput.value = id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Cargar cupones al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarCupones();
            // Evitar submit tradicional del formulario de cupones (Enter o submit)
            const cuponForm = document.getElementById('cuponFormulario');
            if (cuponForm) {
                cuponForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    guardarCupon();
                });
            }
        });
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>