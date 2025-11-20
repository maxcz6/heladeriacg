<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud']);
        exit;
    }
    
    $codigo = strtoupper(trim($input['codigo'] ?? ''));

    if (empty($codigo)) {
        echo json_encode(['success' => false, 'message' => 'Código de cupón inválido']);
        exit;
    }

    try {
        // Buscar cupón activo
        $stmt = $pdo->prepare("
            SELECT id_cupon, codigo, valor_descuento as descuento, tipo_descuento, 
                   fecha_inicio, fecha_fin, usos_maximos, usos_actuales, 
                   usos_por_cliente, descripcion, monto_minimo
            FROM cupones
            WHERE codigo = :codigo 
            AND activo = 1
            AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
            AND (usos_maximos IS NULL OR usos_actuales < usos_maximos)
        ");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $cupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cupon) {
            // Convertir tipo_descuento al formato esperado
            $tipo_descuento = $cupon['tipo_descuento'];
            if ($tipo_descuento === 'monto_fijo') {
                $tipo_descuento = 'fijo';
            } elseif ($tipo_descuento === 'porcentaje') {
                $tipo_descuento = 'porcentaje';
            }
            
            echo json_encode([
                'success' => true,
                'cupon' => [
                    'id_cupon' => $cupon['id_cupon'],
                    'codigo' => $cupon['codigo'],
                    'descuento' => floatval($cupon['descuento']),
                    'tipo_descuento' => $tipo_descuento,
                    'descripcion' => $cupon['descripcion'],
                    'monto_minimo' => floatval($cupon['monto_minimo'] ?? 0)
                ]
            ]);
        } else {
            // Verificar por qué el cupón no es válido
            $stmt_check = $pdo->prepare("SELECT * FROM cupones WHERE codigo = :codigo");
            $stmt_check->bindParam(':codigo', $codigo);
            $stmt_check->execute();
            $cupon_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if (!$cupon_check) {
                $message = 'El cupón ingresado no existe';
            } elseif ($cupon_check['activo'] != 1) {
                $message = 'El cupón ha sido desactivado';
            } elseif (date('Y-m-d') < $cupon_check['fecha_inicio']) {
                $message = 'El cupón aún no está disponible (válido desde ' . date('d/m/Y', strtotime($cupon_check['fecha_inicio'])) . ')';
            } elseif (date('Y-m-d') > $cupon_check['fecha_fin']) {
                $message = 'El cupón expiró el ' . date('d/m/Y', strtotime($cupon_check['fecha_fin']));
            } elseif ($cupon_check['usos_maximos'] && $cupon_check['usos_actuales'] >= $cupon_check['usos_maximos']) {
                $message = 'El cupón ha alcanzado su límite de usos (' . $cupon_check['usos_maximos'] . ' usos)';
            } else {
                $message = 'El cupón no es válido en este momento';
            }
            
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        }
    } catch(PDOException $e) {
        error_log("Error al verificar cupón: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al verificar el cupón. Por favor, intenta nuevamente.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
