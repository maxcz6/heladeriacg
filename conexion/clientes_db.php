<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/admin_functions.php');

// Funciones para clientes (manteniendo las existentes)
function obtenerPedidosCliente($id_cliente) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado,
                   GROUP_CONCAT(CONCAT(p.nombre, ' - ', dv.cantidad, ' x S/.', dv.precio_unit) SEPARATOR ', ') as productos,
                   SUM(dv.cantidad) as cantidad_total
            FROM ventas v
            JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE v.id_cliente = :id_cliente
            GROUP BY v.id_venta, v.fecha, v.total, v.estado
            ORDER BY v.fecha DESC
        ");
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener pedidos del cliente: " . $e->getMessage());
        return [];
    }
}

function crearPedido($id_cliente, $productos, $metodo_entrega, $mesa = null, $codigo_cupon = null) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Obtener un vendedor válido (cualquier usuario existente)
        // Usamos una consulta genérica para evitar errores de columnas desconocidas como 'rol'
        $stmt_vendedor = $pdo->prepare("SELECT id_usuario FROM usuarios LIMIT 1");
        $stmt_vendedor->execute();
        $vendedor = $stmt_vendedor->fetch(PDO::FETCH_ASSOC);
        $id_vendedor = $vendedor ? $vendedor['id_usuario'] : 1; // Fallback a 1 si la tabla está vacía

        // Crear nota con información de mesa y cupón
        $nota_parts = [];
        if ($mesa) {
            $nota_parts[] = "Mesa: $mesa";
        }
        if ($codigo_cupon) {
            $nota_parts[] = "Cupón: $codigo_cupon";
        }
        $nota_parts[] = "Pedido web";
        $nota = implode(" - ", $nota_parts);

        // Validar y obtener información del cupón si existe
        $cupon_descuento = 0;
        $cupon_tipo = null;
        $id_cupon = null;
        
        if ($codigo_cupon) {
            $stmt_cupon = $pdo->prepare("
                SELECT id_cupon, valor_descuento as descuento, tipo_descuento, usos_actuales, usos_maximos
                FROM cupones
                WHERE codigo = :codigo 
                AND activo = 1
                AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
                AND (usos_maximos IS NULL OR usos_actuales < usos_maximos)
            ");
            $stmt_cupon->bindParam(':codigo', $codigo_cupon);
            $stmt_cupon->execute();
            $cupon = $stmt_cupon->fetch(PDO::FETCH_ASSOC);
            
            if ($cupon) {
                $id_cupon = $cupon['id_cupon'];
                $cupon_descuento = floatval($cupon['descuento']);
                $cupon_tipo = $cupon['tipo_descuento'];
                
                // Convertir tipo si es necesario
                if ($cupon_tipo === 'monto_fijo') {
                    $cupon_tipo = 'fijo';
                }
            }
        }

        // Insertar la venta (inicialmente con total 0, se calculará después)
        $stmt_venta = $pdo->prepare("
            INSERT INTO ventas (id_cliente, id_vendedor, total, estado, nota, fecha)
            VALUES (:id_cliente, :id_vendedor, :total, 'Pendiente', :nota, NOW())
        ");

        $stmt_venta->bindParam(':id_cliente', $id_cliente);
        $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
        $stmt_venta->bindValue(':total', 0); // Se calculará después
        $stmt_venta->bindParam(':nota', $nota);
        $stmt_venta->execute();

        $id_venta = $pdo->lastInsertId();

        // Insertar los detalles de la venta y calcular total
        $subtotal = 0;
        foreach ($productos as $producto) {
            // Obtener el precio actual del producto y su promoción
            $stmt_precio = $pdo->prepare("
                SELECT p.precio, pr.descuento
                FROM productos p
                LEFT JOIN promociones pr ON p.id_producto = pr.id_producto 
                    AND pr.activa = 1 
                    AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin
                WHERE p.id_producto = :id_producto
            ");
            $stmt_precio->bindParam(':id_producto', $producto['id']);
            $stmt_precio->execute();
            $producto_info = $stmt_precio->fetch(PDO::FETCH_ASSOC);
            
            $precio_base = floatval($producto_info['precio']);
            $descuento_promo = floatval($producto_info['descuento'] ?? 0);
            
            // Aplicar descuento de promoción al precio
            $precio_final = $descuento_promo > 0 ? 
                $precio_base * (1 - $descuento_promo / 100) : 
                $precio_base;

            $stmt_detalle = $pdo->prepare("
                INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal)
                VALUES (:id_venta, :id_producto, :cantidad, :precio_unit, :subtotal)
            ");

            $subtotal_item = $precio_final * $producto['quantity'];
            $stmt_detalle->bindParam(':id_venta', $id_venta);
            $stmt_detalle->bindParam(':id_producto', $producto['id']);
            $stmt_detalle->bindValue(':cantidad', $producto['quantity'], PDO::PARAM_STR);
            $stmt_detalle->bindParam(':precio_unit', $precio_final);
            $stmt_detalle->bindValue(':subtotal', $subtotal_item, PDO::PARAM_STR);
            $stmt_detalle->execute();

            $subtotal += $subtotal_item;

            // Actualizar stock del producto
            $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto");
            $stmt_stock->bindValue(':cantidad', $producto['quantity'], PDO::PARAM_STR);
            $stmt_stock->bindParam(':id_producto', $producto['id']);
            $stmt_stock->execute();
        }

        // Aplicar descuento de cupón
        $descuento_cupon_monto = 0;
        if ($id_cupon) {
            if ($cupon_tipo === 'porcentaje') {
                $descuento_cupon_monto = $subtotal * ($cupon_descuento / 100);
            } else {
                // monto_fijo o fijo
                $descuento_cupon_monto = $cupon_descuento;
            }
            
            // Incrementar usos del cupón
            $stmt_update_cupon = $pdo->prepare("UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE id_cupon = :id_cupon");
            $stmt_update_cupon->bindParam(':id_cupon', $id_cupon);
            $stmt_update_cupon->execute();
        }

        $total = max(0, $subtotal - $descuento_cupon_monto);

        // Actualizar el total de la venta
        $stmt_update_total = $pdo->prepare("UPDATE ventas SET total = :total WHERE id_venta = :id_venta");
        $stmt_update_total->bindValue(':total', $total, PDO::PARAM_STR);
        $stmt_update_total->bindParam(':id_venta', $id_venta);
        $stmt_update_total->execute();

        $pdo->commit();
        return ['success' => true, 'id_venta' => $id_venta, 'total' => $total];
    } catch(PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        $error_message = $e->getMessage();
        error_log("Error al crear pedido: " . $error_message);
        
        // Devolver mensaje de error más específico
        if (strpos($error_message, 'usos_actuales') !== false) {
            return ['success' => false, 'message' => 'Error al actualizar el cupón. Por favor, intenta nuevamente.'];
        } elseif (strpos($error_message, 'stock') !== false) {
            return ['success' => false, 'message' => 'No hay suficiente stock disponible.'];
        } else {
            return ['success' => false, 'message' => 'Error al crear el pedido: ' . $error_message];
        }
    }
}

function obtenerEstadoPedido($id_venta) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado,
                   GROUP_CONCAT(CONCAT(p.nombre, ' - ', dv.cantidad, ' x S/.', dv.precio_unit) SEPARATOR ', ') as productos
            FROM ventas v
            JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE v.id_venta = :id_venta
            GROUP BY v.id_venta, v.fecha, v.total, v.estado
        ");
        $stmt->bindParam(':id_venta', $id_venta);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener estado del pedido: " . $e->getMessage());
        return null;
    }
}

function obtenerProductos() {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT id_producto, nombre, precio, stock, descripcion
            FROM productos
            WHERE stock > 0 AND activo = 1
            ORDER BY nombre
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener productos: " . $e->getMessage());
        return [];
    }
}
?>