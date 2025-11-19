-- ============================================
-- BASE DE DATOS: heladeriacgbd
-- Estructura completa sin datos de prueba
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- CREAR BASE DE DATOS
-- ============================================
CREATE DATABASE IF NOT EXISTS `heladeriacgbd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `heladeriacgbd`;

-- ============================================
-- TABLAS PRINCIPALES
-- ============================================

-- Tabla: roles
CREATE TABLE `roles` (
  `id_role` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id_role`, `nombre`) VALUES
(1, 'admin'),
(2, 'empleado'),
(3, 'cliente');

-- Tabla: sucursales
CREATE TABLE `sucursales` (
  `id_sucursal` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `horario` VARCHAR(100) DEFAULT NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: clientes
CREATE TABLE `clientes` (
  `id_cliente` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `dni` CHAR(12) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `fecha_registro` DATE DEFAULT (CURDATE()),
  `nota` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: vendedores
CREATE TABLE `vendedores` (
  `id_vendedor` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `dni` CHAR(12) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `turno` ENUM('Mañana','Tarde','Noche') DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_sucursal` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_vendedor`),
  UNIQUE KEY `dni` (`dni`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `vendedores_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` INT(11) DEFAULT NULL,
  `id_vendedor` INT(11) DEFAULT NULL,
  `username` VARCHAR(80) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `id_role` TINYINT(3) UNSIGNED NOT NULL DEFAULT 3,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_sucursal` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  KEY `id_role` (`id_role`),
  KEY `id_sucursal` (`id_sucursal`),
  KEY `idx_usuarios_activo` (`activo`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_3` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Usuario administrador por defecto (password: admin123)
INSERT INTO `usuarios` (`username`, `password`, `id_role`) VALUES
('admin', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 1);

-- Tabla: proveedores
CREATE TABLE `proveedores` (
  `id_proveedor` INT(11) NOT NULL AUTO_INCREMENT,
  `empresa` VARCHAR(100) NOT NULL,
  `contacto` VARCHAR(80) NOT NULL,
  `telefono` VARCHAR(30) NOT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: productos
CREATE TABLE `productos` (
  `id_producto` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `sabor` VARCHAR(60) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `precio` DECIMAL(10,2) NOT NULL CHECK (`precio` >= 0),
  `stock` INT(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
  `id_proveedor` INT(11) DEFAULT NULL,
  `fecha_registro` DATE DEFAULT (CURDATE()),
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_producto`),
  KEY `idx_sabor` (`sabor`),
  KEY `idx_stock` (`stock`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `idx_productos_activo` (`activo`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: inventario_sucursal
CREATE TABLE `inventario_sucursal` (
  `id_producto` INT(11) NOT NULL,
  `id_sucursal` INT(11) NOT NULL,
  `stock_sucursal` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_producto`, `id_sucursal`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `inventario_sucursal_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventario_sucursal_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: ventas
CREATE TABLE `ventas` (
  `id_venta` INT(11) NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_cliente` INT(11) DEFAULT NULL,
  `id_vendedor` INT(11) DEFAULT NULL,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('Pendiente','Procesada','Anulada') NOT NULL DEFAULT 'Procesada',
  `nota` TEXT DEFAULT NULL,
  `id_sucursal` INT(11) DEFAULT 1,
  PRIMARY KEY (`id_venta`),
  KEY `idx_fecha` (`fecha`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  KEY `id_sucursal` (`id_sucursal`),
  KEY `idx_ventas_estado_fecha` (`estado`, `fecha`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ventas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: detalle_ventas
CREATE TABLE `detalle_ventas` (
  `id_detalle` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` INT(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unit` DECIMAL(10,2) NOT NULL CHECK (`precio_unit` >= 0),
  `subtotal` DECIMAL(10,2) NOT NULL CHECK (`subtotal` >= 0),
  PRIMARY KEY (`id_detalle`),
  KEY `idx_id_venta` (`id_venta`),
  KEY `idx_id_producto` (`id_producto`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: promociones (referenciada en procedimientos)
CREATE TABLE `promociones` (
  `id_promocion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NOT NULL,
  `descuento` DECIMAL(5,2) NOT NULL CHECK (`descuento` BETWEEN 0 AND 100),
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_promocion`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `promociones_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: sesiones
CREATE TABLE `sesiones` (
  `id_session` CHAR(40) NOT NULL,
  `id_usuario` INT(11) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `creado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiracion` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_session`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: audit_logs
CREATE TABLE `audit_logs` (
  `id_log` INT(11) NOT NULL AUTO_INCREMENT,
  `tabla` VARCHAR(100) NOT NULL,
  `operacion` ENUM('INSERT','UPDATE','DELETE') NOT NULL,
  `referencia_id` INT(11) DEFAULT NULL,
  `usuario` VARCHAR(80) DEFAULT NULL,
  `detalles` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`detalles`)),
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_audit_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER $$

-- Trigger: Calcular subtotal antes de insertar detalle_ventas
CREATE TRIGGER `trg_detalle_ventas_before_insert` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END$$

-- Trigger: Calcular subtotal antes de actualizar detalle_ventas
CREATE TRIGGER `trg_detalle_ventas_before_update` BEFORE UPDATE ON `detalle_ventas` FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END$$

-- Trigger: Validar stock antes de insertar detalle
CREATE TRIGGER `trg_validar_stock_detalle` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    DECLARE stock_actual INT;
    DECLARE stock_sucursal INT;
    DECLARE id_suc INT;
    
    SELECT stock INTO stock_actual 
    FROM productos 
    WHERE id_producto = NEW.id_producto;
    
    SELECT id_sucursal INTO id_suc 
    FROM ventas 
    WHERE id_venta = NEW.id_venta;
    
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
    
    IF stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para este producto';
    END IF;
END$$

-- Trigger: Actualizar total de venta después de insertar detalle
CREATE TRIGGER `trg_detalle_ventas_after_insert` AFTER INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END$$

-- Trigger: Actualizar total de venta después de actualizar detalle
CREATE TRIGGER `trg_detalle_ventas_after_update` AFTER UPDATE ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END$$

-- Trigger: Actualizar total de venta después de eliminar detalle
CREATE TRIGGER `trg_detalle_ventas_after_delete` AFTER DELETE ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = OLD.id_venta
    )
    WHERE id_venta = OLD.id_venta;
END$$

-- Trigger: Procesar stock al cambiar estado de venta
CREATE TRIGGER `trg_venta_procesar_stock` AFTER UPDATE ON `ventas` FOR EACH ROW
BEGIN
    IF OLD.estado = 'Pendiente' AND NEW.estado = 'Procesada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
        SET p.stock = p.stock - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta;
        
        UPDATE inventario_sucursal inv
        INNER JOIN detalle_ventas dv ON inv.id_producto = dv.id_producto
        SET inv.stock_sucursal = inv.stock_sucursal - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta 
        AND inv.id_sucursal = NEW.id_sucursal;
    END IF;
    
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

-- Trigger: Auditar cambios en productos
CREATE TRIGGER `trg_audit_productos_update` AFTER UPDATE ON `productos` FOR EACH ROW
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

-- Trigger: Auditar inserción de ventas
CREATE TRIGGER `trg_audit_ventas_insert` AFTER INSERT ON `ventas` FOR EACH ROW
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

-- Trigger: Auditar actualización de ventas
CREATE TRIGGER `trg_audit_ventas_update` AFTER UPDATE ON `ventas` FOR EACH ROW
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

-- Trigger: Limpiar sesiones expiradas antes de insertar
CREATE TRIGGER `trg_limpiar_sesiones` BEFORE INSERT ON `sesiones` FOR EACH ROW
BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

DELIMITER ;

-- ============================================
-- FUNCIONES
-- ============================================

DELIMITER $$

-- Función: Calcular estado del inventario
CREATE FUNCTION `fn_estado_inventario`(`p_id_producto` INT) 
RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
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

-- Función: Calcular comisión de vendedor
CREATE FUNCTION `fn_comision_vendedor`(`p_id_vendedor` INT, `p_mes` INT, `p_anio` INT) 
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
    
    SET v_comision = v_total_ventas * 0.05;
    
    RETURN v_comision;
END$$

DELIMITER ;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================

DELIMITER $$

-- Procedimiento: Aplicar promoción
CREATE PROCEDURE `sp_aplicar_promocion`(
    IN `p_id_producto` INT, 
    OUT `p_precio_final` DECIMAL(10,2)
)
BEGIN
    DECLARE v_precio_original DECIMAL(10,2);
    DECLARE v_descuento DECIMAL(5,2);
    
    SELECT precio INTO v_precio_original
    FROM productos
    WHERE id_producto = p_id_producto;
    
    SELECT descuento INTO v_descuento
    FROM promociones
    WHERE id_producto = p_id_producto
    AND activa = 1
    AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
    ORDER BY descuento DESC
    LIMIT 1;
    
    IF v_descuento IS NOT NULL THEN
        SET p_precio_final = v_precio_original * (1 - v_descuento / 100);
    ELSE
        SET p_precio_final = v_precio_original;
    END IF;
END$$

-- Procedimiento: Productos más vendidos
CREATE PROCEDURE `sp_productos_mas_vendidos`(
    IN `p_limite` INT, 
    IN `p_id_sucursal` INT
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

-- Procedimiento: Reporte de ventas
CREATE PROCEDURE `sp_reporte_ventas`(
    IN `p_fecha_inicio` DATE, 
    IN `p_fecha_fin` DATE, 
    IN `p_id_sucursal` INT
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

-- Procedimiento: Transferir stock entre sucursales
CREATE PROCEDURE `sp_transferir_stock`(
    IN `p_id_producto` INT, 
    IN `p_id_sucursal_origen` INT, 
    IN `p_id_sucursal_destino` INT, 
    IN `p_cantidad` INT, 
    OUT `p_mensaje` VARCHAR(255)
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
    
    SELECT stock_sucursal INTO v_stock_origen
    FROM inventario_sucursal
    WHERE id_producto = p_id_producto 
    AND id_sucursal = p_id_sucursal_origen;
    
    IF v_stock_origen IS NULL OR v_stock_origen < p_cantidad THEN
        SET p_mensaje = 'Stock insuficiente en sucursal origen';
        ROLLBACK;
    ELSE
        UPDATE inventario_sucursal
        SET stock_sucursal = stock_sucursal - p_cantidad
        WHERE id_producto = p_id_producto 
        AND id_sucursal = p_id_sucursal_origen;
        
        INSERT INTO inventario_sucursal (id_producto, id_sucursal, stock_sucursal)
        VALUES (p_id_producto, p_id_sucursal_destino, p_cantidad)
        ON DUPLICATE KEY UPDATE stock_sucursal = stock_sucursal + p_cantidad;
        
        IF NOT v_error THEN
            COMMIT;
            SET p_mensaje = 'Transferencia realizada correctamente';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- VISTAS
-- ============================================

-- Vista: Productos con estado de stock
CREATE VIEW `vw_productos_stock` AS
SELECT 
    `productos`.`id_producto` AS `id_producto`,
    `productos`.`nombre` AS `nombre`,
    `productos`.`sabor` AS `sabor`,
    `productos`.`precio` AS `precio`,
    `productos`.`stock` AS `stock`,
    CASE 
        WHEN `productos`.`stock` > 30 THEN 'Disponible'
        WHEN `productos`.`stock` BETWEEN 16 AND 30 THEN 'Medio'
        ELSE 'Bajo'
    END AS `estado_stock`
FROM `productos`;

-- Vista: Resumen de ventas
CREATE VIEW `vw_ventas_resumen` AS
SELECT 
    `v`.`id_venta` AS `id_venta`,
    `v`.`fecha` AS `fecha`,
    `v`.`id_cliente` AS `id_cliente`,
    `c`.`nombre` AS `cliente_nombre`,
    `v`.`id_vendedor` AS `id_vendedor`,
    `vd`.`nombre` AS `vendedor_nombre`,
    `v`.`total` AS `total`,
    `v`.`estado` AS `estado`
FROM ((`ventas` `v` 
LEFT JOIN `clientes` `c` ON(`v`.`id_cliente` = `c`.`id_cliente`)) 
LEFT JOIN `vendedores` `vd` ON(`v`.`id_vendedor` = `vd`.`id_vendedor`));

-- ============================================
-- EVENTOS PROGRAMADOS
-- ============================================

DELIMITER $$

-- Evento: Limpiar sesiones expiradas diariamente
CREATE EVENT `evt_limpiar_sesiones`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
ENABLE
DO BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

-- Evento: Desactivar promociones vencidas cada hora
CREATE EVENT `evt_desactivar_promociones`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
ENABLE
DO BEGIN
    UPDATE promociones
    SET activa = 0
    WHERE fecha_fin < CURDATE()
    AND activa = 1;
END$$

DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================