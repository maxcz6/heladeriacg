<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_promocion = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id_promocion, p.id_producto, pr.nombre AS producto_nombre, 
                   p.descuento, p.fecha_inicio, p.fecha_fin, p.activa, p.descripcion
            FROM promociones p
            LEFT JOIN productos pr ON p.id_producto = pr.id_producto
            WHERE p.id_promocion = :id
        ");
        $stmt->bindParam(':id', $id_promocion, PDO::PARAM_INT);
        $stmt->execute();
        
        $promocion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($promocion) {
            echo json_encode([
                'success' => true,
                'promocion' => $promocion
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Promoción no encontrada'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de promoción no especificado'
    ]);
}
?>
