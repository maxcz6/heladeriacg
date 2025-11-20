<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

// Check if user is logged in and has employee role
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para esta acción']);
    exit;
}

// Get product ID from POST request
$id_producto = $_POST['id_producto'] ?? null;

if (!$id_producto || !is_numeric($id_producto)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit;
}

try {
    // Get current stock for the product
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id_producto");
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stock' => (int)$producto['stock']
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos'
    ]);
    error_log("Error obteniendo stock: " . $e->getMessage());
}
?>