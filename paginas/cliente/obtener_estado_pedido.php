<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('cliente');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_venta = $_GET['id'];
    
    // Obtener el ID del cliente basado en el usuario
    $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt_cliente->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_cliente->execute();
    $usuario_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    $id_cliente = $usuario_cliente['id_cliente'];
    
    // Verificar que el pedido pertenece al cliente
    $stmt_verificacion = $pdo->prepare("SELECT id_venta FROM ventas WHERE id_venta = :id_venta AND id_cliente = :id_cliente");
    $stmt_verificacion->bindParam(':id_venta', $id_venta);
    $stmt_verificacion->bindParam(':id_cliente', $id_cliente);
    $stmt_verificacion->execute();
    
    if ($stmt_verificacion->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado o no autorizado']);
        exit;
    }
    
    $pedido = obtenerEstadoPedido($id_venta);
    
    if ($pedido) {
        echo json_encode([
            'success' => true,
            'pedido' => $pedido
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
}
?>