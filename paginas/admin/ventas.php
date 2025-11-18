<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener todas las ventas
$stmt_ventas = $pdo->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado, v.id_cliente, v.id_vendedor,
           c.nombre as cliente_nombre,
           ve.nombre as vendedor_nombre
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    LEFT JOIN vendedores ve ON v.id_vendedor = ve.id_vendedor
    ORDER BY v.fecha DESC
");
$stmt_ventas->execute();
$ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - Concelato Gelateria</title>
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
                    Concelato Gelateria - Ventas
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
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
                <h1>Gestión de Ventas</h1>
                <p>Aquí puedes ver y administrar todas las ventas realizadas</p>
            </div>

            <div class="ventas-actions">
                <div class="search-filter">
                    <input type="text" id="searchVenta" placeholder="Buscar venta..." onkeyup="searchVentas()">
                    <select id="filterStatus" onchange="filterVentas()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente">Pendientes</option>
                        <option value="Procesada">Procesadas</option>
                        <option value="Anulada">Anuladas</option>
                        <option value="Finalizada">Finalizadas</option>
                    </select>
                    <div class="export-buttons">
                        <button class="action-btn export" onclick="exportarVentas('csv')">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                        <button class="action-btn export" onclick="exportarVentas('xls')">
                            <i class="fas fa-file-excel"></i> XLS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de ventas -->
            <div class="table-container">
                <table class="ventas-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ventasTable">
                        <?php foreach ($ventas as $venta): ?>
                        <tr data-status="<?php echo $venta['estado']; ?>">
                            <td class="venta-id"><?php echo $venta['id_venta']; ?></td>
                            <td class="venta-fecha"><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                            <td class="venta-cliente"><?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Desconocido'); ?></td>
                            <td class="venta-vendedor"><?php echo htmlspecialchars($venta['vendedor_nombre'] ?: 'Desconocido'); ?></td>
                            <td class="venta-total">S/. <?php echo number_format($venta['total'], 2); ?></td>
                            <td class="venta-estado">
                                <span class="status-badge 
                                    <?php echo $venta['estado'] === 'Procesada' ? 'delivered' : ($venta['estado'] === 'Pendiente' ? 'pending' : ($venta['estado'] === 'Anulada' ? 'cancelled' : 'completed')); ?>">
                                    <?php echo htmlspecialchars($venta['estado']); ?>
                                </span>
                            </td>
                            <td class="venta-acciones">
                                <button class="action-btn view" onclick="verDetalle(<?php echo $venta['id_venta']; ?>)">
                                    <i class="fas fa-eye"></i> Detalle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal para ver detalle de venta -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetalleModal()">&times;</span>
            <h2>Detalle de Venta</h2>
            <div id="detalleContent">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        function verDetalle(id_venta) {
            // Cargar los detalles de la venta
            fetch(`obtener_detalle_venta.php?id=${id_venta}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const venta = data.venta;
                    const detalle = data.detalle;
                    
                    let detalleHtml = `
                        <div class="venta-detail-info">
                            <h3>Detalle de Venta #${venta.id_venta}</h3>
                            <p><strong>Cliente:</strong> ${venta.cliente_nombre || 'Desconocido'}</p>
                            <p><strong>Vendedor:</strong> ${venta.vendedor_nombre || 'Desconocido'}</p>
                            <p><strong>Fecha:</strong> ${venta.fecha}</p>
                            <p><strong>Estado:</strong> ${venta.estado}</p>
                            <p><strong>Total:</strong> S/. ${parseFloat(venta.total).toFixed(2)}</p>
                        </div>
                        <h4>Productos:</h4>
                        <div class="venta-products-list">
                    `;
                    
                    detalle.forEach(item => {
                        detalleHtml += `<div class="product-item">${item.producto_nombre} - ${item.cantidad} x S/.${parseFloat(item.precio_unit).toFixed(2)} = S/.${parseFloat(item.subtotal).toFixed(2)}</div>`;
                    });
                    
                    detalleHtml += `</div>`;
                    
                    document.getElementById('detalleContent').innerHTML = detalleHtml;
                    document.getElementById('detalleModal').style.display = 'block';
                } else {
                    alert('Error al obtener detalles de venta: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al obtener detalles de venta');
            });
        }

        function closeDetalleModal() {
            document.getElementById('detalleModal').style.display = 'none';
        }

        function exportarVentas(formato) {
            if (formato === 'csv') {
                window.location.href = 'exportar_ventas_csv.php';
            } else if (formato === 'xls') {
                window.location.href = 'exportar_ventas_xls.php';
            } else {
                alert('Formato de exportación no válido');
            }
        }

        function searchVentas() {
            const input = document.getElementById('searchVenta');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#ventasTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const idCell = row.cells[0].textContent.toLowerCase();
                const clienteCell = row.cells[2].textContent.toLowerCase();
                
                if (idCell.includes(filter) || clienteCell.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        function filterVentas() {
            const filter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#ventasTable tr');
            
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

        // Cerrar modal si se hace clic fuera de él
        window.onclick = function(event) {
            if (event.target === document.getElementById('detalleModal')) {
                closeDetalleModal();
            }
        }
    </script>
</body>
</html>