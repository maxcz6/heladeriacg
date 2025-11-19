<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_vendedor = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT id_vendedor, nombre, dni, telefono, correo, turno
            FROM vendedores
            WHERE id_vendedor = :id_vendedor
        ");
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->execute();
        
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($empleado) {
            echo json_encode([
                'success' => true,
                'empleado' => $empleado
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al obtener empleado: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener empleado: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado no proporcionado'
    ]);
}
?>