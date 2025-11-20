-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2025 a las 21:44:42
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
(1, 'ventas', 'INSERT', 1, 'rosa.vendedor', '{\"id_venta\": 1, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 1}', '2025-11-20 14:15:50'),
(2, 'ventas', 'INSERT', 2, 'rosa.vendedor', '{\"id_venta\": 2, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 1}', '2025-11-20 14:15:50'),
(3, 'ventas', 'INSERT', 3, NULL, '{\"id_venta\": 3, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 2}', '2025-11-20 14:15:50'),
(4, 'ventas', 'INSERT', 4, 'patricia.vendedor', '{\"id_venta\": 4, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 3}', '2025-11-20 14:15:50'),
(5, 'ventas', 'INSERT', 5, NULL, '{\"id_venta\": 5, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 2}', '2025-11-20 14:15:50'),
(6, 'cupones', 'INSERT', 1, NULL, '{\"codigo\": \"NOELD65LTE\", \"tipo_descuento\": \"monto_fijo\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-20\", \"fecha_fin\": \"2025-12-20\"}', '2025-11-20 14:20:25'),
(7, 'productos', 'UPDATE', 1, NULL, '{\"id_producto\": 1, \"precio_anterior\": 4.50, \"precio_nuevo\": 4.50, \"stock_anterior\": 50, \"stock_nuevo\": 5}', '2025-11-20 14:31:37'),
(8, 'productos', 'UPDATE', 5, NULL, '{\"id_producto\": 5, \"precio_anterior\": 4.25, \"precio_nuevo\": 4.25, \"stock_anterior\": 40, \"stock_nuevo\": 4}', '2025-11-20 14:31:41'),
(9, 'ventas', 'INSERT', 6, 'rosa vendedor', '{\"id_venta\": 6, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:35:21'),
(10, 'productos', 'UPDATE', 1, NULL, '{\"id_producto\": 1, \"precio_anterior\": 4.50, \"precio_nuevo\": 4.50, \"stock_anterior\": 5, \"stock_nuevo\": 4}', '2025-11-20 15:35:21'),
(11, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 70, \"stock_nuevo\": 69}', '2025-11-20 15:35:21'),
(12, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 45, \"stock_nuevo\": 44}', '2025-11-20 15:35:21'),
(13, 'productos', 'UPDATE', 5, NULL, '{\"id_producto\": 5, \"precio_anterior\": 4.25, \"precio_nuevo\": 4.25, \"stock_anterior\": 4, \"stock_nuevo\": 3}', '2025-11-20 15:35:21'),
(14, 'ventas', 'UPDATE', 6, NULL, '{\"id_venta\": 6, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 15:37:03'),
(15, 'ventas', 'INSERT', 7, 'rosa vendedor', '{\"id_venta\": 7, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:39:03'),
(16, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 69, \"stock_nuevo\": 52}', '2025-11-20 15:39:03'),
(17, 'productos', 'UPDATE', 1, NULL, '{\"id_producto\": 1, \"precio_anterior\": 4.50, \"precio_nuevo\": 4.50, \"stock_anterior\": 4, \"stock_nuevo\": 0}', '2025-11-20 15:39:03'),
(18, 'productos', 'UPDATE', 5, NULL, '{\"id_producto\": 5, \"precio_anterior\": 4.25, \"precio_nuevo\": 4.25, \"stock_anterior\": 3, \"stock_nuevo\": 0}', '2025-11-20 15:39:03'),
(19, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 44, \"stock_nuevo\": 36}', '2025-11-20 15:39:03'),
(20, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 4.00, \"precio_nuevo\": 4.00, \"stock_anterior\": 60, \"stock_nuevo\": 56}', '2025-11-20 15:39:03'),
(21, 'ventas', 'UPDATE', 7, NULL, '{\"id_venta\": 7, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 15:40:55'),
(23, 'ventas', 'INSERT', 10, NULL, '{\"id_venta\": 10, \"total\": 7.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:57:00'),
(24, 'ventas', 'INSERT', 11, 'rosa vendedor', '{\"id_venta\": 11, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:57:15'),
(25, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 52, \"stock_nuevo\": 44}', '2025-11-20 15:57:15'),
(26, 'ventas', 'INSERT', 12, 'rosa vendedor', '{\"id_venta\": 12, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:57:18'),
(27, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 44, \"stock_nuevo\": 36}', '2025-11-20 15:57:18'),
(28, 'ventas', 'INSERT', 13, 'rosa vendedor', '{\"id_venta\": 13, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:57:21'),
(29, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 36, \"stock_nuevo\": 28}', '2025-11-20 15:57:21'),
(30, 'ventas', 'INSERT', 14, 'rosa vendedor', '{\"id_venta\": 14, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:57:24'),
(31, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 28, \"stock_nuevo\": 20}', '2025-11-20 15:57:24'),
(32, 'ventas', 'INSERT', 15, NULL, '{\"id_venta\": 15, \"total\": 10.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:58:44'),
(33, 'ventas', 'INSERT', 16, NULL, '{\"id_venta\": 16, \"total\": 4.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:58:53'),
(34, 'ventas', 'INSERT', 17, NULL, '{\"id_venta\": 17, \"total\": 4.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 15:59:12'),
(35, 'ventas', 'UPDATE', 17, NULL, '{\"id_venta\": 17, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:10:56'),
(36, 'ventas', 'UPDATE', 16, NULL, '{\"id_venta\": 16, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:11:04'),
(37, 'ventas', 'UPDATE', 15, NULL, '{\"id_venta\": 15, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:11:07'),
(38, 'ventas', 'UPDATE', 14, NULL, '{\"id_venta\": 14, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:19:00'),
(39, 'ventas', 'INSERT', 18, NULL, '{\"id_venta\": 18, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:23:17'),
(40, 'ventas', 'INSERT', 19, 'rosa vendedor', '{\"id_venta\": 19, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:23:33'),
(41, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 36.00, \"stock_nuevo\": 35.00}', '2025-11-20 16:23:33'),
(42, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 20.00, \"stock_nuevo\": 19.00}', '2025-11-20 16:23:33'),
(43, 'ventas', 'INSERT', 20, NULL, '{\"id_venta\": 20, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:23:43'),
(44, 'ventas', 'INSERT', 21, 'rosa vendedor', '{\"id_venta\": 21, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:24:08'),
(45, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 35.00, \"stock_nuevo\": 34.00}', '2025-11-20 16:24:08'),
(46, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 19.00, \"stock_nuevo\": 18.00}', '2025-11-20 16:24:08'),
(47, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 4.00, \"precio_nuevo\": 4.00, \"stock_anterior\": 56.00, \"stock_nuevo\": 55.00}', '2025-11-20 16:24:08'),
(48, 'ventas', 'UPDATE', 20, NULL, '{\"id_venta\": 20, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:26:49'),
(49, 'ventas', 'INSERT', 22, NULL, '{\"id_venta\": 22, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:37:30'),
(50, 'ventas', 'INSERT', 23, 'rosa vendedor', '{\"id_venta\": 23, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:37:48'),
(51, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 34.00, \"stock_nuevo\": 33.50}', '2025-11-20 16:37:48'),
(52, 'ventas', 'INSERT', 28, 'rosa vendedor', '{\"id_venta\": 28, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:44:34'),
(53, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 33.50, \"stock_nuevo\": 29.50}', '2025-11-20 16:44:34'),
(54, 'ventas', 'INSERT', 29, 'rosa vendedor', '{\"id_venta\": 29, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:45:36'),
(55, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 18.00, \"stock_nuevo\": 16.00}', '2025-11-20 16:45:36'),
(56, 'ventas', 'INSERT', 30, NULL, '{\"id_venta\": 30, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:47:28'),
(57, 'ventas', 'INSERT', 31, 'rosa vendedor', '{\"id_venta\": 31, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:52:42'),
(58, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 16.00, \"stock_nuevo\": 13.00}', '2025-11-20 16:52:42'),
(59, 'ventas', 'INSERT', 32, NULL, '{\"id_venta\": 32, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:53:00'),
(60, 'ventas', 'UPDATE', 32, NULL, '{\"id_venta\": 32, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 16:54:39'),
(61, 'ventas', 'INSERT', 34, 'rosa vendedor', '{\"id_venta\": 34, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 16:59:00'),
(62, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 13.00, \"stock_nuevo\": 11.50}', '2025-11-20 16:59:00'),
(63, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 29.50, \"stock_nuevo\": 28.60}', '2025-11-20 16:59:00'),
(64, 'ventas', 'INSERT', 35, NULL, '{\"id_venta\": 35, \"total\": 4.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 17:35:35'),
(65, 'ventas', 'INSERT', 36, 'rosa vendedor', '{\"id_venta\": 36, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 17:36:07'),
(66, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 11.50, \"stock_nuevo\": 8.50}', '2025-11-20 17:36:07'),
(67, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 4.00, \"precio_nuevo\": 4.00, \"stock_anterior\": 55.00, \"stock_nuevo\": 54.00}', '2025-11-20 17:36:07'),
(68, 'ventas', 'INSERT', 37, NULL, '{\"id_venta\": 37, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 18:57:08'),
(69, 'ventas', 'INSERT', 38, NULL, '{\"id_venta\": 38, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:00:16'),
(70, 'ventas', 'INSERT', 39, NULL, '{\"id_venta\": 39, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:00:18'),
(71, 'ventas', 'INSERT', 40, NULL, '{\"id_venta\": 40, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:02:39'),
(72, 'ventas', 'INSERT', 41, 'rosa vendedor', '{\"id_venta\": 41, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:04:35'),
(73, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 28.60, \"stock_nuevo\": 27.60}', '2025-11-20 19:04:35'),
(74, 'ventas', 'UPDATE', 41, NULL, '{\"id_venta\": 41, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:25'),
(75, 'ventas', 'UPDATE', 40, NULL, '{\"id_venta\": 40, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:27'),
(76, 'ventas', 'UPDATE', 39, NULL, '{\"id_venta\": 39, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:29'),
(77, 'ventas', 'UPDATE', 38, NULL, '{\"id_venta\": 38, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:30'),
(78, 'ventas', 'UPDATE', 35, NULL, '{\"id_venta\": 35, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:32'),
(79, 'ventas', 'UPDATE', 36, NULL, '{\"id_venta\": 36, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:34'),
(80, 'ventas', 'UPDATE', 34, NULL, '{\"id_venta\": 34, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:36'),
(81, 'ventas', 'UPDATE', 31, NULL, '{\"id_venta\": 31, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 19:06:38'),
(82, 'ventas', 'INSERT', 42, 'rosa vendedor', '{\"id_venta\": 42, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:34:37'),
(83, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 27.60, \"stock_nuevo\": 26.60}', '2025-11-20 19:34:37'),
(84, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 4.00, \"precio_nuevo\": 4.00, \"stock_anterior\": 54.00, \"stock_nuevo\": 51.00}', '2025-11-20 19:34:37'),
(85, 'ventas', 'INSERT', 43, 'rosa vendedor', '{\"id_venta\": 43, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:38:08'),
(86, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 8.50, \"stock_nuevo\": 7.50}', '2025-11-20 19:38:08'),
(87, 'ventas', 'INSERT', 44, 'rosa vendedor', '{\"id_venta\": 44, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:38:24'),
(88, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 26.60, \"stock_nuevo\": 25.60}', '2025-11-20 19:38:24'),
(89, 'ventas', 'INSERT', 45, 'rosa vendedor', '{\"id_venta\": 45, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:40:37'),
(90, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 25.60, \"stock_nuevo\": 24.50}', '2025-11-20 19:40:37'),
(91, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 7.50, \"stock_nuevo\": 6.50}', '2025-11-20 19:40:37'),
(92, 'ventas', 'INSERT', 46, 'rosa vendedor', '{\"id_venta\": 46, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 19:41:12'),
(93, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 6.50, \"stock_nuevo\": 5.50}', '2025-11-20 19:41:12'),
(94, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 24.50, \"stock_nuevo\": 23.50}', '2025-11-20 19:41:12'),
(95, 'ventas', 'INSERT', 47, NULL, '{\"id_venta\": 47, \"total\": 4.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 20:03:35'),
(108, 'ventas', 'UPDATE', 47, NULL, '{\"id_venta\": 47, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 20:09:52'),
(109, 'ventas', 'UPDATE', 46, NULL, '{\"id_venta\": 46, \"estado_anterior\": \"Pendiente\", \"estado_nuevo\": \"\"}', '2025-11-20 20:09:54'),
(110, 'ventas', 'INSERT', 52, NULL, '{\"id_venta\": 52, \"total\": 5.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 20:11:39'),
(111, 'ventas', 'INSERT', 53, 'rosa vendedor', '{\"id_venta\": 53, \"total\": 0.00, \"estado\": \"Pendiente\", \"id_sucursal\": 1}', '2025-11-20 20:35:12'),
(112, 'productos', 'UPDATE', 4, NULL, '{\"id_producto\": 4, \"precio_anterior\": 3.50, \"precio_nuevo\": 3.50, \"stock_anterior\": 5.50, \"stock_nuevo\": 4.50}', '2025-11-20 20:35:12'),
(113, 'productos', 'UPDATE', 2, NULL, '{\"id_producto\": 2, \"precio_anterior\": 5.00, \"precio_nuevo\": 5.00, \"stock_anterior\": 23.50, \"stock_nuevo\": 22.50}', '2025-11-20 20:35:12'),
(114, 'productos', 'UPDATE', 3, NULL, '{\"id_producto\": 3, \"precio_anterior\": 4.00, \"precio_nuevo\": 4.00, \"stock_anterior\": 51.00, \"stock_nuevo\": 50.00}', '2025-11-20 20:35:12');

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
(1, 'Juan Pérez Gómez', '12345678', '987654321', 'Jr. Junín 123', 'juan.perez@gmail.com', '2025-11-20', 'Cliente VIP - descuento 10%'),
(2, 'María Rodríguez', '23456789', '912345678', 'Av. Ferrocarril 456', 'maria.r@hotmail.com', '2025-11-20', 'Le gusta el helado de chocolate'),
(3, 'Carlos Mendoza', '34567890', '923456789', 'Calle Real 789', 'cmendoza@yahoo.com', '2025-11-20', 'Alérgico a nueces'),
(4, 'Ana Torres Silva', '45678901', '934567890', 'Jr. Parra 234', 'ana.torres@outlook.com', '2025-11-20', 'Prefiere sabores frutales'),
(5, 'Luis Campos', '56789012', '945678901', 'Av. Giraldez 567', 'luiscampos@gmail.com', '2025-11-20', 'Cliente frecuente - programa de puntos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `precio_unit` decimal(10,2) NOT NULL CHECK (`precio_unit` >= 0),
  `subtotal` decimal(10,2) NOT NULL CHECK (`subtotal` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `precio_unit`, `subtotal`) VALUES
(1, 1, 1, 2.00, 4.50, 9.00),
(2, 1, 3, 1.00, 4.00, 4.00),
(3, 2, 2, 3.00, 5.00, 15.00),
(4, 3, 4, 2.00, 3.50, 7.00),
(5, 3, 5, 1.00, 4.25, 4.25),
(6, 4, 1, 1.00, 4.50, 4.50),
(7, 4, 2, 1.00, 5.00, 5.00),
(8, 5, 3, 4.00, 4.00, 16.00),
(9, 6, 1, 1.00, 4.50, 4.50),
(10, 6, 4, 1.00, 3.50, 3.50),
(11, 6, 2, 1.00, 5.00, 5.00),
(12, 6, 5, 1.00, 4.25, 4.25),
(13, 7, 4, 17.00, 3.50, 59.50),
(14, 7, 1, 4.00, 4.50, 18.00),
(15, 7, 5, 3.00, 4.25, 12.75),
(16, 7, 2, 8.00, 5.00, 40.00),
(17, 7, 3, 4.00, 4.00, 16.00),
(18, 10, 4, 2.00, 3.50, 7.00),
(19, 11, 4, 8.00, 3.50, 28.00),
(20, 12, 4, 8.00, 3.50, 28.00),
(21, 13, 4, 8.00, 3.50, 28.00),
(22, 14, 4, 8.00, 3.50, 28.00),
(23, 15, 2, 2.00, 5.00, 10.00),
(24, 16, 3, 1.00, 4.00, 4.00),
(25, 17, 3, 1.00, 4.00, 4.00),
(26, 18, 2, 1.00, 5.00, 5.00),
(27, 19, 2, 1.00, 5.00, 5.00),
(28, 19, 4, 1.00, 3.50, 3.50),
(29, 20, 2, 1.00, 5.00, 5.00),
(30, 21, 2, 1.00, 5.00, 5.00),
(31, 21, 4, 1.00, 3.50, 3.50),
(32, 21, 3, 1.00, 4.00, 4.00),
(33, 22, 2, 1.00, 5.00, 5.00),
(34, 23, 2, 0.50, 5.00, 2.50),
(35, 28, 2, 4.00, 5.00, 20.00),
(36, 29, 4, 2.00, 3.50, 7.00),
(37, 30, 2, 1.00, 5.00, 5.00),
(38, 31, 4, 3.00, 3.50, 10.50),
(39, 32, 2, 1.00, 5.00, 5.00),
(40, 34, 4, 1.50, 3.50, 5.25),
(41, 34, 2, 0.90, 5.00, 4.50),
(42, 35, 3, 1.00, 4.00, 4.00),
(43, 36, 4, 3.00, 2.63, 7.89),
(44, 36, 3, 1.00, 3.60, 3.60),
(45, 37, 2, 1.00, 5.00, 5.00),
(46, 38, 2, 1.00, 5.00, 5.00),
(47, 39, 2, 1.00, 5.00, 5.00),
(48, 40, 2, 1.00, 5.00, 5.00),
(49, 41, 2, 1.00, 4.00, 4.00),
(50, 42, 2, 1.00, 4.00, 4.00),
(51, 42, 3, 3.00, 3.60, 10.80),
(52, 43, 4, 1.00, 2.63, 2.63),
(53, 44, 2, 1.00, 4.00, 4.00),
(54, 45, 2, 1.10, 4.00, 4.40),
(55, 45, 4, 1.00, 2.63, 2.63),
(56, 46, 4, 1.00, 2.63, 2.63),
(57, 46, 2, 1.00, 4.00, 4.00),
(58, 47, 3, 1.00, 4.00, 4.00),
(67, 52, 2, 1.00, 5.00, 5.00),
(68, 53, 4, 1.00, 2.63, 2.63),
(69, 53, 2, 1.00, 4.00, 4.00),
(70, 53, 3, 1.00, 3.60, 3.60);

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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_sucursal`
--

CREATE TABLE `inventario_sucursal` (
  `id_producto` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `stock_sucursal` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_sucursal`
--

INSERT INTO `inventario_sucursal` (`id_producto`, `id_sucursal`, `stock_sucursal`) VALUES
(1, 1, 20),
(1, 2, 15),
(2, 1, 18),
(3, 2, 25),
(4, 3, 30);

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
  `stock` decimal(10,2) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `sabor`, `descripcion`, `precio`, `stock`, `id_proveedor`, `fecha_registro`, `activo`) VALUES
(1, 'Helado Artesanal', 'Lúcuma', 'Helado cremoso de lúcuma peruana', 4.50, 0.00, 1, '2025-11-20', 1),
(2, 'Helado Premium', 'Chocolate Belga', 'Chocolate importado de alta calidad', 5.00, 22.50, 3, '2025-11-20', 1),
(3, 'Helado Tropical', 'Mango', 'Mango fresco de la selva', 4.00, 50.00, 2, '2025-11-20', 1),
(4, 'Helado Clásico', 'Fresa', 'Fresas naturales seleccionadas', 3.50, 4.50, 2, '2025-11-20', 1),
(5, 'Helado Especial', 'Maracuyá', 'Fruta de la pasión peruana', 4.25, 0.00, 2, '2025-11-20', 1);

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
(1, 'Lácteos del Valle', 'Ana García', '987654321', 'ana@lacteosv.com', 'Av. Los Andes 456', '2025-11-20 14:15:50'),
(2, 'Frutas Tropicales SAC', 'Luis Mendoza', '912345678', 'luis@frutastrop.com', 'Jr. Amazonas 789', '2025-11-20 14:15:50'),
(3, 'Chocolates Premium', 'Carmen Silva', '923456789', 'carmen@chocpremium.com', 'Calle Real 234', '2025-11-20 14:15:50'),
(4, 'Distribuidora Frost', 'Pedro Rojas', '934567890', 'pedro@frost.com', 'Av. Industrial 567', '2025-11-20 14:15:50'),
(5, 'Sabores Naturales', 'María López', '945678901', 'maria@sabores.com', 'Jr. Comercio 890', '2025-11-20 14:15:50');

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
-- Estructura de tabla para la tabla `sucursales`
--

CREATE TABLE `sucursales` (
  `id_sucursal` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sucursales`
--

INSERT INTO `sucursales` (`id_sucursal`, `nombre`, `direccion`, `telefono`, `correo`, `horario`, `activa`) VALUES
(1, 'Sucursal Plaza Mayor', 'Plaza de Armas 101', '064-231000', 'plazamayor@heladeria.com', '9:00 AM - 10:00 PM', 1),
(2, 'Sucursal Real Plaza', 'Real Plaza 2do piso', '064-231001', 'realplaza@heladeria.com', '10:00 AM - 10:00 PM', 1),
(3, 'Sucursal Constitución', 'Av. Constitución 567', '064-231002', 'constitucion@heladeria.com', '9:00 AM - 9:00 PM', 1),
(4, 'Sucursal Chilca', 'Calle Real 890 - Chilca', '064-231003', 'chilca@heladeria.com', '10:00 AM - 8:00 PM', 1),
(5, 'Sucursal El Tambo', 'Av. Huancavelica 345', '064-231004', 'tambo@heladeria.com', '9:00 AM - 9:00 PM', 1);

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
(1, NULL, NULL, 'admin', '$2y$10$OEdzwvpBdHbsnCW2ff9CfOaKBRTSRJzT4Tg/sVf71toQ3YpMglKy6', 1, 1, '2025-11-20 14:15:21', NULL),
(2, 1, NULL, 'juan perez', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, '2025-11-20 14:15:50', NULL),
(3, 2, NULL, 'maria rod', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, '2025-11-20 14:15:50', NULL),
(4, NULL, 1, 'rosa vendedor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 2, 1, '2025-11-20 14:15:50', 1),
(5, NULL, 3, 'patricia vendedor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 2, 1, '2025-11-20 14:15:50', 2),
(6, NULL, NULL, 'supervisor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 1, 1, '2025-11-20 14:15:50', NULL);

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
(1, 'Rosa Martínez', '71234567', '987111222', 'rosa.m@heladeria.com', 'Mañana', '2025-11-20 14:15:50', 1),
(2, 'Diego Sánchez', '72345678', '987222333', 'diego.s@heladeria.com', 'Tarde', '2025-11-20 14:15:50', 1),
(3, 'Patricia Vega', '73456789', '987333444', 'patricia.v@heladeria.com', 'Mañana', '2025-11-20 14:15:50', 2),
(4, 'Roberto Cruz', '74567890', '987444555', 'roberto.c@heladeria.com', 'Tarde', '2025-11-20 14:15:50', 3),
(5, 'Carmen Flores', '75678901', '987555666', 'carmen.f@heladeria.com', 'Noche', '2025-11-20 14:15:50', 2);

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
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `igv` decimal(10,2) DEFAULT 0.00,
  `tipo_comprobante` varchar(20) DEFAULT 'boleta',
  `estado` enum('Pendiente','Procesada','Anulada') NOT NULL DEFAULT 'Procesada',
  `nota` text DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT 1,
  `id_cupon` int(11) DEFAULT NULL,
  `descuento_cupon` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `fecha`, `id_cliente`, `id_vendedor`, `total`, `subtotal`, `igv`, `tipo_comprobante`, `estado`, `nota`, `id_sucursal`, `id_cupon`, `descuento_cupon`) VALUES
(1, '2025-11-20 10:30:00', 1, 1, 13.00, 0.00, 0.00, 'boleta', 'Procesada', 'Pago en efectivo', 1, NULL, 0.00),
(2, '2025-11-20 11:45:00', 2, 1, 15.00, 0.00, 0.00, 'boleta', 'Procesada', 'Pago con tarjeta', 1, NULL, 0.00),
(3, '2025-11-20 14:20:00', 3, 2, 11.25, 0.00, 0.00, 'boleta', 'Procesada', NULL, 2, NULL, 0.00),
(4, '2025-11-20 16:00:00', 4, 3, 9.50, 0.00, 0.00, 'boleta', 'Procesada', 'Delivery', 3, NULL, 0.00),
(5, '2025-11-20 18:30:00', 5, 2, 16.00, 0.00, 0.00, 'boleta', 'Procesada', 'Para llevar', 2, NULL, 0.00),
(6, '2025-11-20 10:35:21', 1, 1, 17.25, 0.00, 0.00, 'boleta', '', NULL, 1, NULL, 0.00),
(7, '2025-11-20 10:39:03', 1, 1, 146.25, 0.00, 0.00, 'boleta', '', NULL, 1, NULL, 0.00),
(10, '2025-11-20 10:57:00', NULL, NULL, 7.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(11, '2025-11-20 10:57:15', 1, 1, 28.00, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(12, '2025-11-20 10:57:18', 1, 1, 28.00, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(13, '2025-11-20 10:57:21', 1, 1, 28.00, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(14, '2025-11-20 10:57:24', 1, 1, 28.00, 0.00, 0.00, 'boleta', '', NULL, 1, NULL, 0.00),
(15, '2025-11-20 10:58:44', NULL, NULL, 10.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(16, '2025-11-20 10:58:53', NULL, NULL, 4.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(17, '2025-11-20 10:59:12', NULL, NULL, 4.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(18, '2025-11-20 11:23:17', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(19, '2025-11-20 11:23:33', 1, 1, 8.50, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(20, '2025-11-20 11:23:43', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(21, '2025-11-20 11:24:08', 1, 1, 12.50, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(22, '2025-11-20 11:37:30', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(23, '2025-11-20 11:37:48', 1, 1, 2.50, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(28, '2025-11-20 11:44:34', 1, 1, 20.00, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(29, '2025-11-20 11:45:36', 1, 1, 7.00, 0.00, 0.00, 'boleta', 'Pendiente', NULL, 1, NULL, 0.00),
(30, '2025-11-20 11:47:28', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(31, '2025-11-20 11:52:42', 1, 1, 10.50, 0.00, 0.00, 'boleta', '', NULL, 1, NULL, 0.00),
(32, '2025-11-20 11:53:00', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(34, '2025-11-20 11:59:00', 1, 1, 9.75, 0.00, 0.00, 'boleta', '', 'Mesa: 3 - Pedido web', 1, NULL, 0.00),
(35, '2025-11-20 12:35:35', NULL, NULL, 4.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(36, '2025-11-20 12:36:07', 1, 1, 11.48, 0.00, 0.00, 'boleta', '', 'Mesa: 12 - Pedido web', 1, NULL, 0.00),
(37, '2025-11-20 13:57:08', NULL, NULL, 5.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(38, '2025-11-20 14:00:16', 1, NULL, 5.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(39, '2025-11-20 14:00:18', 1, NULL, 5.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(40, '2025-11-20 14:02:39', 1, NULL, 5.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(41, '2025-11-20 14:04:35', 1, 1, 4.00, 0.00, 0.00, 'boleta', '', 'Mesa: 2 - Pedido web', 1, NULL, 0.00),
(42, '2025-11-20 14:34:37', 1, 1, 9.80, 0.00, 0.00, 'boleta', 'Pendiente', 'Mesa: 3 - Cupón: NOELD65LTE - Pedido web', 1, NULL, 0.00),
(43, '2025-11-20 14:38:08', 1, 1, 2.63, 0.00, 0.00, 'boleta', 'Pendiente', 'Mesa: 1 - Pedido web', 1, NULL, 0.00),
(44, '2025-11-20 14:38:24', 1, 1, 4.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Mesa: 3 - Pedido web', 1, NULL, 0.00),
(45, '2025-11-20 14:40:37', 1, 1, 7.03, 0.00, 0.00, 'boleta', 'Pendiente', 'Mesa: 4 - Pedido web', 1, NULL, 0.00),
(46, '2025-11-20 14:41:12', 1, 1, 6.63, 0.00, 0.00, 'boleta', '', 'Mesa: 3 - Pedido web', 1, NULL, 0.00),
(47, '2025-11-20 15:03:35', 1, NULL, 4.00, 0.00, 0.00, 'boleta', '', 'Pedido rápido desde web', 1, NULL, 0.00),
(52, '2025-11-20 15:11:39', 1, NULL, 5.00, 0.00, 0.00, 'boleta', 'Pendiente', 'Pedido rápido desde web', 1, NULL, 0.00),
(53, '2025-11-20 15:35:12', 1, 1, 10.23, 0.00, 0.00, 'boleta', 'Pendiente', 'Mesa: 2 - Pedido web', 1, NULL, 0.00);

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
,`stock` decimal(10,2)
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
-- Indices de la tabla `inventario_sucursal`
--
ALTER TABLE `inventario_sucursal`
  ADD PRIMARY KEY (`id_producto`,`id_sucursal`),
  ADD KEY `id_sucursal` (`id_sucursal`);

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
-- Indices de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`id_sucursal`);

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
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `id_sucursal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id_vendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
-- Filtros para la tabla `inventario_sucursal`
--
ALTER TABLE `inventario_sucursal`
  ADD CONSTRAINT `inventario_sucursal_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventario_sucursal_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE;

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
CREATE DEFINER=`root`@`localhost` EVENT `evt_limpiar_sesiones` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-20 14:15:21' ON COMPLETION PRESERVE ENABLE DO BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_desactivar_promociones` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-20 14:15:21' ON COMPLETION PRESERVE ENABLE DO BEGIN
    UPDATE promociones
    SET activa = 0
    WHERE fecha_fin < CURDATE()
    AND activa = 1;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_desactivar_cupones_vencidos` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-20 14:15:22' ON COMPLETION PRESERVE ENABLE DO BEGIN
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
