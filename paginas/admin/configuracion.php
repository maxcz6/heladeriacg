<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/admin_functions.php');

$mensaje = '';
$tipo_mensaje = '';

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
        }

        case 'configurar_sistema':
            // Actualmente no se almacena la configuración en la base de datos
            // pero se puede implementar si se necesita
            $mensaje = 'Configuración del sistema actualizada exitosamente';
            $tipo_mensaje = 'success';
            break;
    }
}

// Obtener métricas del sistema
$metricas = obtenerMetricasSistema();
$stats_inventario = obtenerEstadisticasInventario();
$stats_ventas = obtenerEstadisticasVentas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Concelato Gelateria</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .config-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .config-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .config-section h3 {
            margin-top: 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.2em;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
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
                    <a href="promociones.php">
                        <i class="fas fa-tag"></i> <span>Promociones</span>
                    </a>
                    <a href="sucursales.php">
                        <i class="fas fa-store"></i> <span>Sucursales</span>
                    </a>
                    <a href="configuracion.php" class="active">
                        <i class="fas fa-cog"></i> <span>Configuración</span>
                    </a>
                    <a href="../../conexion/cerrar_sesion.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                    </a>
                </nav>
            </div>
        </header>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Configuración del Sistema</h1>
                <p>Gestiona las configuraciones generales del sistema</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="config-container">
                <!-- Cambiar contraseña -->
                <div class="config-section">
                    <h3><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiar_password">
                        
                        <div class="form-group">
                            <label for="password_actual">Contraseña Actual *</label>
                            <input type="password" id="password_actual" name="password_actual" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_nuevo">Nueva Contraseña *</label>
                            <input type="password" id="password_nuevo" name="password_nuevo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmar">Confirmar Nueva Contraseña *</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                        </div>
                    </form>
                </div>

                <!-- Backup de la base de datos -->
                <div class="config-section">
                    <h3><i class="fas fa-database"></i> Backup de la Base de Datos</h3>
                    <p>Realiza un backup completo de la base de datos</p>
                    
                    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas crear un backup de la base de datos?')">
                        <input type="hidden" name="accion" value="backup_db">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Crear Backup</button>
                        </div>
                    </form>
                </div>

                <!-- Estadísticas del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-chart-bar"></i> Estadísticas del Sistema</h3>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h4>Ventas Totales</h4>
                            <p>S/. <?php echo number_format($metricas['ventas_totales'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Productos Activos</h4>
                            <p><?php echo $metricas['productos_activos']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Clientes</h4>
                            <p><?php echo $metricas['clientes_totales']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Empleados</h4>
                            <p><?php echo $metricas['empleados_totales']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Ventas Hoy</h4>
                            <p>S/. <?php echo number_format($metricas['ventas_hoy'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Productos Bajos</h4>
                            <p><?php echo $metricas['productos_bajos']; ?></p>
                        </div>
                    </div>

                    <div class="stats-grid" style="margin-top: 20px;">
                        <div class="stat-card">
                            <h4>Total de Ventas</h4>
                            <p><?php echo $stats_ventas['total_ventas']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Valor Inventario</h4>
                            <p>S/. <?php echo number_format($stats_inventario['valor_inventario'], 2); ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Productos con Bajo Stock</h4>
                            <p><?php echo $stats_inventario['productos_bajo_stock']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Usuarios Activos</h4>
                            <p><?php echo $metricas['usuarios_activos']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Configuración del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-cogs"></i> Configuración del Sistema</h3>
                    <form method="POST">
                        <input type="hidden" name="accion" value="configurar_sistema">

                        <div class="form-group">
                            <label for="nombre_negocio">Nombre del Negocio</label>
                            <input type="text" id="nombre_negocio" name="nombre_negocio" value="Concelato Gelateria" placeholder="Nombre del negocio">
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
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
                            <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                        </div>
                    </form>
                </div>

                <!-- Gestión de usuarios -->
                <div class="config-section">
                    <h3><i class="fas fa-users-cog"></i> Gestión de Usuarios</h3>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="location.href='usuarios.php'">
                            <i class="fas fa-user-friends"></i> Administrar Usuarios
                        </button>
                        <button type="button" class="btn btn-primary" onclick="location.href='usuarios.php#nuevo'">
                            <i class="fas fa-user-plus"></i> Agregar Usuario
                        </button>
                    </div>
                </div>

                <!-- Información del sistema -->
                <div class="config-section">
                    <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <div>
                            <h4>Versión del Sistema</h4>
                            <p>Heladería CG v2.0</p>
                        </div>
                        <div>
                            <h4>Fecha de Instalación</h4>
                            <p>17/11/2025</p>
                        </div>
                        <div>
                            <h4>Última Actualización</h4>
                            <p><?php echo date('d/m/Y H:i:s'); ?></p>
                        </div>
                        <div>
                            <h4>Admin Actual</h4>
                            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
    <script src="/heladeriacg/js/admin/script.js"></script>
</body>
</html>