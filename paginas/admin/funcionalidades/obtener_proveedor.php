<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_proveedor = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id_proveedor = :id");
        $stmt->bindParam(':id', $id_proveedor, PDO::PARAM_INT);
        $stmt->execute();
        
        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($proveedor) {
            echo json_encode([
                'success' => true,
                'proveedor' => $proveedor
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Proveedor no encontrado'
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
        'message' => 'ID de proveedor no especificado'
    ]);
}
