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

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

header('Content-Type: application/json');

// Obtener el ID del empleado y sucursal
$stmt_empleado = $pdo->prepare("SELECT id_vendedor, id_sucursal FROM usuarios WHERE id_usuario = :id_usuario");
$stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_empleado->execute();
$usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

if (!$usuario_empleado || !$usuario_empleado['id_sucursal']) {
    echo json_encode(['success' => false, 'message' => 'No tiene permiso para realizar ventas']);
    exit();
}

$id_vendedor = $usuario_empleado['id_vendedor'];
$id_sucursal = $usuario_empleado['id_sucursal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_venta') {
    $productos_venta = json_decode($_POST['productos'], true);
    $id_cliente = $_POST['id_cliente'] ?? null;
    $nota = $_POST['nota'] ?? '';
    $total = floatval($_POST['total']);

    // Validar que los productos existan y tengan stock suficiente
    foreach ($productos_venta as $producto) {
        $stmt_validar = $pdo->prepare("
            SELECT i.stock_sucursal
            FROM inventario_sucursal i
            WHERE i.id_producto = :id_producto AND i.id_sucursal = :id_sucursal
        ");
        $stmt_validar->bindParam(':id_producto', $producto['id']);
        $stmt_validar->bindParam(':id_sucursal', $id_sucursal);
        $stmt_validar->execute();
        $inventario = $stmt_validar->fetch(PDO::FETCH_ASSOC);

        if (!$inventario || $inventario['stock_sucursal'] < $producto['cantidad']) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay suficiente stock para ' . $producto['nombre']
            ]);
            exit();
        }
    }

    // Preparar datos para la venta
    $venta_datos = [
        'id_cliente' => $id_cliente,
        'id_vendedor' => $id_vendedor,
        'id_sucursal' => $id_sucursal,
        'total' => $total,
        'nota' => $nota,
        'productos' => []
    ];

    // Transformar los productos para el formato de la base de datos
    foreach ($productos_venta as $producto) {
        $venta_datos['productos'][] = [
            'id_producto' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'precio_unit' => $producto['precio'],
            'subtotal' => $producto['cantidad'] * $producto['precio']
        ];
    }

    $resultado = registrarVentaSucursal($venta_datos);

    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'id_venta' => $resultado['id_venta'],
            'message' => 'Venta registrada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida'
    ]);
}
?>