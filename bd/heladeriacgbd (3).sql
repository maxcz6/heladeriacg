-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2025 a las 02:55:14
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `heladeriacgbd`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_aplicar_cupon_venta` (IN `p_id_venta` INT, IN `p_codigo_cupon` VARCHAR(50), OUT `p_resultado` VARCHAR(255), OUT `p_descuento` DECIMAL(10,2))   BEGIN
    DECLARE v_id_cupon INT;
    DECLARE v_id_cliente INT;
    DECLARE v_id_sucursal INT;
    DECLARE v_total_original DECIMAL(10,2);
    DECLARE v_validacion VARCHAR(255);
    DECLARE v_error BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        SET v_error = TRUE;
        ROLLBACK;
        SET p_resultado = 'ERROR: Error en la transacción';
    END;
    
    START TRANSACTION;
    
    -- Obtener datos de la venta
    SELECT id_cliente, id_sucursal, total
    INTO v_id_cliente, v_id_sucursal, v_total_original
    FROM ventas
    WHERE id_venta = p_id_venta;
    
    IF v_total_original IS NULL THEN
        SET p_resultado = 'ERROR: Venta no encontrada';
        ROLLBACK;
    ELSE
        -- Validar cupón
        SET v_validacion = fn_validar_cupon(p_codigo_cupon, v_id_cliente, v_total_original);
        
        IF v_validacion != 'VALIDO' THEN
            SET p_resultado = v_validacion;
            ROLLBACK;
        ELSE
            -- Obtener ID del cupón
            SELECT id_cupon INTO v_id_cupon
            FROM cupones
            WHERE codigo = p_codigo_cupon;
            
            -- Calcular descuento
            SET p_descuento = fn_calcular_descuento_cupon(p_codigo_cupon, v_total_original);
            
            -- Actualizar venta
            UPDATE ventas
            SET id_cupon = v_id_cupon,
                descuento_cupon = p_descuento,
                total = v_total_original - p_descuento
            WHERE id_venta = p_id_venta;
            
            -- Registrar uso
            INSERT INTO cupones_uso (
                id_cupon, id_venta, id_cliente, monto_descuento,
                monto_original, monto_final, id_sucursal
            ) VALUES (
                v_id_cupon, p_id_venta, v_id_cliente, p_descuento,
                v_total_original, v_total_original - p_descuento, v_id_sucursal
            );
            
            IF NOT v_error THEN
                COMMIT;
                SET p_resultado = 'OK: Cupón aplicado correctamente';
            END IF;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_aplicar_promocion` (IN `p_id_producto` INT, OUT `p_precio_final` DECIMAL(10,2))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_cupon` (IN `p_prefijo` VARCHAR(10), IN `p_descripcion` VARCHAR(255), IN `p_tipo_descuento` VARCHAR(20), IN `p_valor_descuento` DECIMAL(10,2), IN `p_monto_minimo` DECIMAL(10,2), IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_usos_maximos` INT, IN `p_usos_por_cliente` INT, IN `p_id_usuario` INT, OUT `p_codigo` VARCHAR(50), OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_error BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        SET v_error = TRUE;
        ROLLBACK;
        SET p_mensaje = 'ERROR: No se pudo crear el cupón';
    END;
    
    START TRANSACTION;
    
    -- Generar código único
    SET p_codigo = fn_generar_codigo_cupon(p_prefijo, 6);
    
    -- Insertar cupón
    INSERT INTO cupones (
        codigo, descripcion, tipo_descuento, valor_descuento,
        monto_minimo, fecha_inicio, fecha_fin, usos_maximos,
        usos_por_cliente, creado_por
    ) VALUES (
        p_codigo, p_descripcion, p_tipo_descuento, p_valor_descuento,
        p_monto_minimo, p_fecha_inicio, p_fecha_fin, p_usos_maximos,
        p_usos_por_cliente, p_id_usuario
    );
    
    IF NOT v_error THEN
        COMMIT;
        SET p_mensaje = CONCAT('OK: Cupón creado con código ', p_codigo);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_productos_mas_vendidos` (IN `p_limite` INT, IN `p_id_sucursal` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reporte_cupones` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_id_sucursal` INT)   BEGIN
    SELECT 
        c.codigo,
        c.descripcion,
        c.tipo_descuento,
        c.valor_descuento,
        c.usos_actuales,
        c.usos_maximos,
        COUNT(cu.id_uso) as total_usos,
        SUM(cu.monto_descuento) as total_descuentos,
        SUM(cu.monto_original) as ventas_brutas,
        SUM(cu.monto_final) as ventas_netas,
        AVG(cu.monto_descuento) as descuento_promedio,
        MIN(cu.fecha_uso) as primer_uso,
        MAX(cu.fecha_uso) as ultimo_uso
    FROM cupones c
    LEFT JOIN cupones_uso cu ON c.id_cupon = cu.id_cupon
        AND cu.fecha_uso BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_id_sucursal IS NULL OR cu.id_sucursal = p_id_sucursal)
    WHERE c.fecha_creacion BETWEEN p_fecha_inicio AND p_fecha_fin
    GROUP BY c.id_cupon, c.codigo, c.descripcion, c.tipo_descuento, 
             c.valor_descuento, c.usos_actuales, c.usos_maximos
    ORDER BY total_descuentos DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reporte_ventas` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_id_sucursal` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_transferir_stock` (IN `p_id_producto` INT, IN `p_id_sucursal_origen` INT, IN `p_id_sucursal_destino` INT, IN `p_cantidad` INT, OUT `p_mensaje` VARCHAR(255))   BEGIN
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

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_calcular_descuento_cupon` (`p_codigo` VARCHAR(50), `p_monto_compra` DECIMAL(10,2)) RETURNS DECIMAL(10,2) READS SQL DATA BEGIN
    DECLARE v_tipo_descuento VARCHAR(20);
    DECLARE v_valor_descuento DECIMAL(10,2);
    DECLARE v_descuento DECIMAL(10,2);
    
    SELECT tipo_descuento, valor_descuento
    INTO v_tipo_descuento, v_valor_descuento
    FROM cupones
    WHERE codigo = p_codigo AND activo = 1;
    
    IF v_tipo_descuento IS NULL THEN
        RETURN 0.00;
    END IF;
    
    IF v_tipo_descuento = 'porcentaje' THEN
        SET v_descuento = p_monto_compra * (v_valor_descuento / 100);
    ELSE
        SET v_descuento = v_valor_descuento;
    END IF;
    
    -- No puede ser mayor al monto de compra
    IF v_descuento > p_monto_compra THEN
        SET v_descuento = p_monto_compra;
    END IF;
    
    RETURN v_descuento;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_comision_vendedor` (`p_id_vendedor` INT, `p_mes` INT, `p_anio` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
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

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_estado_inventario` (`p_id_producto` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
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

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_generar_codigo_cupon` (`p_prefijo` VARCHAR(10), `p_longitud` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE v_codigo VARCHAR(50);
    DECLARE v_caracteres VARCHAR(36) DEFAULT 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    DECLARE v_i INT DEFAULT 0;
    DECLARE v_existe INT DEFAULT 1;
    
    WHILE v_existe > 0 DO
        SET v_codigo = UPPER(p_prefijo);
        SET v_i = 0;
        
        WHILE v_i < p_longitud DO
            SET v_codigo = CONCAT(v_codigo, 
                SUBSTRING(v_caracteres, FLOOR(1 + RAND() * 34), 1)
            );
            SET v_i = v_i + 1;
        END WHILE;
        
        SELECT COUNT(*) INTO v_existe
        FROM cupones
        WHERE codigo = v_codigo;
    END WHILE;
    
    RETURN v_codigo;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_validar_cupon` (`p_codigo` VARCHAR(50), `p_id_cliente` INT, `p_monto_compra` DECIMAL(10,2)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_id_cupon INT;
    DECLARE v_activo TINYINT;
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    DECLARE v_monto_minimo DECIMAL(10,2);
    DECLARE v_usos_maximos INT;
    DECLARE v_usos_actuales INT;
    DECLARE v_usos_por_cliente INT;
    DECLARE v_usos_cliente INT;
    
    -- Obtener datos del cupón
    SELECT id_cupon, activo, fecha_inicio, fecha_fin, monto_minimo,
           usos_maximos, usos_actuales, usos_por_cliente
    INTO v_id_cupon, v_activo, v_fecha_inicio, v_fecha_fin, v_monto_minimo,
         v_usos_maximos, v_usos_actuales, v_usos_por_cliente
    FROM cupones
    WHERE codigo = p_codigo;
    
    -- Validaciones
    IF v_id_cupon IS NULL THEN
        RETURN 'ERROR: Cupón no existe';
    END IF;
    
    IF v_activo = 0 THEN
        RETURN 'ERROR: Cupón inactivo';
    END IF;
    
    IF CURDATE() < v_fecha_inicio THEN
        RETURN 'ERROR: Cupón aún no válido';
    END IF;
    
    IF CURDATE() > v_fecha_fin THEN
        RETURN 'ERROR: Cupón vencido';
    END IF;
    
    IF p_monto_compra < v_monto_minimo THEN
        RETURN CONCAT('ERROR: Compra mínima S/ ', v_monto_minimo);
    END IF;
    
    IF v_usos_maximos IS NOT NULL AND v_usos_actuales >= v_usos_maximos THEN
        RETURN 'ERROR: Cupón agotado';
    END IF;
    
    -- Verificar usos por cliente
    IF p_id_cliente IS NOT NULL THEN
        SELECT COUNT(*) INTO v_usos_cliente
        FROM cupones_uso
        WHERE id_cupon = v_id_cupon AND id_cliente = p_id_cliente;
        
        IF v_usos_cliente >= v_usos_por_cliente THEN
            RETURN 'ERROR: Ya usaste este cupón';
        END IF;
    END IF;
    
    RETURN 'VALIDO';
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id_log` int(11) NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `operacion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `usuario` varchar(80) DEFAULT NULL,
  `detalles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles`)),
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id_log`, `tabla`, `operacion`, `referencia_id`, `usuario`, `detalles`, `fecha`) VALUES
(1, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 3.75, \"precio_nuevo\": 3.75, \"stock_anterior\": 0, \"stock_nuevo\": 5}', '2025-11-19 16:39:51'),
(2, 'productos', 'UPDATE', 1, NULL, '{\"id_producto\": 1, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 0, \"stock_nuevo\": 5}', '2025-11-19 16:41:06'),
(3, 'productos', 'UPDATE', 8, NULL, '{\"id_producto\": 8, \"precio_anterior\": 0.00, \"precio_nuevo\": 0.00, \"stock_anterior\": 0, \"stock_nuevo\": 5}', '2025-11-19 16:49:31'),
(4, 'productos', 'UPDATE', 13, NULL, '{\"id_producto\": 13, \"precio_anterior\": 55.00, \"precio_nuevo\": 55.00, \"stock_anterior\": 6, \"stock_nuevo\": 5}', '2025-11-19 16:49:47'),
(5, 'productos', 'UPDATE', 8, NULL, '{\"id_producto\": 8, \"precio_anterior\": 0.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 5, \"stock_nuevo\": 5}', '2025-11-19 17:05:07'),
(6, 'productos', 'UPDATE', 8, NULL, '{\"id_producto\": 8, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 5, \"stock_nuevo\": 6000}', '2025-11-19 17:05:23'),
(7, 'cupones', 'INSERT', 1, NULL, '{\"codigo\": \"NOELFRNGY8\", \"tipo_descuento\": \"monto_fijo\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-19\", \"fecha_fin\": \"2025-12-19\"}', '2025-11-19 20:05:34'),
(8, 'cupones', 'INSERT', 2, NULL, '{\"codigo\": \"NOELZLYZT7\", \"tipo_descuento\": \"monto_fijo\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-19\", \"fecha_fin\": \"2025-12-19\"}', '2025-11-19 20:05:34'),
(9, 'cupones', 'INSERT', 3, NULL, '{\"codigo\": \"NOELUBDQV5\", \"tipo_descuento\": \"porcentaje\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-19\", \"fecha_fin\": \"2025-12-19\"}', '2025-11-19 20:05:46'),
(10, 'cupones', 'INSERT', 4, NULL, '{\"codigo\": \"NOELULQ4AX\", \"tipo_descuento\": \"porcentaje\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-19\", \"fecha_fin\": \"2025-12-19\"}', '2025-11-19 20:07:37'),
(11, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 0, \"stock_nuevo\": 5}', '2025-11-19 23:08:37'),
(12, 'productos', 'UPDATE', 1, NULL, '{\"id_producto\": 1, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 5, \"stock_nuevo\": 8}', '2025-11-19 23:31:20'),
(13, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 5, \"stock_nuevo\": 8}', '2025-11-19 23:31:20'),
(14, 'productos', 'UPDATE', 22, NULL, '{\"id_producto\": 22, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 555, \"stock_nuevo\": 8}', '2025-11-19 23:31:20'),
(15, 'cupones', 'INSERT', 5, NULL, '{\"codigo\": \"VERANOZZD78B\", \"tipo_descuento\": \"monto_fijo\", \"valor_descuento\": 55.00, \"fecha_inicio\": \"2025-11-20\", \"fecha_fin\": \"2025-12-20\"}', '2025-11-20 01:01:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `dni` char(12) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `nota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `dni`, `telefono`, `direccion`, `correo`, `fecha_registro`, `nota`) VALUES
(2, 'María Torres', '65432198', '912345678', 'Jr. Miraflores 432', 'maria@hotmail.com', '2025-11-17', ''),
(4, 'max', NULL, '912333444', 'Pj Campos55', 'maxjpr7@gmail.com', '2025-11-19', NULL),
(5, 'pepa', NULL, '888888888', 'Pj Camposfff', 'peparrr@g', '2025-11-19', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unit` decimal(10,2) NOT NULL CHECK (`precio_unit` >= 0),
  `subtotal` decimal(10,2) NOT NULL CHECK (`subtotal` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `precio_unit`, `subtotal`) VALUES
(1, 1, 1, 2, 3.50, 7.00),
(2, 2, 2, 1, 3.75, 3.75),
(3, 2, 1, 1, 3.50, 3.50),
(4, 2, 3, 1, 3.50, 3.50);

--
-- Disparadores `detalle_ventas`
--
DELIMITER $$
CREATE TRIGGER `trg_detalle_ventas_after_delete` AFTER DELETE ON `detalle_ventas` FOR EACH ROW BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = OLD.id_venta
    )
    WHERE id_venta = OLD.id_venta;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ventas_after_insert` AFTER INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ventas_after_update` AFTER UPDATE ON `detalle_ventas` FOR EACH ROW BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ventas_before_insert` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ventas_before_update` BEFORE UPDATE ON `detalle_ventas` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_stock_detalle` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `sabor` varchar(60) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
  `id_proveedor` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `sabor`, `descripcion`, `precio`, `stock`, `id_proveedor`, `fecha_registro`, `activo`) VALUES
(1, 'Helado de Fresa', 'Fresa', 'Helado artesanal de fresa', 3.50, 8, 2, '2025-11-17', 0),
(2, 'Helado de Chocolate', 'Chocolate', 'Chocolate oscuro premium', 3.75, 5, 2, '2025-11-17', 1),
(3, 'Helado de Vainilla', 'Vainilla', 'Vainilla naturall', 3.50, 8, 2, '2025-11-17', 1),
(8, 'cremaa', 'choco', 'riko', 5.00, 6000, 2, '2025-11-19', 1),
(20, 'vainilla look', 'vainilla', 'helado crema', 5.00, 5, 5, '2025-11-19', 1),
(21, 'vainilla look', 'vainilla', 'helado crema', 5.00, 5, 5, '2025-11-19', 1),
(22, 'max', 'fresa', 'mmm', 5.00, 8, 5, '2025-11-19', 1);

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_productos_update` AFTER UPDATE ON `productos` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `contacto` varchar(80) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `empresa`, `contacto`, `telefono`, `correo`, `direccion`, `fecha_registro`) VALUES
(2, 'acao Andino S.A.C.', 'José Medin', '987654326', 'ventas@cacaoandino.co', 'Jr. Comercio 1225', '2025-11-17 22:45:38'),
(4, 'pepa Andino S.A.C.', 'pepe', '987654321', 'maxjpr7@gmail.com', 'Pj Campos9999', '2025-11-19 17:18:39'),
(5, 'leche Andino S.A.C.', 'rober', '987654388', 'rober@correo.com', 'Pj Ca', '2025-11-19 21:06:55'),
(6, 'xyz', 'Medina', '555555555', 'm@gmail.com', 'Pj jj', '2025-11-20 00:10:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_role` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_role`, `nombre`) VALUES
(1, 'admin'),
(3, 'cliente'),
(2, 'empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id_session` char(40) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiracion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `sesiones`
--
DELIMITER $$
CREATE TRIGGER `trg_limpiar_sesiones` BEFORE INSERT ON `sesiones` FOR EACH ROW BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_sucursal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_cliente`, `id_vendedor`, `username`, `password`, `id_role`, `activo`, `fecha_registro`, `id_sucursal`) VALUES
(1, NULL, NULL, 'admin', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 1, 1, '2025-11-17 22:45:38', NULL),
(3, NULL, NULL, 'cliente', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, '2025-11-17 22:45:38', NULL),
(4, NULL, NULL, 'cliente1', '$2y$10$lY3r3Jbbn3/WLY5NqshiOev8LW54MiRKvLsOXKlzVX2KHIN/A5jVy', 3, 1, '2025-11-17 23:09:19', NULL),
(5, NULL, NULL, 'empleado1', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 2, 1, '2025-11-17 23:10:57', NULL),
(6, NULL, NULL, 'max', '$2y$10$CiIMqUMI2Hn2nKRXjY9TPegkyAXwUaN/O1D6vvvtTWSIaet8jo/Ey', 1, 1, '2025-11-19 17:21:54', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

CREATE TABLE `vendedores` (
  `id_vendedor` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `dni` char(12) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `turno` enum('Mañana','Tarde','Noche') DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_sucursal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`id_vendedor`, `nombre`, `dni`, `telefono`, `correo`, `turno`, `fecha_registro`, `id_sucursal`) VALUES
(2, 'Lucía Ramos', '87654322', '912333444', 'lucia@correo.com', 'Mañana', '2025-11-17 22:45:38', 1),
(3, 'Juan Pérez', NULL, '999888777', 'juan@correo.com', 'Mañana', '2025-11-19 17:16:02', 4),
(4, 'max', NULL, '987654321', 'maxjpr7@gmail.com', 'Mañana', '2025-11-19 17:17:33', 4),
(6, 'Lucía Ramostt', NULL, '555555555', 'luciattt@correo.com', 'Tarde', '2025-11-19 20:51:22', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Procesada','Anulada') NOT NULL DEFAULT 'Procesada',
  `nota` text DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT 1,
  `id_cupon` int(11) DEFAULT NULL,
  `descuento_cupon` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `fecha`, `id_cliente`, `id_vendedor`, `total`, `estado`, `nota`, `id_sucursal`, `id_cupon`, `descuento_cupon`) VALUES
(1, '2025-11-17 17:45:38', NULL, NULL, 0.00, 'Procesada', NULL, 1, NULL, 0.00),
(2, '2025-11-17 18:11:17', NULL, NULL, 10.75, '', NULL, 1, NULL, 0.00);

--
-- Disparadores `ventas`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_ventas_insert` AFTER INSERT ON `ventas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_audit_ventas_update` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_venta_procesar_stock` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_productos_stock`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_productos_stock` (
`id_producto` int(11)
,`nombre` varchar(120)
,`sabor` varchar(60)
,`precio` decimal(10,2)
,`stock` int(11)
,`estado_stock` varchar(10)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_ventas_resumen`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_ventas_resumen` (
`id_venta` int(11)
,`fecha` datetime
,`id_cliente` int(11)
,`cliente_nombre` varchar(120)
,`id_vendedor` int(11)
,`vendedor_nombre` varchar(120)
,`total` decimal(10,2)
,`estado` enum('Pendiente','Procesada','Anulada')
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_productos_stock`
--
DROP TABLE IF EXISTS `vw_productos_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_productos_stock`  AS SELECT `productos`.`id_producto` AS `id_producto`, `productos`.`nombre` AS `nombre`, `productos`.`sabor` AS `sabor`, `productos`.`precio` AS `precio`, `productos`.`stock` AS `stock`, CASE WHEN `productos`.`stock` > 30 THEN 'Disponible' WHEN `productos`.`stock` between 16 and 30 THEN 'Medio' ELSE 'Bajo' END AS `estado_stock` FROM `productos` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_ventas_resumen`
--
DROP TABLE IF EXISTS `vw_ventas_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_ventas_resumen`  AS SELECT `v`.`id_venta` AS `id_venta`, `v`.`fecha` AS `fecha`, `v`.`id_cliente` AS `id_cliente`, `c`.`nombre` AS `cliente_nombre`, `v`.`id_vendedor` AS `id_vendedor`, `vd`.`nombre` AS `vendedor_nombre`, `v`.`total` AS `total`, `v`.`estado` AS `estado` FROM ((`ventas` `v` left join `clientes` `c` on(`v`.`id_cliente` = `c`.`id_cliente`)) left join `vendedores` `vd` on(`v`.`id_vendedor` = `vd`.`id_vendedor`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_audit_fecha` (`fecha`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_id_venta` (`id_venta`),
  ADD KEY `idx_id_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_sabor` (`sabor`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `idx_productos_activo` (`activo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id_session`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_vendedor` (`id_vendedor`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `idx_usuarios_activo` (`activo`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id_vendedor`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_vendedor` (`id_vendedor`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `idx_ventas_estado_fecha` (`estado`,`fecha`),
  ADD KEY `idx_id_cupon` (`id_cupon`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id_vendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_3` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD CONSTRAINT `vendedores_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_cupon` FOREIGN KEY (`id_cupon`) REFERENCES `cupones` (`id_cupon`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `evt_limpiar_sesiones` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-18 20:49:30' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_desactivar_promociones` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-18 20:49:30' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE promociones
    SET activa = 0
    WHERE fecha_fin < CURDATE()
    AND activa = 1;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_desactivar_cupones_vencidos` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-19 14:44:32' ON COMPLETION PRESERVE ENABLE DO BEGIN
    UPDATE cupones
    SET activo = 0
    WHERE fecha_fin < CURDATE()
    AND activo = 1;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
