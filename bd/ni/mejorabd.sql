-- ============================================
-- MEJORAS PARA BASE DE DATOS HELADERIACGBD
-- Triggers, Procedimientos y Funciones
-- ============================================

USE heladeriacgbd;

DELIMITER $$

-- ============================================
-- 1. TRIGGER: Actualizar total de venta automáticamente
-- ============================================
DROP TRIGGER IF EXISTS trg_detalle_ventas_after_insert$$
CREATE TRIGGER trg_detalle_ventas_after_insert
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END$$

DROP TRIGGER IF EXISTS trg_detalle_ventas_after_update$$
CREATE TRIGGER trg_detalle_ventas_after_update
AFTER UPDATE ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END$$

DROP TRIGGER IF EXISTS trg_detalle_ventas_after_delete$$
CREATE TRIGGER trg_detalle_ventas_after_delete
AFTER DELETE ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = OLD.id_venta
    )
    WHERE id_venta = OLD.id_venta;
END$$

-- ============================================
-- 2. TRIGGER: Calcular subtotal automáticamente
-- ============================================
DROP TRIGGER IF EXISTS trg_detalle_ventas_before_insert$$
CREATE TRIGGER trg_detalle_ventas_before_insert
BEFORE INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END$$

DROP TRIGGER IF EXISTS trg_detalle_ventas_before_update$$
CREATE TRIGGER trg_detalle_ventas_before_update
BEFORE UPDATE ON detalle_ventas
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END$$

-- ============================================
-- 3. TRIGGER: Descontar stock al procesar venta
-- ============================================
DROP TRIGGER IF EXISTS trg_venta_procesar_stock$$
CREATE TRIGGER trg_venta_procesar_stock
AFTER UPDATE ON ventas
FOR EACH ROW
BEGIN
    -- Solo descontar si cambia de Pendiente a Procesada
    IF OLD.estado = 'Pendiente' AND NEW.estado = 'Procesada' THEN
        -- Actualizar stock general de productos
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
        SET p.stock = p.stock - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta;
        
        -- Actualizar stock por sucursal si existe
        UPDATE inventario_sucursal inv
        INNER JOIN detalle_ventas dv ON inv.id_producto = dv.id_producto
        SET inv.stock_sucursal = inv.stock_sucursal - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta 
        AND inv.id_sucursal = NEW.id_sucursal;
    END IF;
    
    -- Devolver stock si se anula una venta procesada
    IF OLD.estado = 'Procesada' AND NEW.estado = 'Anulada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
        SET p.stock = p.stock + dv.cantidad
        WHERE dv.id_venta = NEW.id_venta;
        
        UPDATE inventario_sucursal inv
        INNER JOIN detalle_ventas dv ON inv.id_producto = dv.id_producto
        SET inv.stock_sucursal = inv.stock_sucursal + dv.cantidad
        WHERE dv.id_venta = NEW.id_venta 
        AND inv.id_sucursal = NEW.id_sucursal;
    END IF;
END$$

-- ============================================
-- 4. TRIGGER: Auditoría de cambios importantes
-- ============================================
DROP TRIGGER IF EXISTS trg_audit_productos_update$$
CREATE TRIGGER trg_audit_productos_update
AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
    IF OLD.precio != NEW.precio OR OLD.stock != NEW.stock THEN
        INSERT INTO audit_logs (tabla, operacion, referencia_id, detalles)
        VALUES (
            'productos',
            'UPDATE',
            NEW.id_producto,
            JSON_OBJECT(
                'id_producto', NEW.id_producto,
                'precio_anterior', OLD.precio,
                'precio_nuevo', NEW.precio,
                'stock_anterior', OLD.stock,
                'stock_nuevo', NEW.stock
            )
        );
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_audit_ventas_insert$$
CREATE TRIGGER trg_audit_ventas_insert
AFTER INSERT ON ventas
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
    VALUES (
        'ventas',
        'INSERT',
        NEW.id_venta,
        (SELECT username FROM usuarios u 
         INNER JOIN vendedores v ON u.id_vendedor = v.id_vendedor 
         WHERE v.id_vendedor = NEW.id_vendedor LIMIT 1),
        JSON_OBJECT(
            'id_venta', NEW.id_venta,
            'total', NEW.total,
            'estado', NEW.estado,
            'id_sucursal', NEW.id_sucursal
        )
    );
END$$

DROP TRIGGER IF EXISTS trg_audit_ventas_update$$
CREATE TRIGGER trg_audit_ventas_update
AFTER UPDATE ON ventas
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO audit_logs (tabla, operacion, referencia_id, detalles)
        VALUES (
            'ventas',
            'UPDATE',
            NEW.id_venta,
            JSON_OBJECT(
                'id_venta', NEW.id_venta,
                'estado_anterior', OLD.estado,
                'estado_nuevo', NEW.estado
            )
        );
    END IF;
END$$

-- ============================================
-- 5. TRIGGER: Sincronizar stock general con sucursales
-- ============================================
DROP TRIGGER IF EXISTS trg_sync_stock_productos$$
CREATE TRIGGER trg_sync_stock_productos
AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
    -- Solo si cambió el stock general
    IF OLD.stock != NEW.stock THEN
        DECLARE diferencia INT;
        SET diferencia = NEW.stock - OLD.stock;
        
        -- Distribuir proporcionalmente en sucursales activas
        UPDATE inventario_sucursal
        SET stock_sucursal = stock_sucursal + 
            FLOOR(diferencia / (SELECT COUNT(*) FROM inventario_sucursal WHERE id_producto = NEW.id_producto))
        WHERE id_producto = NEW.id_producto;
    END IF;
END$$

-- ============================================
-- 6. TRIGGER: Validar stock antes de venta
-- ============================================
DROP TRIGGER IF EXISTS trg_validar_stock_detalle$$
CREATE TRIGGER trg_validar_stock_detalle
BEFORE INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    DECLARE stock_actual INT;
    DECLARE stock_sucursal INT;
    DECLARE id_suc INT;
    
    -- Obtener stock del producto
    SELECT stock INTO stock_actual 
    FROM productos 
    WHERE id_producto = NEW.id_producto;
    
    -- Obtener sucursal de la venta
    SELECT id_sucursal INTO id_suc 
    FROM ventas 
    WHERE id_venta = NEW.id_venta;
    
    -- Verificar stock en sucursal si existe
    IF id_suc IS NOT NULL THEN
        SELECT stock_sucursal INTO stock_sucursal
        FROM inventario_sucursal
        WHERE id_producto = NEW.id_producto 
        AND id_sucursal = id_suc;
        
        IF stock_sucursal IS NOT NULL AND stock_sucursal < NEW.cantidad THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insuficiente en la sucursal para este producto';
        END IF;
    END IF;
    
    -- Verificar stock general
    IF stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para este producto';
    END IF;
END$$

-- ============================================
-- 7. TRIGGER: Limpiar sesiones expiradas
-- ============================================
DROP TRIGGER IF EXISTS trg_limpiar_sesiones$$
CREATE TRIGGER trg_limpiar_sesiones
BEFORE INSERT ON sesiones
FOR EACH ROW
BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

-- ============================================
-- 8. PROCEDIMIENTO: Registrar venta completa
-- ============================================
DROP PROCEDURE IF EXISTS sp_registrar_venta$$
CREATE PROCEDURE sp_registrar_venta(
    IN p_id_cliente INT,
    IN p_id_vendedor INT,
    IN p_id_sucursal INT,
    IN p_nota TEXT,
    IN p_detalles JSON,
    OUT p_id_venta INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_error BOOLEAN DEFAULT FALSE;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        SET v_error = TRUE;
        ROLLBACK;
        SET p_mensaje = 'Error al registrar la venta';
    END;
    
    START TRANSACTION;
    
    -- Insertar venta
    INSERT INTO ventas (id_cliente, id_vendedor, id_sucursal, nota, estado)
    VALUES (p_id_cliente, p_id_vendedor, p_id_sucursal, p_nota, 'Pendiente');
    
    SET p_id_venta = LAST_INSERT_ID();
    
    -- Insertar detalles desde JSON
    INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit)
    SELECT 
        p_id_venta,
        JSON_UNQUOTE(JSON_EXTRACT(detalle, '$.id_producto')),
        JSON_UNQUOTE(JSON_EXTRACT(detalle, '$.cantidad')),
        JSON_UNQUOTE(JSON_EXTRACT(detalle, '$.precio_unit'))
    FROM JSON_TABLE(
        p_detalles,
        '$[*]' COLUMNS(
            detalle JSON PATH '$'
        )
    ) AS jt;
    
    IF NOT v_error THEN
        COMMIT;
        SET p_mensaje = 'Venta registrada correctamente';
    END IF;
END$$

-- ============================================
-- 9. PROCEDIMIENTO: Transferir stock entre sucursales
-- ============================================
DROP PROCEDURE IF EXISTS sp_transferir_stock$$
CREATE PROCEDURE sp_transferir_stock(
    IN p_id_producto INT,
    IN p_id_sucursal_origen INT,
    IN p_id_sucursal_destino INT,
    IN p_cantidad INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_stock_origen INT;
    DECLARE v_error BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        SET v_error = TRUE;
        ROLLBACK;
        SET p_mensaje = 'Error en la transferencia';
    END;
    
    START TRANSACTION;
    
    -- Verificar stock origen
    SELECT stock_sucursal INTO v_stock_origen
    FROM inventario_sucursal
    WHERE id_producto = p_id_producto 
    AND id_sucursal = p_id_sucursal_origen;
    
    IF v_stock_origen IS NULL OR v_stock_origen < p_cantidad THEN
        SET p_mensaje = 'Stock insuficiente en sucursal origen';
        ROLLBACK;
    ELSE
        -- Descontar de origen
        UPDATE inventario_sucursal
        SET stock_sucursal = stock_sucursal - p_cantidad
        WHERE id_producto = p_id_producto 
        AND id_sucursal = p_id_sucursal_origen;
        
        -- Agregar a destino
        INSERT INTO inventario_sucursal (id_producto, id_sucursal, stock_sucursal)
        VALUES (p_id_producto, p_id_sucursal_destino, p_cantidad)
        ON DUPLICATE KEY UPDATE stock_sucursal = stock_sucursal + p_cantidad;
        
        IF NOT v_error THEN
            COMMIT;
            SET p_mensaje = 'Transferencia realizada correctamente';
        END IF;
    END IF;
END$$

-- ============================================
-- 10. PROCEDIMIENTO: Aplicar promoción a producto
-- ============================================
DROP PROCEDURE IF EXISTS sp_aplicar_promocion$$
CREATE PROCEDURE sp_aplicar_promocion(
    IN p_id_producto INT,
    OUT p_precio_final DECIMAL(10,2)
)
BEGIN
    DECLARE v_precio_original DECIMAL(10,2);
    DECLARE v_descuento DECIMAL(5,2);
    
    -- Obtener precio original
    SELECT precio INTO v_precio_original
    FROM productos
    WHERE id_producto = p_id_producto;
    
    -- Buscar promoción activa
    SELECT descuento INTO v_descuento
    FROM promociones
    WHERE id_producto = p_id_producto
    AND activa = 1
    AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
    ORDER BY descuento DESC
    LIMIT 1;
    
    -- Calcular precio final
    IF v_descuento IS NOT NULL THEN
        SET p_precio_final = v_precio_original * (1 - v_descuento / 100);
    ELSE
        SET p_precio_final = v_precio_original;
    END IF;
END$$

-- ============================================
-- 11. FUNCIÓN: Obtener estado de inventario
-- ============================================
DROP FUNCTION IF EXISTS fn_estado_inventario$$
CREATE FUNCTION fn_estado_inventario(p_id_producto INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE v_stock INT;
    DECLARE v_estado VARCHAR(20);
    
    SELECT stock INTO v_stock
    FROM productos
    WHERE id_producto = p_id_producto;
    
    IF v_stock > 30 THEN
        SET v_estado = 'Disponible';
    ELSEIF v_stock BETWEEN 16 AND 30 THEN
        SET v_estado = 'Medio';
    ELSEIF v_stock BETWEEN 1 AND 15 THEN
        SET v_estado = 'Bajo';
    ELSE
        SET v_estado = 'Agotado';
    END IF;
    
    RETURN v_estado;
END$$

-- ============================================
-- 12. FUNCIÓN: Calcular comisión vendedor
-- ============================================
DROP FUNCTION IF EXISTS fn_comision_vendedor$$
CREATE FUNCTION fn_comision_vendedor(
    p_id_vendedor INT,
    p_mes INT,
    p_anio INT
)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_total_ventas DECIMAL(10,2);
    DECLARE v_comision DECIMAL(10,2);
    
    SELECT IFNULL(SUM(total), 0) INTO v_total_ventas
    FROM ventas
    WHERE id_vendedor = p_id_vendedor
    AND MONTH(fecha) = p_mes
    AND YEAR(fecha) = p_anio
    AND estado = 'Procesada';
    
    -- Comisión del 5%
    SET v_comision = v_total_ventas * 0.05;
    
    RETURN v_comision;
END$$

-- ============================================
-- 13. PROCEDIMIENTO: Reporte de ventas por periodo
-- ============================================
DROP PROCEDURE IF EXISTS sp_reporte_ventas$$
CREATE PROCEDURE sp_reporte_ventas(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_sucursal INT
)
BEGIN
    SELECT 
        DATE(v.fecha) as fecha,
        s.nombre as sucursal,
        COUNT(v.id_venta) as num_ventas,
        SUM(v.total) as total_ventas,
        AVG(v.total) as promedio_venta,
        GROUP_CONCAT(DISTINCT vd.nombre) as vendedores
    FROM ventas v
    LEFT JOIN sucursales s ON v.id_sucursal = s.id_sucursal
    LEFT JOIN vendedores vd ON v.id_vendedor = vd.id_vendedor
    WHERE v.fecha BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_id_sucursal IS NULL OR v.id_sucursal = p_id_sucursal)
    AND v.estado = 'Procesada'
    GROUP BY DATE(v.fecha), s.nombre
    ORDER BY fecha DESC;
END$$

-- ============================================
-- 14. PROCEDIMIENTO: Productos más vendidos
-- ============================================
DROP PROCEDURE IF EXISTS sp_productos_mas_vendidos$$
CREATE PROCEDURE sp_productos_mas_vendidos(
    IN p_limite INT,
    IN p_id_sucursal INT
)
BEGIN
    SELECT 
        p.id_producto,
        p.nombre,
        p.sabor,
        SUM(dv.cantidad) as cantidad_vendida,
        SUM(dv.subtotal) as total_vendido,
        COUNT(DISTINCT dv.id_venta) as num_ventas
    FROM detalle_ventas dv
    INNER JOIN productos p ON dv.id_producto = p.id_producto
    INNER JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE v.estado = 'Procesada'
    AND (p_id_sucursal IS NULL OR v.id_sucursal = p_id_sucursal)
    GROUP BY p.id_producto, p.nombre, p.sabor
    ORDER BY cantidad_vendida DESC
    LIMIT p_limite;
END$$

-- ============================================
-- 15. EVENTO: Limpiar sesiones expiradas diariamente
-- ============================================
SET GLOBAL event_scheduler = ON$$

DROP EVENT IF EXISTS evt_limpiar_sesiones$$
CREATE EVENT evt_limpiar_sesiones
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

-- ============================================
-- 16. EVENTO: Desactivar promociones vencidas
-- ============================================
DROP EVENT IF EXISTS evt_desactivar_promociones$$
CREATE EVENT evt_desactivar_promociones
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE promociones
    SET activa = 0
    WHERE fecha_fin < CURDATE()
    AND activa = 1;
END$$

DELIMITER ;

-- ============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================
CREATE INDEX idx_ventas_estado_fecha ON ventas(estado, fecha);
CREATE INDEX idx_productos_activo ON productos(activo);
CREATE INDEX idx_usuarios_activo ON usuarios(activo);
CREATE INDEX idx_promociones_fechas ON promociones(fecha_inicio, fecha_fin, activa);
CREATE INDEX idx_audit_fecha ON audit_logs(fecha);

-- ============================================
-- MENSAJE FINAL
-- ============================================
SELECT 'Base de datos mejorada exitosamente. Triggers, procedimientos y eventos creados.' AS Resultado;