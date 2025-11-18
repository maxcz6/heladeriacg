<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('cliente');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    // Obtener el ID del cliente
    $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt_cliente->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_cliente->execute();
    $usuario_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    $id_cliente = $usuario_cliente['id_cliente'];
    $productos = $input['productos'];
    $metodo_entrega = $input['metodo_entrega'];
    
    // Validar stock antes de crear el pedido
    foreach ($productos as $producto) {
        $stmt_stock = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id_producto");
        $stmt_stock->bindParam(':id_producto', $producto['id']);
        $stmt_stock->execute();
        $stock_actual = $stmt_stock->fetchColumn();
        
        if ($producto['quantity'] > $stock_actual) {
            echo json_encode(['success' => false, 'message' => 'No hay suficiente stock para ' . $producto['name']]);
            exit;
        }
    }
    
    // Crear el pedido
    $resultado = crearPedido($id_cliente, $productos, $metodo_entrega);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'id_venta' => $resultado['id_venta'],
            'total' => $resultado['total']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>