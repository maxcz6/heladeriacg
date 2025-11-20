<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in and has employee role
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_venta = $_GET['id'];

    try {
        $stmt = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado,
                   c.nombre as cliente_nombre,
                   GROUP_CONCAT(CONCAT(p.nombre, ' - ', dv.cantidad, ' x S/.', dv.precio_unit) SEPARATOR ', ') as productos
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE v.id_venta = :id_venta
            GROUP BY v.id_venta, v.fecha, v.total, v.estado, c.nombre
        ");
        $stmt->bindParam(':id_venta', $id_venta);
        $stmt->execute();

        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            echo json_encode([
                'success' => true,
                'pedido' => $pedido
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al obtener detalles del pedido: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener detalles del pedido: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de venta no proporcionado'
    ]);
}
?>