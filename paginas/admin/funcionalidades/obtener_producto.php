<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id_producto, p.nombre, p.sabor, p.descripcion, p.precio, p.stock, p.activo, p.fecha_registro, p.id_proveedor,
                   prv.empresa as proveedor_nombre
            FROM productos p
            LEFT JOIN proveedores prv ON p.id_proveedor = prv.id_proveedor
            WHERE p.id_producto = :id_producto
        ");
        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->execute();
        
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto) {
            echo json_encode([
                'success' => true,
                'producto' => $producto
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Producto no encontrado'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al obtener producto: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener producto: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto no proporcionado'
    ]);
}
?>