<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sucursales_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sucursal = obtenerSucursalPorId($id);
    
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
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de sucursal no proporcionado'
    ]);
}
?>