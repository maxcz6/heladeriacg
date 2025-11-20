<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

// Check if user is logged in and has employee role
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['id_venta']) || !isset($input['estado'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $id_venta = $input['id_venta'];
    $nuevo_estado = $input['estado'];

    // Validar estados permitidos
    $estados_permitidos = ['Pendiente', 'Procesada', 'Anulada', 'Finalizada'];
    if (!in_array($nuevo_estado, $estados_permitidos)) {
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE ventas SET estado = :estado WHERE id_venta = :id_venta");
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id_venta', $id_venta);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Estado del pedido actualizado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado del pedido'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al actualizar estado del pedido: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el estado del pedido: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>