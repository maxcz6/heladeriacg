-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2025 a las 02:02:08
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
(1, 'Carlos López', '87654321', '987654321', 'Av. Lima 123', 'carlos@gmail.com', '2025-11-17', 'Cliente frecuente'),
(2, 'María Torres', '65432198', '912345678', 'Jr. Miraflores 432', 'maria@hotmail.com', '2025-11-17', ''),
(3, 'Cliente Contado', NULL, NULL, NULL, NULL, '2025-11-17', NULL);

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
(1, 'Helado de Fresa', 'Fresa', 'Helado artesanal de fresa', 3.50, 29, 1, '2025-11-17', 0),
(2, 'Helado de Chocolate', 'Chocolate', 'Chocolate oscuro premium', 3.75, 24, 2, '2025-11-17', 1),
(3, 'Helado de Vainilla', 'Vainilla', 'Vainilla natural', 3.50, 19, 1, '2025-11-17', 1);

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
(1, 'Lácteos Perú', 'María Torres', '923456789', 'contacto@lacteosperu.com', 'Av. Industria 45', '2025-11-17 22:45:38'),
(2, 'Cacao Andino', 'José Medina', '987654320', 'ventas@cacaoandino.com', 'Jr. Comercio 123', '2025-11-17 22:45:38');

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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_cliente`, `id_vendedor`, `username`, `password`, `id_role`, `activo`, `fecha_registro`) VALUES
(1, NULL, NULL, 'admin', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 1, 1, '2025-11-17 22:45:38'),
(2, NULL, 1, 'empleado', '$2y$10$EXAMPLE_HASH_EMP', 2, 1, '2025-11-17 22:45:38'),
(3, NULL, NULL, 'cliente', '$2y$10$EXAMPLE_HASH_CLIENT', 3, 1, '2025-11-17 22:45:38'),
(4, NULL, NULL, 'cliente1', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, '2025-11-17 23:09:19'),
(5, NULL, NULL, 'empleado1', '$2y$10$up2ZrC.s9mzupTay4oXQ9eG/znyGt9EYkS1LJAOqaMznVKkC5u3mG', 2, 1, '2025-11-17 23:10:57');

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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`id_vendedor`, `nombre`, `dni`, `telefono`, `correo`, `turno`, `fecha_registro`) VALUES
(1, 'Juan Pérez', '12345678', '999888777', 'juan@correo.com', 'Mañana', '2025-11-17 22:45:38'),
(2, 'Lucía Ramos', '87654322', '912333444', 'lucia@correo.com', 'Tarde', '2025-11-17 22:45:38');

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
  `nota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `fecha`, `id_cliente`, `id_vendedor`, `total`, `estado`, `nota`) VALUES
(1, '2025-11-17 17:45:38', 1, 1, 0.00, 'Procesada', NULL),
(2, '2025-11-17 18:11:17', 3, NULL, 10.75, 'Procesada', NULL);

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
  ADD PRIMARY KEY (`id_log`);

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
  ADD KEY `id_proveedor` (`id_proveedor`);

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
  ADD KEY `id_role` (`id_role`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id_vendedor`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_vendedor` (`id_vendedor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id_vendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `usuarios_ibfk_3` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
