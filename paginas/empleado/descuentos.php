<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');

// Include database connection
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Obtener descuentos activos
try {
    $stmt_descuentos = $pdo->prepare("
        SELECT d.*,
               (SELECT COUNT(*) FROM cupones c WHERE c.id_descuento = d.id_descuento) as cantidad_cupones
        FROM descuentos d
        WHERE d.activo = 1
        AND (d.fecha_fin IS NULL OR d.fecha_fin >= NOW())
        ORDER BY d.fecha_inicio DESC
    ");
    $stmt_descuentos->execute();
    $descuentos = $stmt_descuentos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener cupones activos
    $stmt_cupones = $pdo->prepare("
        SELECT c.*, d.nombre as descuento_nombre, d.tipo, d.valor, d.uso_maximo, d.veces_usado
        FROM cupones c
        JOIN descuentos d ON c.id_descuento = d.id_descuento
        WHERE c.activo = 1
        AND (c.fecha_vencimiento IS NULL OR c.fecha_vencimiento >= NOW())
        ORDER BY c.fecha_creacion DESC
    ");
    $stmt_cupones->execute();
    $cupones = $stmt_cupones->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $descuentos = [];
    $cupones = [];
    error_log("Error al obtener descuentos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heladería Concelato - Empleado - Descuentos y Cupones</title>
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
                        <li><a href="descuentos.php" class="active">
                            <i class="fas fa-tags"></i> <span>Descuentos</span>
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
                <h1>Descuentos y Cupones Activos</h1>
                <p>Consulta los descuentos y cupones disponibles para aplicar en ventas</p>
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

            <div class="card-empleado">
                <h2>Descuentos Activos</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Período</th>
                                <th>Uso Máximo</th>
                                <th>Veces Usado</th>
                                <th>Cupones</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($descuentos)): ?>
                                <?php foreach ($descuentos as $descuento): ?>
                                <tr>
                                    <td><?php echo $descuento['id_descuento']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($descuento['nombre']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $descuento['tipo'] === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($descuento['tipo'] === 'porcentaje'): ?>
                                            <span class="badge badge-warning"><?php echo number_format($descuento['valor'], 2); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">S/. <?php echo number_format($descuento['valor'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($descuento['fecha_inicio'])); ?> 
                                        <?php if ($descuento['fecha_fin']): ?>
                                            - <?php echo date('d/m/Y', strtotime($descuento['fecha_fin'])); ?>
                                        <?php else: ?>
                                            - Sin fecha límite
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($descuento['uso_maximo']): ?>
                                            <?php echo $descuento['veces_usado']; ?>/<?php echo $descuento['uso_maximo']; ?>
                                        <?php else: ?>
                                            Ilimitado
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $descuento['veces_usado']; ?></td>
                                    <td><?php echo $descuento['cantidad_cupones']; ?> cupones</td>
                                    <td>
                                        <span class="status-badge active">Activo</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No hay descuentos activos</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-empleado">
                <h2>Cupones Disponibles</h2>
                <div class="table-container-empleado">
                    <table class="empleado-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Fecha Vencimiento</th>
                                <th>Uso Restante</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($cupones)): ?>
                                <?php foreach ($cupones as $cupon): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cupon['codigo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cupon['descuento_nombre']); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $cupon['tipo'] === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cupon['tipo'] === 'porcentaje'): ?>
                                            <span class="badge badge-warning"><?php echo number_format($cupon['valor'], 2); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">S/. <?php echo number_format($cupon['valor'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cupon['fecha_vencimiento']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($cupon['fecha_vencimiento'])); ?>
                                        <?php else: ?>
                                            Sin fecha límite
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $usos_restantes = $cupon['uso_maximo'] - $cupon['veces_usado'];
                                        echo $usos_restantes > 0 ? $usos_restantes : 'Agotado';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!$cupon['activo'] || ($cupon['fecha_vencimiento'] && strtotime($cupon['fecha_vencimiento']) < time())): ?>
                                            <span class="status-badge inactive">Inactivo</span>
                                        <?php else: ?>
                                            <span class="status-badge active">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No hay cupones disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-empleado">
                <h2>Instrucciones de Uso</h2>
                <div class="instructions">
                    <ol>
                        <li><strong>Identificar el código de cupón</strong>: Localiza el código del cupón en la tabla inferior.</li>
                        <li><strong>Verificar vigencia</strong>: Asegúrate de que el cupón esté activo y no haya expirado.</li>
                        <li><strong>Aplicar en ventas</strong>: Durante la venta, introduce el código de cupón en el campo correspondiente.</li>
                        <li><strong>Validar descuento</strong>: El sistema verificará automáticamente la validez del cupón.</li>
                        <li><strong>Aplicar descuento</strong>: Si es válido, el descuento se aplicará al total.</li>
                    </ol>
                    <p><strong>Nota:</strong> Los descuentos automáticos están configurados para aplicarse según condiciones específicas (fecha, productos, clientes, etc.)</p>
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

        // Toggle mobile menu
        document.querySelector('.menu-toggle-empleado').addEventListener('click', function() {
            const nav = document.getElementById('empleado-nav');
            nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
        });
    </script>
    
    <style>
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #059669;
        }
        
        .instructions {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border-left: 4px solid #3b82f6;
        }
        
        .instructions ol {
            padding-left: 1.5rem;
        }
        
        .instructions li {
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .empleado-table th,
            .empleado-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .instructions {
                padding: 1rem;
            }
        }
    </style>
</body>
</html>