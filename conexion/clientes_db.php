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

function crearPedido($id_cliente, $productos, $metodo_entrega) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Para este ejemplo, usaremos un vendedor por defecto
        $id_vendedor = 1; // Juan Pérez

        // Insertar la venta (inicialmente con total 0, se calculará después)
        $stmt_venta = $pdo->prepare("
            INSERT INTO ventas (id_cliente, id_vendedor, total, estado)
            VALUES (:id_cliente, :id_vendedor, :total, 'Pendiente')
        ");

        $stmt_venta->bindParam(':id_cliente', $id_cliente);
        $stmt_venta->bindParam(':id_vendedor', $id_vendedor);
        $stmt_venta->bindValue(':total', 0); // Se calculará después
        $stmt_venta->execute();

        $id_venta = $pdo->lastInsertId();

        // Insertar los detalles de la venta y calcular total
        $total = 0;
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

            $total += $subtotal;

            // Actualizar stock del producto
            $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto");
            $stmt_stock->bindParam(':cantidad', $producto['quantity']);
            $stmt_stock->bindParam(':id_producto', $producto['id']);
            $stmt_stock->execute();
        }

        // Actualizar el total de la venta
        $stmt_update_total = $pdo->prepare("UPDATE ventas SET total = :total WHERE id_venta = :id_venta");
        $stmt_update_total->bindParam(':total', $total);
        $stmt_update_total->bindParam(':id_venta', $id_venta);
        $stmt_update_total->execute();

        $pdo->commit();
        return ['success' => true, 'id_venta' => $id_venta, 'total' => $total];
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al crear pedido: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al crear el pedido'];
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