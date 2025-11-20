<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['id'])) {
    $id_venta = (int)$_GET['id'];
    
    try {
        // Obtener información de la venta
        $stmt_venta = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado, v.id_cliente, v.id_vendedor,
                   c.nombre as cliente_nombre,
                   ve.nombre as vendedor_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN vendedores ve ON v.id_vendedor = ve.id_vendedor
            WHERE v.id_venta = :id_venta
        ");
        $stmt_venta->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt_venta->execute();
        
        $venta = $stmt_venta->fetch(PDO::FETCH_ASSOC);
        
        if (!$venta) {
            echo json_encode([
                'success' => false,
                'message' => 'Venta no encontrada'
            ]);
            exit;
        }
        
        // Obtener detalles de la venta
        $stmt_detalle = $pdo->prepare("
            SELECT dv.cantidad, dv.precio_unit, dv.subtotal, p.nombre as nombre_producto, p.id_producto
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = :id_venta
            ORDER BY dv.id_detalle ASC
        ");
        $stmt_detalle->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt_detalle->execute();
        
        $items = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
        
        // Estructurar la respuesta exactamente como la espera JavaScript
        echo json_encode([
            'success' => true,
            'detalle' => [
                'id_venta' => $venta['id_venta'],
                'fecha' => $venta['fecha'],
                'total' => $venta['total'],
                'estado' => $venta['estado'],
                'cliente_nombre' => $venta['cliente_nombre'],
                'vendedor_nombre' => $venta['vendedor_nombre'],
                'items' => $items
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
    } catch(PDOException $e) {
        error_log("Error al obtener detalle de venta: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener detalle de venta'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de venta no proporcionado'
    ]);
}
?>