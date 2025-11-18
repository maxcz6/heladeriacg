<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

// Obtener el ID del empleado basado en el usuario
$stmt_empleado = $pdo->prepare("SELECT id_vendedor FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado) {
    // Si no hay empleado asociado, crear uno
    $stmt_insert = $pdo->prepare("INSERT INTO vendedores (nombre, dni, telefono, correo, turno) VALUES (:nombre, :dni, :telefono, :correo, :turno)");
    $stmt_insert->bindParam(':nombre', $_SESSION['username']);
    $stmt_insert->bindParam(':dni', '00000000');
    $stmt_insert->bindParam(':telefono', '000000000');
    $stmt_insert->bindParam(':correo', 'empleado@concelato.com');
    $stmt_insert->bindParam(':turno', 'Mañana');
    $stmt_insert->execute();
    
    $id_vendedor = $pdo->lastInsertId();
    
    // Actualizar el usuario para asociar con el empleado
    $stmt_update = $pdo->prepare("UPDATE usuarios SET id_vendedor = :id_vendedor WHERE id_usuario = :id_usuario");
    $stmt_update->bindParam(':id_vendedor', $id_vendedor);
    $stmt_update->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_update->execute();
} else {
    $id_vendedor = $usuario_empleado['id_vendedor'];
}

$productos = obtenerProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empleado - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/empleado/estilos_empleado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="employee-container">
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Empleado
                </div>
                <nav>
                    <ul>
                        <li><a href="ventas.php"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
                        <li><a href="inventario.php"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li><a href="pedidos_recibidos.php"><i class="fas fa-list"></i> Pedidos</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="employee-main">
            <div class="welcome-section">
                <h1>Panel de Empleado</h1>
                <p>Bienvenido de vuelta, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>0</h3>
                        <p>Ventas Hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>S/. 0.00</h3>
                        <p>Total Hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="stat-info">
                        <h3>0</h3>
                        <p>Productos Bajos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3>0</h3>
                        <p>Pedidos Pendientes</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3>Registrar Venta</h3>
                    <p>Procesar una nueva venta</p>
                    <a href="ventas.php" class="action-btn">Iniciar Venta</a>
                </div>
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3>Control de Inventario</h3>
                    <p>Ver y actualizar stock</p>
                    <a href="inventario.php" class="action-btn">Ver Inventario</a>
                </div>
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Gestionar Pedidos</h3>
                    <p>Ver y actualizar estado de pedidos</p>
                    <a href="pedidos_recibidos.php" class="action-btn">Ver Pedidos</a>
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
</body>
</html>