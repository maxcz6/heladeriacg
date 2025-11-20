<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json');

try {
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
        ORDER BY fecha_fin DESC, id_cupon DESC
    ");
    
    $stmt->execute();
    $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cupones' => $cupones
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener cupones: ' . $e->getMessage()
    ]);
}
?>
