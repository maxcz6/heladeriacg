<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
verificarSesion();
verificarRol('empleado');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pedidos_recibidos.php');
    exit();
}

$id_venta = $_GET['id'];

try {
    // Obtener información de la venta
    $stmt_venta = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, c.dni, c.telefono, c.direccion,
               (v.total / 1.18) as subtotal,
               (v.total / 1.18) * 0.18 as igv
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        WHERE v.id_venta = :id_venta
    ");
    $stmt_venta->bindParam(':id_venta', $id_venta);
    $stmt_venta->execute();
    $venta = $stmt_venta->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        $_SESSION['mensaje_error'] = 'Venta no encontrada';
        header('Location: pedidos_recibidos.php');
        exit();
    }

    // Obtener detalles de la venta
    $stmt_detalle = $pdo->prepare("
        SELECT dv.*, p.nombre as producto_nombre, p.sabor
        FROM detalle_ventas dv
        JOIN productos p ON dv.id_producto = p.id_producto
        WHERE dv.id_venta = :id_venta
    ");
    $stmt_detalle->bindParam(':id_venta', $id_venta);
    $stmt_detalle->execute();
    $detalle_venta = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['mensaje_error'] = 'Error al obtener detalles de la venta';
    header('Location: pedidos_recibidos.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta #<?php echo $venta['id_venta']; ?> - Heladería Concelato</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/modernos_estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        <li><a href="inventario.php">
                            <i class="fas fa-box"></i> <span>Productos</span>
                        </a></li>
                        <li><a href="clientes.php">
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
                <h1>Detalle de Venta #<?php echo $venta['id_venta']; ?></h1>
                <p>Información completa de la venta realizada</p>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success" role="status" aria-live="polite">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['mensaje_exito']; ?></span>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-error" role="status" aria-live="polite">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['mensaje_error']; ?></span>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <!-- Información de la venta -->
            <div class="card-empleado">
                <h2>Información de la Venta</h2>
                <div class="venta-detalle-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>ID Venta:</strong>
                            <span>#<?php echo $venta['id_venta']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha:</strong>
                            <span><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Estado:</strong>
                            <span class="status-badge <?php echo $venta['estado'] === 'Procesada' ? 'active' : ($venta['estado'] === 'Pendiente' ? 'warning' : ($venta['estado'] === 'Procesando' ? 'active' : 'inactive')); ?>">
                                <?php echo $venta['estado']; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Tipo Comprobante:</strong>
                            <span><?php echo ucfirst($venta['tipo_comprobante'] ?? 'Venta'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Subtotal:</strong>
                            <span>S/. <?php echo number_format($venta['subtotal'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>IGV (18%):</strong>
                            <span>S/. <?php echo number_format($venta['igv'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Total:</strong>
                            <span class="total-venta">S/. <?php echo number_format($venta['total'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del cliente -->
            <?php if ($venta['cliente_nombre']): ?>
            <div class="card-empleado">
                <h2>Información del Cliente</h2>
                <div class="venta-detalle-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Nombre:</strong>
                            <span><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>DNI:</strong>
                            <span><?php echo htmlspecialchars($venta['dni'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Teléfono:</strong>
                            <span><?php echo htmlspecialchars($venta['telefono'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Dirección:</strong>
                            <span><?php echo htmlspecialchars($venta['direccion'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Detalle de productos -->
            <div class="card-empleado">
                <h2>Productos de la Venta</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Sabor</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle_venta as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['sabor']); ?></td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td>S/. <?php echo number_format($item['precio_unit'], 2); ?></td>
                                <td>S/. <?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL:</td>
                                <td style="font-weight: bold;">S/. <?php echo number_format($venta['total'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card-empleado" style="text-align: center;">
                <h3>Opciones de Impresión</h3>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
                    <button class="btn-empleado btn-primary-empleado" onclick="imprimirComprobante('boleta')">
                        <i class="fas fa-print"></i> Imprimir Boleta
                    </button>
                    <button class="btn-empleado btn-secondary-empleado" onclick="imprimirComprobante('factura')">
                        <i class="fas fa-file-invoice"></i> Imprimir Factura
                    </button>
                    <a href="pedidos_recibidos.php" class="btn-empleado btn-outline-empleado">
                        <i class="fas fa-arrow-left"></i> Volver a Pedidos
                    </a>
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

        function imprimirComprobante(tipo) {
            // Simular impresión de comprobante
            const ventaInfo = {
                id: <?php echo $venta['id_venta']; ?>,
                fecha: '<?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?>',
                cliente: '<?php echo addslashes(htmlspecialchars($venta['cliente_nombre'] ?: 'Cliente Público')); ?>',
                subtotal: <?php echo $venta['subtotal']; ?>,
                igv: <?php echo $venta['igv']; ?>,
                total: <?php echo $venta['total']; ?>,
                tipo: tipo.toUpperCase(),
                tipo_comprobante: '<?php echo $venta['tipo_comprobante']; ?>'
            };

            // Crear ventana de impresión
            const ventana = window.open('', '_blank', 'height=600,width=600');
            ventana.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>` + tipo.charAt(0).toUpperCase() + tipo.slice(1) + ` #` + ventaInfo.id + `</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .comprobante { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
                        .encabezado { text-align: center; margin-bottom: 20px; }
                        .cliente-info { margin: 10px 0; }
                        .items { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        .items th, .items td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        .items th { background-color: #f2f2f2; }
                        .totales { margin-top: 15px; text-align: right; }
                        .totales div { margin: 5px 0; }
                        .total-final { font-weight: bold; font-size: 1.1em; }
                        .footer { margin-top: 20px; text-align: center; font-size: 0.9em; }
                    </style>
                </head>
                <body>
                    <div class="comprobante">
                        <div class="encabezado">
                            <h2>HELADERÍA CONCELAITO</h2>
                            <p>RUC: 12345678901</p>
                            <h3>` + ventaInfo.tipo + ` ELECTRÓNICA</h3>
                            <p>N° 001-000000` + ventaInfo.id + `</p>
                        </div>
                        
                        <div class="cliente-info">
                            <p><strong>Cliente:</strong> ` + ventaInfo.cliente + `</p>
                            <p><strong>Fecha:</strong> ` + ventaInfo.fecha + `</p>
                            <p><strong>Tipo:</strong> ` + ventaInfo.tipo_comprobante.charAt(0).toUpperCase() + ventaInfo.tipo_comprobante.slice(1) + `</p>
                        </div>
                        
                        <table class="items">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Cant.</th>
                                    <th>P. Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Venta de helados</td>
                                    <td>1</td>
                                    <td>S/. ` + ventaInfo.total.toFixed(2) + `</td>
                                    <td>S/. ` + ventaInfo.total.toFixed(2) + `</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="totales">
                            <div>Subtotal: S/. ` + ventaInfo.subtotal.toFixed(2) + `</div>
                            <div>IGV (18%): S/. ` + ventaInfo.igv.toFixed(2) + `</div>
                            <div class="total-final">TOTAL: S/. ` + ventaInfo.total.toFixed(2) + `</div>
                        </div>
                        
                        <div class="footer">
                            <hr>
                            <p>Gracias por su compra</p>
                            <p>Heladería Concelato - Servicio de Calidad</p>
                        </div>
                    </div>
                </body>
                </html>
            `);
            ventana.document.close();
            ventana.focus();
        }

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.querySelector('.empleado-nav ul');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>

    <style>
        .venta-detalle-info {
            background: var(--empleado-card-bg);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-item strong {
            color: var(--empleado-text-light);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .info-item span {
            color: var(--empleado-text);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .total-venta {
            font-weight: bold;
            color: var(--empleado-primary);
        }
        
        .total-row {
            background: var(--empleado-card-bg);
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .venta-detalle-info {
                padding: 1rem;
            }
        }
    </style>
</body>
</html>