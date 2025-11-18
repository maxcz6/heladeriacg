<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

// Manejar operaciones de configuración
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'configurar') {
        // Aquí iría la lógica para guardar configuraciones
        // Por ejemplo: horarios, información de contacto, etc.
        $mensaje = 'Configuración actualizada exitosamente';
        $tipo_mensaje = 'success';
    }
}

// En una implementación real, aquí se obtendrían las configuraciones actuales
// Por ahora, usaremos valores por defecto
$configuracion = [
    'nombre_negocio' => 'Concelato Gelateria',
    'direccion' => 'Av. Principal 123',
    'telefono' => '999 888 777',
    'correo' => 'info@concelatogelateria.com',
    'hora_inicio' => '10:00',
    'hora_fin' => '22:00',
    'dias_atencion' => 'Lunes a Domingo',
    'moneda' => 'PEN (Soles)',
    'tax_rate' => '0.18',
    'metodo_pago' => 'Efectivo y Tarjeta'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - Concelato Gelateria</title>
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
                    Concelato Gelateria - Configuración
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
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="admin-main">
            <div class="welcome-section">
                <h1>Configuración del Sistema</h1>
                <p>Aquí puedes administrar la configuración general del sistema</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <div class="configuracion-container">
                <form id="configuracionForm" method="POST">
                    <input type="hidden" name="accion" value="configurar">
                    
                    <div class="config-section">
                        <h2>Información del Negocio</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_negocio">Nombre del Negocio</label>
                                <input type="text" id="nombre_negocio" name="nombre_negocio" value="<?php echo htmlspecialchars($configuracion['nombre_negocio']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($configuracion['direccion']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($configuracion['telefono']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($configuracion['correo']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="config-section">
                        <h2>Horarios de Atención</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="hora_inicio">Hora de Inicio</label>
                                <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo $configuracion['hora_inicio']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="hora_fin">Hora de Fin</label>
                                <input type="time" id="hora_fin" name="hora_fin" value="<?php echo $configuracion['hora_fin']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="dias_atencion">Días de Atención</label>
                            <input type="text" id="dias_atencion" name="dias_atencion" value="<?php echo htmlspecialchars($configuracion['dias_atencion']); ?>">
                        </div>
                    </div>
                    
                    <div class="config-section">
                        <h2>Configuración de Ventas</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="moneda">Moneda</label>
                                <input type="text" id="moneda" name="moneda" value="<?php echo htmlspecialchars($configuracion['moneda']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="tax_rate">Tasa de Impuestos (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" step="0.01" value="<?php echo $configuracion['tax_rate']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="metodo_pago">Métodos de Pago</label>
                            <input type="text" id="metodo_pago" name="metodo_pago" value="<?php echo htmlspecialchars($configuracion['metodo_pago']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn cancel" onclick="location.reload()">Cancelar</button>
                        <button type="submit" class="btn save">Guardar Configuración</button>
                    </div>
                </form>
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
</body>
</html>