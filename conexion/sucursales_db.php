<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

/**
 * Funciones para la gestión de sucursales
 */

function crearSucursal($datos) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO sucursales (nombre, direccion, telefono, correo, horario, activa) VALUES (:nombre, :direccion, :telefono, :correo, :horario, 1)");
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':horario', $datos['horario']);

        if ($stmt->execute()) {
            return ['success' => true, 'id' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Error al crear la sucursal'];
        }
    } catch(PDOException $e) {
        error_log("Error al crear sucursal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function obtenerSucursales() {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE activa = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener sucursales: " . $e->getMessage());
        return [];
    }
}

function obtenerSucursalPorId($id_sucursal) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE id_sucursal = :id_sucursal AND activa = 1");
        $stmt->bindParam(':id_sucursal', $id_sucursal);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener sucursal: " . $e->getMessage());
        return null;
    }
}

function actualizarSucursal($id_sucursal, $datos) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE sucursales SET nombre = :nombre, direccion = :direccion, telefono = :telefono, correo = :correo, horario = :horario WHERE id_sucursal = :id_sucursal");
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':horario', $datos['horario']);
        $stmt->bindParam(':id_sucursal', $id_sucursal);

        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar la sucursal'];
        }
    } catch(PDOException $e) {
        error_log("Error al actualizar sucursal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function eliminarSucursal($id_sucursal) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE sucursales SET activa = 0 WHERE id_sucursal = :id_sucursal");
        $stmt->bindParam(':id_sucursal', $id_sucursal);

        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al desactivar la sucursal'];
        }
    } catch(PDOException $e) {
        error_log("Error al desactivar sucursal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function asignarEmpleadoASucursal($id_empleado, $id_sucursal) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE vendedores SET id_sucursal = :id_sucursal WHERE id_vendedor = :id_vendedor");
        $stmt->bindParam(':id_sucursal', $id_sucursal);
        $stmt->bindParam(':id_vendedor', $id_empleado);

        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al asignar empleado a sucursal'];
        }
    } catch(PDOException $e) {
        error_log("Error al asignar empleado a sucursal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function obtenerEmpleadosPorSucursal($id_sucursal) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT v.* 
            FROM vendedores v 
            WHERE v.id_sucursal = :id_sucursal 
            ORDER BY v.nombre
        ");
        $stmt->bindParam(':id_sucursal', $id_sucursal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener empleados por sucursal: " . $e->getMessage());
        return [];
    }
}

function obtenerInventarioPorSucursal($id_sucursal) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, i.stock_sucursal 
            FROM productos p
            JOIN inventario_sucursal i ON p.id_producto = i.id_producto
            WHERE i.id_sucursal = :id_sucursal
            ORDER BY p.nombre
        ");
        $stmt->bindParam(':id_sucursal', $id_sucursal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener inventario por sucursal: " . $e->getMessage());
        return [];
    }
}

function actualizarStockSucursal($id_producto, $id_sucursal, $cantidad) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inventario_sucursal (id_producto, id_sucursal, stock_sucursal) 
            VALUES (:id_producto, :id_sucursal, :cantidad) 
            ON DUPLICATE KEY UPDATE stock_sucursal = stock_sucursal + :cantidad
        ");
        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->bindParam(':id_sucursal', $id_sucursal);
        $stmt->bindParam(':cantidad', $cantidad);

        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar stock de sucursal: " . $e->getMessage());
        return false;
    }
}

function registrarVentaSucursal($venta_datos) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Insertar la venta
        $stmt_venta = $pdo->prepare("
            INSERT INTO ventas (id_cliente, id_vendedor, total, estado, id_sucursal, nota) 
            VALUES (:id_cliente, :id_vendedor, :total, :estado, :id_sucursal, :nota)
        ");
        $stmt_venta->bindParam(':id_cliente', $venta_datos['id_cliente']);
        $stmt_venta->bindParam(':id_vendedor', $venta_datos['id_vendedor']);
        $stmt_venta->bindParam(':total', $venta_datos['total']);
        $stmt_venta->bindValue(':estado', 'Procesada');
        $stmt_venta->bindParam(':id_sucursal', $venta_datos['id_sucursal']);
        $stmt_venta->bindParam(':nota', $venta_datos['nota']);
        $stmt_venta->execute();

        $id_venta = $pdo->lastInsertId();

        // Insertar los detalles de la venta y actualizar stock
        foreach ($venta_datos['productos'] as $producto) {
            $stmt_detalle = $pdo->prepare("
                INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal) 
                VALUES (:id_venta, :id_producto, :cantidad, :precio_unit, :subtotal)
            ");
            $stmt_detalle->bindParam(':id_venta', $id_venta);
            $stmt_detalle->bindParam(':id_producto', $producto['id_producto']);
            $stmt_detalle->bindParam(':cantidad', $producto['cantidad']);
            $stmt_detalle->bindParam(':precio_unit', $producto['precio_unit']);
            $stmt_detalle->bindParam(':subtotal', $producto['subtotal']);
            $stmt_detalle->execute();

            // Actualizar stock en la sucursal
            actualizarStockSucursal($producto['id_producto'], $venta_datos['id_sucursal'], -$producto['cantidad']);
        }

        $pdo->commit();
        return ['success' => true, 'id_venta' => $id_venta];
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al registrar venta en sucursal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al registrar la venta'];
    }
}
?>