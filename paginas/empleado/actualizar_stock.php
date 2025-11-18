<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id_producto']) || !isset($input['nuevo_stock'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $id_producto = $input['id_producto'];
    $nuevo_stock = $input['nuevo_stock'];
    
    if ($nuevo_stock < 0) {
        echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id_producto");
        $stmt->bindParam(':stock', $nuevo_stock);
        $stmt->bindParam(':id_producto', $id_producto);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Stock actualizado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el stock'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al actualizar stock: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el stock: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>