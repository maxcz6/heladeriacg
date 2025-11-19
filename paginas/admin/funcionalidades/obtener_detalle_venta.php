<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_venta = $_GET['id'];
    
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
        $stmt_venta->bindParam(':id_venta', $id_venta);
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
            SELECT dv.cantidad, dv.precio_unit, dv.subtotal, p.nombre as producto_nombre
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = :id_venta
        ");
        $stmt_detalle->bindParam(':id_venta', $id_venta);
        $stmt_detalle->execute();
        
        $detalle = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'venta' => $venta,
            'detalle' => $detalle
        ]);
    } catch(PDOException $e) {
        error_log("Error al obtener detalle de venta: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener detalle de venta: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de venta no proporcionado'
    ]);
}
?>