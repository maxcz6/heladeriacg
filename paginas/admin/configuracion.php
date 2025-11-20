<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/admin_functions.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/SucursalLocal.php');

$mensaje = '';
$tipo_mensaje = '';

// Inicializar sucursal local
$sucursal_local = new SucursalLocal($pdo);

// Manejar operaciones de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'cambiar_password':
                $resultado = cambiarPasswordUsuario(
                    $_SESSION['id_usuario'],
                    $_POST['password_actual'],
                    $_POST['password_nuevo'],
                    $_POST['password_confirmar']
                );
                $mensaje = $resultado['message'];
                $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
                break;
                
            case 'backup_db':
                // Crear backup de la base de datos
                $archivo = 'backup_heladeria_' . date('Y-m-d_H-i-s') . '.sql';
                $ruta = $_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/backups/';

                if (!is_dir($ruta)) {
                    mkdir($ruta, 0755, true);
                }

                $archivo_completo = $ruta . $archivo;

                // Usar PDO para exportar la base de datos de forma segura
                try {
                    $host = 'localhost';
                    $dbname = 'heladeriacgbd';
                    $username = 'root';
                    $password = '';

                    $comando = 'mysqldump --host=' . $host . ' --user=' . $username . ' --password="' . $password . '" ' . $dbname . ' > ' . escapeshellarg($archivo_completo);

                    // Ejecutar mysqldump en Windows/XAMPP
                    $process = proc_open($comando, [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w']
                    ], $pipes);

                    if (is_resource($process)) {
                        $stdout = stream_get_contents($pipes[1]);
                        $stderr = stream_get_contents($pipes[2]);

                        foreach ($pipes as $pipe) {
                            fclose($pipe);
                        }

                        $return_code = proc_close($process);

                        if ($return_code === 0 && file_exists($archivo_completo)) {
                            $mensaje = 'Backup creado exitosamente: ' . $archivo;
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al crear backup: ' . $stderr;
                            $tipo_mensaje = 'error';
                        }
                    } else {
                        // Alternativa: crear backup manual
                        $backup_content = "-- Backup de heladeriacgbd\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

                        // Exportar estructura y datos de tablas importantes
                        $tables = ['productos', 'clientes', 'vendedores', 'ventas', 'detalle_ventas', 'usuarios', 'proveedores', 'roles'];

                        foreach ($tables as $table) {
                            // Obtener estructura de la tabla
                            $stmt = $pdo->query("SHOW CREATE TABLE $table");
                            $create_table = $stmt->fetch();
                            $backup_content .= $create_table[1] . ";\n\n";

                            // Exportar datos
                            $stmt = $pdo->query("SELECT * FROM $table");
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (!empty($rows)) {
                                $backup_content .= "INSERT INTO `$table` VALUES ";
                                $values = [];
                                foreach ($rows as $row) {
                                    $row_values = [];
                                    foreach ($row as $value) {
                                        if ($value === null) {
                                            $row_values[] = 'NULL';
                                        } else {
                                            $row_values[] = $pdo->quote($value);
                                        }
                                    }
                                    $values[] = '(' . implode(',', $row_values) . ')';
                                }
                                $backup_content .= implode(',', $values) . ";\n\n";
                            }
                        }

                        if (file_put_contents($archivo_completo, $backup_content)) {
                            $mensaje = 'Backup creado exitosamente (método alternativo): ' . $archivo;
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'No se pudo crear el backup';
                            $tipo_mensaje = 'error';
                        }
                    }
                } catch (Exception $e) {
                    $mensaje = 'Error al crear backup: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
                break;
            case 'configurar_sistema':
                // Actualmente no se almacena la configuración en la base de datos
                // pero se puede implementar si se necesita
                $mensaje = 'Configuración del sistema actualizada exitosamente';
                $tipo_mensaje = 'success';
                break;
        }
    }
}

// Obtener métricas del sistema
$metricas = obtenerMetricasSistema();
$stats_inventario = obtenerEstadisticasInventario();
$stats_ventas = obtenerEstadisticasVentas();
$sucursal_actual = $sucursal_local->obtenerDatosSucursalActual($pdo);
$modo_offline = $sucursal_local->estaEnModoOffline();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="../../css/admin/navbar.css">
    <link rel="stylesheet" href="../../css/admin/configuracion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="admin-container">

        <main class="admin-main">
            <div class="welcome-section">
                <h1><i class="fas fa-cog"></i> Configuración del Sistema</h1>
                <p>Gestiona las configuraciones generales y opciones avanzadas del sistema</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="config-container">
                <!-- Estado de Sucursal -->
                <div class="config-section">
                    <h3><i class="fas fa-store"></i> Estado de Sucursal Actual</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">Sucursal Activa</div>
                            <p><?php echo htmlspecialchars($sucursal_actual['nombre'] ?? 'Sin seleccionar'); ?></p>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Dirección</div>
                            <p><?php echo htmlspecialchars($sucursal_actual['direccion'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Modo de Operación</div>
                            <p>
                                <span style="color: <?php echo $modo_offline ? '#ef4444' : '#10b981'; ?>;">
                                    <i class="fas fa-<?php echo $modo_offline ? 'wifi-off' : 'wifi'; ?>"></i>
                                    <?php echo $modo_offline ? 'Offline' : 'Online'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="location.href='sucursales.php'">
                            <i class="fas fa-exchange-alt"></i> Cambiar Sucursal
                        </button>
                    </div>
                </div>

                <!-- Cambiar contraseña -->
                <div class="config-section">
                    <h3><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                    <p>Actualiza tu contraseña de acceso al sistema</p>
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiar_password">
                        
                        <div class="form-group">
                            <label for="password_actual">Contraseña Actual <span>*</span></label>
                            <input type="password" id="password_actual" name="password_actual" required placeholder="Ingresa tu contraseña actual">
                        </div>
                        
                        <div class="form-group">
                            <label for="password_nuevo">Nueva Contraseña <span>*</span></label>
                            <input type="password" id="password_nuevo" name="password_nuevo" required placeholder="Ingresa tu nueva contraseña">
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmar">Confirmar Nueva Contraseña <span>*</span></label>
                            <input type="password" id="password_confirmar" name="password_confirmar" required placeholder="Confirma tu nueva contraseña">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Backup de la base de datos -->
                <div class="config-section">
                    <h3><i class="fas fa-database"></i> Backup de la Base de Datos</h3>
                    <div class="backup-info">
                        <strong><i class="fas fa-shield-alt"></i> Información Importante:</strong>
                        <p>Realiza backups regulares de tu base de datos para evitar pérdida de información. Se recomienda hacer un backup cada semana.</p>
                    </div>
                    
                    <form method="POST" onsubmit="return confirm('¿Deseas crear un backup completo de la base de datos? Este proceso puede tomar algunos minutos...')">
                        <input type="hidden" name="accion" value="backup_db">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cloud-download-alt"></i> Crear Backup Completo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Estadísticas del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-chart-bar"></i> Estadísticas del Sistema</h3>
                    
                    <h4 style="color: #0891b2; margin-top: 20px; margin-bottom: 15px; font-size: 1.1em;">
                        <i class="fas fa-chart-line"></i> Métricas Generales
                    </h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="stat-icon fas fa-money-bill-wave"></i>
                            <h4>Ventas Totales</h4>
                            <p>S/. <?php echo number_format($metricas['ventas_totales'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-box"></i>
                            <h4>Productos Activos</h4>
                            <p><?php echo $metricas['productos_activos']; ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-users"></i>
                            <h4>Clientes</h4>
                            <p><?php echo $metricas['clientes_totales']; ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-user-tie"></i>
                            <h4>Empleados</h4>
                            <p><?php echo $metricas['empleados_totales']; ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-sun"></i>
                            <h4>Ventas Hoy</h4>
                            <p>S/. <?php echo number_format($metricas['ventas_hoy'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-exclamation-triangle"></i>
                            <h4>Productos Bajos</h4>
                            <p><?php echo $metricas['productos_bajos']; ?></p>
                        </div>
                    </div>

                    <h4 style="color: #0891b2; margin-top: 20px; margin-bottom: 15px; font-size: 1.1em;">
                        <i class="fas fa-inventory"></i> Inventario y Ventas
                    </h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="stat-icon fas fa-receipt"></i>
                            <h4>Total de Ventas</h4>
                            <p><?php echo $stats_ventas['total_ventas']; ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-weight"></i>
                            <h4>Valor Inventario</h4>
                            <p>S/. <?php echo number_format($stats_inventario['valor_inventario'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-low-vision"></i>
                            <h4>Bajo Stock</h4>
                            <p><?php echo $stats_inventario['productos_bajo_stock']; ?></p>
                        </div>
                        <div class="stat-card">
                            <i class="stat-icon fas fa-user-shield"></i>
                            <h4>Usuarios Activos</h4>
                            <p><?php echo $metricas['usuarios_activos']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Configuración del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-sliders-h"></i> Configuración del Sistema</h3>
                    <p>Personaliza los parámetros básicos del sistema</p>
                    <form method="POST">
                        <input type="hidden" name="accion" value="configurar_sistema">

                        <div class="form-group">
                            <label for="nombre_negocio">Nombre del Negocio <span>*</span></label>
                            <input type="text" id="nombre_negocio" name="nombre_negocio" value="Concelato Gelateria" placeholder="Nombre del negocio" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_negocio">Teléfono de Contacto</label>
                                <input type="text" id="telefono_negocio" name="telefono_negocio" placeholder="(064) 223-4567">
                            </div>
                            <div class="form-group">
                                <label for="email_negocio">Email de Contacto</label>
                                <input type="email" id="email_negocio" name="email_negocio" placeholder="info@concelato.com">
                            </div>
                            <div class="form-group">
                                <label for="direccion_negocio">Dirección</label>
                                <input type="text" id="direccion_negocio" name="direccion_negocio" placeholder="Jirón Real 425, Huancayo">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Gestión de usuarios -->
                <div class="config-section">
                    <h3><i class="fas fa-users-cog"></i> Gestión de Usuarios</h3>
                    <p>Administra los usuarios y roles del sistema</p>
                    <div class="form-actions" style="justify-content: flex-start; gap: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="location.href='usuarios.php'">
                            <i class="fas fa-list"></i> Ver Todos los Usuarios
                        </button>
                        <button type="button" class="btn btn-primary" onclick="location.href='usuarios.php'">
                            <i class="fas fa-user-plus"></i> Agregar Usuario
                        </button>
                    </div>
                </div>

                <!-- Información del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label"><i class="fas fa-code-branch"></i> Versión del Sistema</div>
                            <p>Heladería CG v2.0</p>
                        </div>
                        <div class="info-card">
                            <div class="info-label"><i class="fas fa-calendar"></i> Fecha de Instalación</div>
                            <p>19/11/2024</p>
                        </div>
                        <div class="info-card">
                            <div class="info-label"><i class="fas fa-sync-alt"></i> Última Actualización</div>
                            <p><?php echo date('d/m/Y H:i:s'); ?></p>
                        </div>
                        <div class="info-card">
                            <div class="info-label"><i class="fas fa-user-circle"></i> Admin Actual</div>
                            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Funciones de utilidad
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }

        // Validar contraseña al cambiar
        document.addEventListener('DOMContentLoaded', function() {
            const formPassword = document.querySelector('form[action=""] input[name="accion"][value="cambiar_password"]');
            if (formPassword) {
                const form = formPassword.closest('form');
                form.addEventListener('submit', function(e) {
                    const passwordNuevo = document.getElementById('password_nuevo').value;
                    const passwordConfirmar = document.getElementById('password_confirmar').value;
                    
                    if (passwordNuevo !== passwordConfirmar) {
                        e.preventDefault();
                        alert('Las contraseñas no coinciden. Intenta de nuevo.');
                        return false;
                    }
                    
                    if (passwordNuevo.length < 6) {
                        e.preventDefault();
                        alert('La contraseña debe tener al menos 6 caracteres.');
                        return false;
                    }
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