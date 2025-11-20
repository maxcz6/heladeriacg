<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Check if user is logged in and has employee role
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para esta acción']);
    exit;
}

// Get coupon code from request
$codigo = $_POST['codigo'] ?? null;

if (!$codigo) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Código de cupón no proporcionado']);
    exit;
}

try {
    // Buscar el cupón en la base de datos
    $stmt = $pdo->prepare("
        SELECT c.*, d.*
        FROM cupones c
        JOIN descuentos d ON c.id_descuento = d.id_descuento
        WHERE c.codigo = :codigo 
        AND c.activo = 1
        AND (c.fecha_vencimiento IS NULL OR c.fecha_vencimiento >= NOW())
        AND (c.veces_usado < c.uso_maximo OR c.uso_maximo IS NULL)
    ");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();
    $cupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupon) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Código de cupón no válido o ha expirado']);
        exit;
    }

    // Return coupon information
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'descuento' => [
            'id_cupon' => $cupon['id_cupon'],
            'codigo' => $cupon['codigo'],
            'id_descuento' => $cupon['id_descuento'],
            'nombre' => $cupon['nombre'],
            'tipo' => $cupon['tipo'],
            'valor' => $cupon['valor'],
            'descripcion' => $cupon['descripcion']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Error al validar cupón: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al validar cupón']);
    exit;
}
?>