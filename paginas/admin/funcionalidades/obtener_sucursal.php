<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_sucursal = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("
            SELECT id_sucursal, nombre, direccion, telefono, correo, horario, activa
            FROM sucursales
            WHERE id_sucursal = :id
        ");
        $stmt->bindParam(':id', $id_sucursal, PDO::PARAM_INT);
        $stmt->execute();
        
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sucursal) {
            echo json_encode([
                'success' => true,
                'sucursal' => $sucursal
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sucursal no encontrada'
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
        'message' => 'ID de sucursal no especificado'
    ]);
}
?>
