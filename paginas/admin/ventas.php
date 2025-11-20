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
    <link rel="stylesheet" href="/heladeriacg/css/admin/navbar.css">
    <link rel="stylesheet" href="/heladeriacg/css/admin/ventas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header con navegación -->
    <!-- Header con navegación -->
    <?php include 'includes/navbar.php'; ?>

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
        <div class="table-container">
            <table id="tablaVentas" class="ventas-table" role="table">
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
        </div>
        <?php if (empty($ventas)): ?>
        <div style="text-align: center; padding: 20px; background: white; margin-bottom: 24px;">
            <p><i class="fas fa-inbox"></i> No hay ventas registradas<?php echo $filtro_estado ? ' con el filtro seleccionado' : ''; ?>.</p>
        </div>
        <?php endif; ?>
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
            // URL relativa correcta - estamos en paginas/admin/ventas.php
            const url = `funcionalidades/obtener_detalle_venta.php?id=${idVenta}`;
            
            console.log('Fetching:', url, 'for id:', idVenta);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (!data || typeof data !== 'object') {
                        throw new Error('Respuesta inválida del servidor');
                    }
                    
                    if (data.success === false) {
                        showNotification(data.message || 'Error al obtener detalle', 'error');
                        return;
                    }
                    
                    if (!data.detalle) {
                        throw new Error('No hay datos de detalle en la respuesta');
                    }

                    const detalle = data.detalle;
                    const fechaFormato = new Date(detalle.fecha).toLocaleDateString('es-ES', { 
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit', 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    let html = `
                        <div class="detalle-venta">
                            <div class="info-row">
                                <label>📋 ID Venta:</label>
                                <strong>#${detalle.id_venta}</strong>
                            </div>
                            <div class="info-row">
                                <label>👤 Cliente:</label>
                                <strong>${detalle.cliente_nombre || 'Cliente Anónimo'}</strong>
                            </div>
                            <div class="info-row">
                                <label>📅 Fecha:</label>
                                <strong>${fechaFormato}</strong>
                            </div>
                            <div class="info-row">
                                <label>🔔 Estado:</label>
                                <strong><span class="badge badge-${detalle.estado.toLowerCase().replace(/\s+/g, '')}">${detalle.estado}</span></strong>
                            </div>
                            <div class="info-row">
                                <label>💵 Total:</label>
                                <strong class="text-success">S/ ${parseFloat(detalle.total).toFixed(2)}</strong>
                            </div>
                    `;

                    if (detalle.items && Array.isArray(detalle.items) && detalle.items.length > 0) {
                        html += `
                            <div class="detalle-productos">
                                <h3 class="detalle-productos-titulo">🛍️ Artículos de la Venta (${detalle.items.length})</h3>
                        `;
                        detalle.items.forEach((item, index) => {
                            const cantidad = parseInt(item.cantidad) || 1;
                            const subtotal = parseFloat(item.subtotal) || 0;
                            const precioUnitario = (cantidad > 0) ? (subtotal / cantidad).toFixed(2) : '0.00';
                            
                            html += `
                                <div class="detalle-producto-item">
                                    <div style="flex: 1;">
                                        <div class="detalle-producto-nombre">${item.nombre_producto || 'Producto'}</div>
                                        <small style="color: #6b7280;">Precio unitario: S/ ${precioUnitario}</small>
                                    </div>
                                    <div class="detalle-producto-cantidad">x${cantidad}</div>
                                    <div class="detalle-producto-precio">S/ ${subtotal.toFixed(2)}</div>
                                </div>
                            `;
                        });
                        html += '</div>';
                    } else {
                        html += `
                            <div class="detalle-productos">
                                <p style="color: #6b7280; text-align: center; padding: 20px;">
                                    No hay artículos en esta venta
                                </p>
                            </div>
                        `;
                    }

                    html += '</div>';

                    document.getElementById('modalDetalleBody').innerHTML = html;
                    document.getElementById('modalDetalleTitle').textContent = `Detalle de Venta #${detalle.id_venta}`;
                    openModal('modalDetalleVenta');
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    console.error('Stack:', error.stack);
                    showNotification('Error al obtener detalle: ' + error.message, 'error');
                });
        }
    </script>

    <style>
        .text-success {
            color: #059669;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px 12px 0 0;
        }

        .modal-header h2 {
            margin: 0;
            color: #1f2937;
            font-size: 1.3rem;
        }

        .modal-close {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: #6b7280;
        }

        .modal-close:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            background: white;
            border-radius: 0 0 12px 12px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 40px;
        }

        .btn.cancel {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn.cancel:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script src="/heladeriacg/js/admin/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            NavbarController.init();
        });
    </script>
</body>
</html>