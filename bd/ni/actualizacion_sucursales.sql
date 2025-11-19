-- Script para actualizar la base de datos con la funcionalidad de sucursales

-- Crear tabla de sucursales
CREATE TABLE IF NOT EXISTS `sucursales` (
  `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo para sucursales
INSERT INTO `sucursales` (`id_sucursal`, `nombre`, `direccion`, `telefono`, `correo`, `horario`, `activa`) VALUES
(1, 'Sucursal Central', 'Av. Principal 123', '999-888-777', 'central@heladeria.com', '8am - 10pm', 1),
(2, 'Sucursal Norte', 'Av. Norte 456', '999-888-778', 'norte@heladeria.com', '9am - 9pm', 1),
(3, 'Sucursal Sur', 'Calle Sur 789', '999-888-779', 'sur@heladeria.com', '10am - 8pm', 1);

-- Añadir columna de sucursal a la tabla de usuarios
ALTER TABLE `usuarios` ADD COLUMN `id_sucursal` int(11) DEFAULT NULL;
ALTER TABLE `usuarios` ADD KEY `id_sucursal` (`id_sucursal`);
ALTER TABLE `usuarios` ADD CONSTRAINT `usuarios_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Añadir columna de sucursal a la tabla de ventas
ALTER TABLE `ventas` ADD COLUMN `id_sucursal` int(11) DEFAULT 1;
ALTER TABLE `ventas` ADD KEY `id_sucursal` (`id_sucursal`);
ALTER TABLE `ventas` ADD CONSTRAINT `ventas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Crear tabla de inventario por sucursal
CREATE TABLE IF NOT EXISTS `inventario_sucursal` (
  `id_producto` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `stock_sucursal` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_producto`,`id_sucursal`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `inventario_sucursal_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventario_sucursal_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Añadir columna de sucursal a la tabla de vendedores
ALTER TABLE `vendedores` ADD COLUMN `id_sucursal` int(11) DEFAULT NULL;
ALTER TABLE `vendedores` ADD KEY `id_sucursal` (`id_sucursal`);
ALTER TABLE `vendedores` ADD CONSTRAINT `vendedores_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Inicializar inventario para todas las sucursales con productos existentes
INSERT INTO inventario_sucursal (id_producto, id_sucursal, stock_sucursal)
SELECT p.id_producto, s.id_sucursal, p.stock
FROM productos p
CROSS JOIN sucursales s
WHERE NOT EXISTS (
    SELECT 1 FROM inventario_sucursal i 
    WHERE i.id_producto = p.id_producto AND i.id_sucursal = s.id_sucursal
);

-- Actualizar stock original en productos a 0 ya que ahora se gestiona por sucursal
UPDATE productos SET stock = 0;