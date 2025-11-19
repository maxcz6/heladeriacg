<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Para el header
$current_page = 'ventas';

$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

// Construir query según filtro
$query = "
    SELECT v.*, c.nombre as cliente_nombre
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
";

if ($filtro_estado) {
    $query .= " WHERE v.estado = :estado";
}

$query .= " ORDER BY v.fecha DESC";

$stmt_ventas = $pdo->prepare($query);

if ($filtro_estado) {
    $stmt_ventas->bindParam(':estado', $filtro_estado);
}

$stmt_ventas->execute();
$ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas de ventas
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        SUM(total) as monto_total,
        AVG(total) as monto_promedio,
        COUNT(CASE WHEN estado = 'Procesada' THEN 1 END) as procesadas,
        COUNT(CASE WHEN estado = 'Pendiente' THEN 1 END) as pendientes,
        COUNT(CASE WHEN estado = 'Anulada' THEN 1 END) as anuladas
    FROM ventas
    WHERE DATE(fecha) = CURDATE()
");
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/admin/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación -->
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
                <a href="ventas.php" class="active">
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
                <a href="configuracion.php">
                    <i class="fas fa-cog"></i> <span>Configuración</span>
                </a>
                <a href="../../conexion/cerrar_sesion.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main content -->
    <main class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Reporte de Ventas</h1>
            <p>Historial de transacciones y estadísticas del día</p>
        </div>

        <!-- Stats Grid -->
        <section class="dashboard-stats" aria-label="Estadísticas de ventas">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_ventas'] ?? 0; ?></h3>
                    <p>Total de Ventas</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>S/ <?php echo number_format($stats['monto_total'] ?? 0, 2); ?></h3>
                    <p>Monto Total</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['procesadas'] ?? 0; ?></h3>
                    <p>Procesadas</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pendientes'] ?? 0; ?></h3>
                    <p>Pendientes</p>
                </div>
            </div>
        </section>

        <!-- Search and Actions Card -->
        <div class="card">
            <div class="card-body">
                <div class="ventas-actions">
                    <div class="search-filter">
                        <input 
                            type="search" 
                            id="searchVenta"
                            class="search-input"
                            placeholder="Buscar por venta, cliente..."
                            aria-label="Buscar ventas"
                            data-filter-table="tablaVentas">
                        <select
                            id="filterEstado"
                            class="filter-select"
                            onchange="filterByStatus(this.value)"
                            aria-label="Filtrar por estado">
                            <option value="">Todos los estados</option>
                            <option value="Procesada" <?php echo $filtro_estado === 'Procesada' ? 'selected' : ''; ?>>Procesada</option>
                            <option value="Pendiente" <?php echo $filtro_estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Anulada" <?php echo $filtro_estado === 'Anulada' ? 'selected' : ''; ?>>Anulada</option>
                        </select>
                    </div>
                    <button class="action-btn export" onclick="exportToCSV('tablaVentas', 'ventas.csv')">
                        <i class="fas fa-download"></i> Exportar CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaVentas" class="tabla-admin" role="table">
                        <thead>
                            <tr role="row">
                                <th role="columnheader" aria-sort="none" onclick="TableSorter.sortTable(this)">
                                    <i class="fas fa-arrows-alt-v"></i> ID Venta
                                </th>
                                <th role="columnheader" aria-sort="none">Cliente</th>
                                <th role="columnheader" aria-sort="none">Fecha</th>
                                <th role="columnheader" aria-sort="none">Total</th>
                                <th role="columnheader" aria-sort="none">Estado</th>
                                <th role="columnheader" aria-label="Acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                            <tr role="row" tabindex="0">
                                <td><span class="venta-id">#<?php echo $venta['id_venta']; ?></span></td>
                                <td><?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente Anónimo'); ?></td>
                                <td><span class="venta-fecha"><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></span></td>
                                <td><strong class="venta-total">S/ <?php echo number_format($venta['total'], 2); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $venta['estado'])); 
                                    ?>">
                                        <?php echo htmlspecialchars($venta['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button 
                                        class="action-btn" 
                                        onclick="verDetalleVenta(<?php echo $venta['id_venta']; ?>)"
                                        aria-label="Ver detalle de venta #<?php echo $venta['id_venta']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($ventas)): ?>
                    <div class="empty-state">
                        <p>No hay ventas registradas<?php echo $filtro_estado ? ' con el filtro seleccionado' : ''; ?>.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Ver Detalle de Venta -->
    <div id="modalDetalleVenta" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalDetalleTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalDetalleTitle">Detalle de Venta</h2>
                <button class="modal-close" aria-label="Cerrar diálogo" onclick="closeModal('modalDetalleVenta')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalDetalleBody">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel" onclick="closeModal('modalDetalleVenta')">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <script src="/heladeriacg/js/admin/script.js"></script>
    <script>
        function filterByStatus(status) {
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('estado', status);
            } else {
                url.searchParams.delete('estado');
            }
            window.location = url.toString();
        }

        function verDetalleVenta(idVenta) {
            fetch(`funcionalidades/obtener_detalle_venta.php?id=${idVenta}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const detalle = data.detalle;
                        let html = `
                            <div class="detalle-venta">
                                <div class="info-row">
                                    <label>ID Venta:</label>
                                    <strong>#${detalle.id_venta}</strong>
                                </div>
                                <div class="info-row">
                                    <label>Cliente:</label>
                                    <strong>${detalle.cliente_nombre || 'Cliente Anónimo'}</strong>
                                </div>
                                <div class="info-row">
                                    <label>Fecha:</label>
                                    <strong>${new Date(detalle.fecha).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</strong>
                                </div>
                                <div class="info-row">
                                    <label>Estado:</label>
                                    <strong>${detalle.estado}</strong>
                                </div>
                                <div class="info-row">
                                    <label>Total:</label>
                                    <strong class="text-success">S/ ${parseFloat(detalle.total).toFixed(2)}</strong>
                                </div>
                        `;

                        if (detalle.items && detalle.items.length > 0) {
                            html += '<hr><h4>Artículos:</h4><ul>';
                            detalle.items.forEach(item => {
                                html += `<li>${item.nombre_producto} x${item.cantidad} = S/ ${parseFloat(item.subtotal).toFixed(2)}</li>`;
                            });
                            html += '</ul>';
                        }

                        html += '</div>';

                        document.getElementById('modalDetalleBody').innerHTML = html;
                        document.getElementById('modalDetalleTitle').textContent = `Detalle de Venta #${detalle.id_venta}`;
                        openModal('modalDetalleVenta');
                    } else {
                        showNotification('Error al obtener detalle', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
        }
    </script>

    <style>
        .detalle-venta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row label {
            font-weight: 600;
            color: #1f2937;
        }

        .info-row strong {
            color: #0891b2;
        }

        .text-success {
            color: #059669;
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-completado {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #78350f;
        }

        .badge-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }

        .filter-select {
            padding: 0.8rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            transition: all 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(6, 182, 202, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .empty-state a {
            color: #0891b2;
            text-decoration: none;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>