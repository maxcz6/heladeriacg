<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Test: Insertar un cupón de prueba
$codigo = 'TEST' . rand(100, 999);
$descripcion = 'Cupón de Prueba';
$tipo_descuento = 'porcentaje';
$valor_descuento = 10.00;
$monto_minimo = 0;
$fecha_inicio = date('Y-m-d');
$fecha_fin = date('Y-m-d', strtotime('+30 days'));
$usos_maximos = 100;
$usos_por_cliente = 1;
$creado_por = 1;

try {
    $sql = "INSERT INTO cupones (
        codigo, descripcion, tipo_descuento, valor_descuento,
        monto_minimo, fecha_inicio, fecha_fin, usos_maximos,
        usos_por_cliente, creado_por, activo, usos_actuales
    ) VALUES (
        :codigo, :descripcion, :tipo_descuento, :valor_descuento,
        :monto_minimo, :fecha_inicio, :fecha_fin, :usos_maximos,
        :usos_por_cliente, :creado_por, 1, 0
    )";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':tipo_descuento', $tipo_descuento);
    $stmt->bindParam(':valor_descuento', $valor_descuento);
    $stmt->bindParam(':monto_minimo', $monto_minimo);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':usos_maximos', $usos_maximos, PDO::PARAM_INT);
    $stmt->bindParam(':usos_por_cliente', $usos_por_cliente, PDO::PARAM_INT);
    $stmt->bindParam(':creado_por', $creado_por, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cupón de prueba creado exitosamente',
            'codigo' => $codigo,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al insertar cupón'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>
