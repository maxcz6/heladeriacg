<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

// Simular datos de auditoría
// En una implementación real, se obtendrían de la tabla audit_logs
$logs = [
    [
        'id_log' => 1,
        'tabla' => 'productos',
        'operacion' => 'INSERT',
        'referencia_id' => 15,
        'usuario' => 'admin',
        'detalles' => 'Creación de nuevo producto: Helado de Vainilla',
        'fecha' => '2025-11-17 10:30:15'
    ],
    [
        'id_log' => 2,
        'tabla' => 'ventas',
        'operacion' => 'INSERT',
        'referencia_id' => 123,
        'usuario' => 'empleado1',
        'detalles' => 'Creación de nueva venta',
        'fecha' => '2025-11-17 11:15:30'
    ],
    [
        'id_log' => 3,
        'tabla' => 'productos',
        'operacion' => 'UPDATE',
        'referencia_id' => 7,
        'usuario' => 'admin',
        'detalles' => 'Actualización de stock para: Helado de Chocolate',
        'fecha' => '2025-11-17 12:45:20'
    ],
    [
        'id_log' => 4,
        'tabla' => 'clientes',
        'operacion' => 'INSERT',
        'referencia_id' => 8,
        'usuario' => 'admin',
        'detalles' => 'Registro de nuevo cliente: Juan Pérez',
        'fecha' => '2025-11-17 14:20:05'
    ],
    [
        'id_log' => 5,
        'tabla' => 'ventas',
        'operacion' => 'UPDATE',
        'referencia_id' => 122,
        'usuario' => 'empleado2',
        'detalles' => 'Cambio de estado a Procesada',
        'fecha' => '2025-11-17 15:30:40'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del Sistema - Concelato Gelateria</title>
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
                    Concelato Gelateria - Auditoría
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
                <h1>Auditoría del Sistema</h1>
                <p>Aquí puedes ver las actividades registradas en el sistema</p>
            </div>

            <div class="logs-actions">
                <div class="search-filter">
                    <input type="text" id="searchLog" placeholder="Buscar en logs..." onkeyup="searchLogs()">
                    <select id="filterTable" onchange="filterLogs()">
                        <option value="">Todas las tablas</option>
                        <option value="productos">Productos</option>
                        <option value="ventas">Ventas</option>
                        <option value="clientes">Clientes</option>
                        <option value="empleados">Empleados</option>
                    </select>
                    <select id="filterOperation" onchange="filterLogs()">
                        <option value="">Todas las operaciones</option>
                        <option value="INSERT">Crear</option>
                        <option value="UPDATE">Actualizar</option>
                        <option value="DELETE">Eliminar</option>
                    </select>
                    <button class="action-btn secondary" onclick="exportarLogs()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </div>

            <!-- Tabla de logs de auditoría -->
            <div class="table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tabla</th>
                            <th>Operación</th>
                            <th>ID Referencia</th>
                            <th>Usuario</th>
                            <th>Detalles</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="logsTable">
                        <?php foreach ($logs as $log): ?>
                        <tr data-table="<?php echo $log['tabla']; ?>" data-operation="<?php echo $log['operacion']; ?>">
                            <td><?php echo $log['id_log']; ?></td>
                            <td><span class="log-table"><?php echo htmlspecialchars($log['tabla']); ?></span></td>
                            <td>
                                <span class="status-badge 
                                    <?php 
                                    switch($log['operacion']) {
                                        case 'INSERT': echo 'created'; break;
                                        case 'UPDATE': echo 'updated'; break;
                                        case 'DELETE': echo 'deleted'; break;
                                        default: echo 'info';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($log['operacion']); ?>
                                </span>
                            </td>
                            <td><?php echo $log['referencia_id']; ?></td>
                            <td><?php echo htmlspecialchars($log['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($log['detalles']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($log['fecha'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function searchLogs() {
            const input = document.getElementById('searchLog');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#logsTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const detallesCell = row.cells[5].textContent.toLowerCase(); // Detalles
                const usuarioCell = row.cells[4].textContent.toLowerCase(); // Usuario
                
                if (detallesCell.includes(filter) || usuarioCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function filterLogs() {
            const filterTable = document.getElementById('filterTable').value;
            const filterOperation = document.getElementById('filterOperation').value;
            const rows = document.querySelectorAll('#logsTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const table = row.getAttribute('data-table');
                const operation = row.getAttribute('data-operation');
                
                const tableMatch = filterTable === '' || table === filterTable;
                const operationMatch = filterOperation === '' || operation === filterOperation;
                
                if (tableMatch && operationMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        function exportarLogs() {
            alert('Funcionalidad de exportación de logs. En una implementación real, se generaría un archivo con los logs filtrados.');
        }
        
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>