<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('empleado');

// Obtener clientes
try {
    $stmt = $pdo->prepare("
        SELECT c.id_cliente, c.nombre, c.dni, c.telefono, c.direccion, c.correo, c.fecha_registro, c.nota,
               COUNT(v.id_venta) as total_compras,
               COALESCE(SUM(v.total), 0) as total_gastado
        FROM clientes c
        LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
        GROUP BY c.id_cliente, c.nombre, c.dni, c.telefono, c.direccion, c.correo, c.fecha_registro, c.nota
        ORDER BY c.fecha_registro DESC
    ");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $clientes = [];
    error_log("Error al obtener clientes: " . $e->getMessage());
}

// Manejar operaciones (solo lectura para empleado)
$mensaje = '';
$tipo_mensaje = '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado - Gestión de Clientes</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cliente-stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        
        .stat-card-cliente {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        
        .stat-card-cliente h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #64748b;
        }
        
        .stat-card-cliente p {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="empleado-container">
        <!-- Header con navegación -->
        <header class="empleado-header">
            <div class="header-content-empleado">
                <button class="menu-toggle-empleado" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="empleado-nav">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-empleado">
                    <i class="fas fa-ice-cream"></i>
                    <span>Concelato Empleado</span>
                </div>
                <nav id="empleado-nav" class="empleado-nav">
                    <ul>
                        <li><a href="index.php">
                            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                        </a></li>
                        <li><a href="ventas.php">
                            <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
                        </a></li>
                        <li><a href="inventario.php">
                            <i class="fas fa-boxes"></i> <span>Inventario</span>
                        </a></li>
                        <li><a href="pedidos_recibidos.php">
                            <i class="fas fa-list"></i> <span>Pedidos</span>
                        </a></li>
                        <li><a href="../admin/productos.php">
                            <i class="fas fa-box"></i> <span>Productos</span>
                        </a></li>
                        <li><a href="clientes.php" class="active">
                            <i class="fas fa-user-friends"></i> <span>Clientes</span>
                        </a></li>
                    </ul>
                </nav>
                <button class="logout-btn-empleado" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </button>
            </div>
        </header>

        <main class="empleado-main">
            <div class="welcome-section-empleado">
                <h1>Gestión de Clientes</h1>
                <p>Consulta la información de los clientes (solo lectura para empleados)</p>
            </div>

            <!-- Mensajes -->
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="status" aria-live="polite">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($mensaje); ?></span>
                </div>
            <?php endif; ?>

            <div class="card-empleado">
                <div class="empleados-actions-empleado">
                    <div class="search-filter-empleado">
                        <input
                            type="search"
                            id="searchCliente"
                            class="search-input-empleado"
                            placeholder="Buscar por nombre, DNI o email..."
                            aria-label="Buscar clientes"
                            onkeyup="filtrarClientes()"
                        >
                    </div>
                </div>
            </div>

            <div class="card-empleado">
                <h2>Clientes Registrados</h2>
                <div class="table-container-empleado">
                    <table id="tablaClientes" class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th>Compras</th>
                                <th>Total Gastado</th>
                                <th>Registrado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo $cliente['id_cliente']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cliente['dni'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['correo'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['direccion'] ?: 'N/A'); ?></td>
                                    <td><?php echo $cliente['total_compras']; ?></td>
                                    <td>S/. <?php echo number_format($cliente['total_gastado'], 2); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                                    <td>
                                        <button class="btn-empleado btn-secondary-empleado" onclick="verDetallesCliente(<?php echo $cliente['id_cliente']; ?>)" title="Ver detalles completos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" style="text-align: center;">No hay clientes registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
        
        function verDetallesCliente(id) {
            // Mostrar detalles en un modal o redirigir a página de detalles
            alert('Funcionalidad de ver detalles del cliente ID: ' + id + '\n(En implementación real, se mostrarían los detalles completos)');
        }
        
        function filtrarClientes() {
            const input = document.getElementById('searchCliente');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('tablaClientes');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
        
        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>