<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json');

try {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de cupón inválido'
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            id_cupon,
            codigo,
            descripcion,
            tipo_descuento,
            valor_descuento,
            monto_minimo,
            fecha_inicio,
            fecha_fin,
            usos_maximos,
            usos_actuales,
            usos_por_cliente,
            activo
        FROM cupones
        WHERE id_cupon = :id
    ");
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $cupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cupon) {
        echo json_encode([
            'success' => true,
            'cupon' => $cupon
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Cupón no encontrado'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener cupón: ' . $e->getMessage()
    ]);
}
?>
