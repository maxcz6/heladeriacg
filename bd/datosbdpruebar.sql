-- ============================================
-- REGISTROS DE EJEMPLO PARA HELADERÍA
-- ============================================

-- 1. TABLA: proveedores
INSERT INTO proveedores (empresa, contacto, telefono, correo, direccion) VALUES
('Lácteos del Valle', 'Ana García', '987654321', 'ana@lacteosv.com', 'Av. Los Andes 456'),
('Frutas Tropicales SAC', 'Luis Mendoza', '912345678', 'luis@frutastrop.com', 'Jr. Amazonas 789'),
('Chocolates Premium', 'Carmen Silva', '923456789', 'carmen@chocpremium.com', 'Calle Real 234'),
('Distribuidora Frost', 'Pedro Rojas', '934567890', 'pedro@frost.com', 'Av. Industrial 567'),
('Sabores Naturales', 'María López', '945678901', 'maria@sabores.com', 'Jr. Comercio 890');

-- 2. TABLA: productos
INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor, activo) VALUES
('Helado Artesanal', 'Lúcuma', 'Helado cremoso de lúcuma peruana', 4.50, 50, 1, 1),
('Helado Premium', 'Chocolate Belga', 'Chocolate importado de alta calidad', 5.00, 45, 3, 1),
('Helado Tropical', 'Mango', 'Mango fresco de la selva', 4.00, 60, 2, 1),
('Helado Clásico', 'Fresa', 'Fresas naturales seleccionadas', 3.50, 70, 2, 1),
('Helado Especial', 'Maracuyá', 'Fruta de la pasión peruana', 4.25, 40, 2, 1);

-- 3. TABLA: sucursales
INSERT INTO sucursales (nombre, direccion, telefono, correo, horario, activa) VALUES
('Sucursal Plaza Mayor', 'Plaza de Armas 101', '064-231000', 'plazamayor@heladeria.com', '9:00 AM - 10:00 PM', 1),
('Sucursal Real Plaza', 'Real Plaza 2do piso', '064-231001', 'realplaza@heladeria.com', '10:00 AM - 10:00 PM', 1),
('Sucursal Constitución', 'Av. Constitución 567', '064-231002', 'constitucion@heladeria.com', '9:00 AM - 9:00 PM', 1),
('Sucursal Chilca', 'Calle Real 890 - Chilca', '064-231003', 'chilca@heladeria.com', '10:00 AM - 8:00 PM', 1),
('Sucursal El Tambo', 'Av. Huancavelica 345', '064-231004', 'tambo@heladeria.com', '9:00 AM - 9:00 PM', 1);

-- 4. TABLA: clientes
INSERT INTO clientes (nombre, dni, telefono, direccion, correo, nota) VALUES
('Juan Pérez Gómez', '12345678', '987654321', 'Jr. Junín 123', 'juan.perez@gmail.com', 'Cliente VIP - descuento 10%'),
('María Rodríguez', '23456789', '912345678', 'Av. Ferrocarril 456', 'maria.r@hotmail.com', 'Le gusta el helado de chocolate'),
('Carlos Mendoza', '34567890', '923456789', 'Calle Real 789', 'cmendoza@yahoo.com', 'Alérgico a nueces'),
('Ana Torres Silva', '45678901', '934567890', 'Jr. Parra 234', 'ana.torres@outlook.com', 'Prefiere sabores frutales'),
('Luis Campos', '56789012', '945678901', 'Av. Giraldez 567', 'luiscampos@gmail.com', 'Cliente frecuente - programa de puntos');

-- 5. TABLA: vendedores
INSERT INTO vendedores (nombre, dni, telefono, correo, turno, id_sucursal) VALUES
('Rosa Martínez', '71234567', '987111222', 'rosa.m@heladeria.com', 'Mañana', 1),
('Diego Sánchez', '72345678', '987222333', 'diego.s@heladeria.com', 'Tarde', 1),
('Patricia Vega', '73456789', '987333444', 'patricia.v@heladeria.com', 'Mañana', 2),
('Roberto Cruz', '74567890', '987444555', 'roberto.c@heladeria.com', 'Tarde', 3),
('Carmen Flores', '75678901', '987555666', 'carmen.f@heladeria.com', 'Noche', 2);

-- 6. TABLA: usuarios (contraseña: helado123 para todos)
INSERT INTO usuarios (id_cliente, id_vendedor, username, password, id_role, activo, id_sucursal) VALUES
(1, NULL, 'juan.perez', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, NULL),
(2, NULL, 'maria.rod', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 3, 1, NULL),
(NULL, 1, 'rosa.vendedor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 2, 1, 1),
(NULL, 3, 'patricia.vendedor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 2, 1, 2),
(NULL, NULL, 'supervisor', '$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy', 1, 1, NULL);

-- 7. TABLA: inventario_sucursal
INSERT INTO inventario_sucursal (id_producto, id_sucursal, stock_sucursal) VALUES
(1, 1, 20),
(1, 2, 15),
(2, 1, 18),
(3, 2, 25),
(4, 3, 30);

-- 8. TABLA: ventas
INSERT INTO ventas (fecha, id_cliente, id_vendedor, total, estado, nota, id_sucursal) VALUES
('2025-11-20 10:30:00', 1, 1, 0, 'Procesada', 'Pago en efectivo', 1),
('2025-11-20 11:45:00', 2, 1, 0, 'Procesada', 'Pago con tarjeta', 1),
('2025-11-20 14:20:00', 3, 2, 0, 'Procesada', NULL, 2),
('2025-11-20 16:00:00', 4, 3, 0, 'Procesada', 'Delivery', 3),
('2025-11-20 18:30:00', 5, 2, 0, 'Procesada', 'Para llevar', 2);

-- 9. TABLA: detalle_ventas
INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit) VALUES
(1, 1, 2, 4.50),
(1, 3, 1, 4.00),
(2, 2, 3, 5.00),
(3, 4, 2, 3.50),
(3, 5, 1, 4.25),
(4, 1, 1, 4.50),
(4, 2, 1, 5.00),
(5, 3, 4, 4.00);

-- 10. TABLA: promociones (opcional - crear la tabla primero si no existe)
-- CREATE TABLE IF NOT EXISTS promociones (
--     id_promocion INT AUTO_INCREMENT PRIMARY KEY,
--     id_producto INT NOT NULL,
--     descuento DECIMAL(5,2) NOT NULL,
--     fecha_inicio DATE NOT NULL,
--     fecha_fin DATE NOT NULL,
--     activa TINYINT(1) DEFAULT 1,
--     FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
-- );

INSERT INTO promociones (id_producto, descuento, fecha_inicio, fecha_fin, activa) VALUES
(1, 15.00, '2025-11-20', '2025-11-30', 1),
(2, 20.00, '2025-11-20', '2025-11-25', 1),
(3, 10.00, '2025-11-18', '2025-11-22', 1),
(4, 25.00, '2025-11-19', '2025-11-26', 1),
(5, 12.00, '2025-11-20', '2025-11-27', 1);

-- ============================================
-- VERIFICACIÓN DE REGISTROS
-- ============================================

-- Ver todos los productos con su proveedor
SELECT p.nombre, p.sabor, p.precio, p.stock, pr.empresa as proveedor
FROM productos p
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor;

-- Ver ventas con detalles
SELECT v.id_venta, v.fecha, c.nombre as cliente, vd.nombre as vendedor, v.total, s.nombre as sucursal
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
LEFT JOIN vendedores vd ON v.id_vendedor = vd.id_vendedor
LEFT JOIN sucursales s ON v.id_sucursal = s.id_sucursal;

-- Ver inventario por sucursal
SELECT s.nombre as sucursal, p.nombre as producto, i.stock_sucursal
FROM inventario_sucursal i
INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
INNER JOIN productos p ON i.id_producto = p.id_producto
ORDER BY s.nombre, p.nombre;