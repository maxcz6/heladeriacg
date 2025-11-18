<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('empleado');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    // Obtener el ID del empleado
    $stmt_empleado = $pdo->prepare("SELECT id_vendedor FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt_empleado->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt_empleado->execute();
    $usuario_empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_empleado) {
        echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
        exit;
    }
    
    $id_vendedor = $usuario_empleado['id_vendedor'];
    $productos = $input['productos'];
    $cliente_nombre = $input['cliente_nombre'];
    $cliente_dni = $input['cliente_dni'];
    
    // Validar stock antes de crear la venta
    foreach ($productos as $producto) {
        $stmt_stock = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id_producto");
        $stmt_stock->bindParam(':id_producto', $producto['id']);
        $stmt_stock->execute();
        $stock_actual = $stmt_stock->fetchColumn();
        
        if ($producto['quantity'] > $stock_actual) {
            echo json_encode(['success' => false, 'message' => 'No hay suficiente stock para ' . $producto['name']]);
            exit;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Verificar si el cliente ya existe por DNI
        $id_cliente = null;
        if ($cliente_dni) {
            $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM clientes WHERE dni = :dni");
            $stmt_cliente->bindParam(':dni', $cliente_dni);
            $stmt_cliente->execute();
            $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
            
            if ($cliente) {
                $id_cliente = $cliente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $stmt_insert_cliente = $pdo->prepare("INSERT INTO clientes (nombre, dni) VALUES (:nombre, :dni)");
                $stmt_insert_cliente->bindParam(':nombre', $cliente_nombre);
                $stmt_insert_cliente->bindParam(':dni', $cliente_dni);
                $stmt_insert_cliente->execute();
                $id_cliente = $pdo->lastInsertId();
            }
        } else {
            // Cliente general o temporal
            $stmt_cliente_temp = $pdo->prepare("SELECT id_cliente FROM clientes WHERE nombre = 'Cliente Contado' LIMIT 1");
            $stmt_cliente_temp->execute();
            $cliente_temp = $stmt_cliente_temp->fetch(PDO::FETCH_ASSOC);
            
            if ($cliente_temp) {
                $id_cliente = $cliente_temp['id_cliente'];
            } else {
                // Crear cliente "Cliente Contado"
                $stmt_insert_cliente = $pdo->prepare("INSERT INTO clientes (nombre) VALUES ('Cliente Contado')");
                $stmt_insert_cliente->execute();
                $id_cliente = $pdo->lastInsertId();
            }
        }
        
        // Insertar la venta
        $stmt_venta = $pdo->prepare("
            INSERT INTO ventas (id_cliente, id_vendedor, total, estado) 
            VALUES (:id_cliente, :id_vendedor, :total, 'Procesada')
        ");
        
        $stmt_venta->bindParam(':id_cliente', $id_cliente);
        $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
        $stmt_venta->bindParam(':total', $input['total']);
        $stmt_venta->execute();
        
        $id_venta = $pdo->lastInsertId();
        
        // Insertar los detalles de la venta
        foreach ($productos as $producto) {
            // Obtener el precio actual del producto
            $stmt_precio = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = :id_producto");
            $stmt_precio->bindParam(':id_producto', $producto['id']);
            $stmt_precio->execute();
            $precio_producto = $stmt_precio->fetchColumn();
            
            $stmt_detalle = $pdo->prepare("
                INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal) 
                VALUES (:id_venta, :id_producto, :cantidad, :precio_unit, :subtotal)
            ");
            
            $subtotal = $precio_producto * $producto['quantity'];
            $stmt_detalle->bindParam(':id_venta', $id_venta);
            $stmt_detalle->bindParam(':id_producto', $producto['id']);
            $stmt_detalle->bindParam(':cantidad', $producto['quantity']);
            $stmt_detalle->bindParam(':precio_unit', $precio_producto);
            $stmt_detalle->bindParam(':subtotal', $subtotal);
            $stmt_detalle->execute();
            
            // Actualizar stock del producto
            $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto");
            $stmt_stock->bindParam(':cantidad', $producto['quantity']);
            $stmt_stock->bindParam(':id_producto', $producto['id']);
            $stmt_stock->execute();
        }
        
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'id_venta' => $id_venta,
            'total' => $input['total']
        ]);
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al procesar venta: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar la venta: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>