<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT id_cliente, nombre, dni, telefono, direccion, correo, nota
            FROM clientes
            WHERE id_cliente = :id_cliente
        ");
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();
        
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente) {
            echo json_encode([
                'success' => true,
                'cliente' => $cliente
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al obtener cliente: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener cliente: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de cliente no proporcionado'
    ]);
}
?>