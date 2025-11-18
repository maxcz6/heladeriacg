/*
  Base de datos para "heladeriacg" (adaptada para XAMPP / MySQL 8+)

  Objetivos de esta versión:
  - Implementar una estructura coherente con la app (roles: admin/empleado/cliente)
  - Facilitar la gestión de ventas, inventario y usuarios
  - Proveer vistas e índices útiles para el frontend (React/PHP)
  - Incluir ejemplos de triggers (comentados) para: validar stock, mantener totales y auditoría

  NOTAS:
  - En producción: usar bcrypt para los passwords y no dejar hashes de ejemplo.
  - Antes de activar triggers que actualicen stock/totales, haga backup.
  - Si su MySQL es < 8.0, elimine o adapte CHECK y JSON usage según corresponda.
*/

DROP DATABASE IF EXISTS heladeriacgbd;
CREATE DATABASE heladeriacgbd
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
USE heladeriacgbd;

-- =====================================================
-- Roles
-- =====================================================
CREATE TABLE roles (
  id_role TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (nombre) VALUES ('admin'), ('empleado'), ('cliente');

-- =====================================================
-- Proveedores
-- =====================================================
CREATE TABLE proveedores (
  id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
  empresa VARCHAR(100) NOT NULL,
  contacto VARCHAR(80) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  correo VARCHAR(150),
  direccion VARCHAR(200),
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Clientes
-- =====================================================
CREATE TABLE clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  dni CHAR(12) NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  direccion VARCHAR(200) NULL,
  correo VARCHAR(150) NULL,
  fecha_registro DATE DEFAULT (CURRENT_DATE()),
  nota TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Vendedores / Empleados (se usa la tabla "vendedores" para vincular ventas y usuarios)
-- =====================================================
CREATE TABLE vendedores (
  id_vendedor INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  dni CHAR(12) NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  correo VARCHAR(150) NULL,
  turno ENUM('Mañana','Tarde','Noche') NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Productos
-- =====================================================
CREATE TABLE productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  sabor VARCHAR(60) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
  stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
  id_proveedor INT NULL,
  fecha_registro DATE DEFAULT (CURRENT_DATE()),
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  INDEX idx_sabor (sabor),
  INDEX idx_stock (stock),
  FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Ventas
-- =====================================================
CREATE TABLE ventas (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_cliente INT NULL,
  id_vendedor INT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  estado ENUM('Pendiente','Procesada','Anulada') NOT NULL DEFAULT 'Procesada',
  nota TEXT NULL,
  INDEX idx_fecha (fecha),
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (id_vendedor) REFERENCES vendedores(id_vendedor)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Detalle de ventas
-- =====================================================
CREATE TABLE detalle_ventas (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  precio_unit DECIMAL(10,2) NOT NULL CHECK (precio_unit >= 0),
  subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
  INDEX idx_id_venta (id_venta),
  INDEX idx_id_producto (id_producto),
  FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Usuarios (puede estar vinculado a cliente o vendedor según rol)
-- - Almacenar password como hash (bcrypt). En PHP use password_hash/password_verify.
-- =====================================================
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NULL,
  id_vendedor INT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  id_role TINYINT UNSIGNED NOT NULL DEFAULT 3,
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (id_vendedor) REFERENCES vendedores(id_vendedor) ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (id_role) REFERENCES roles(id_role) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Sesiones (opcional - si prefiere gestionar tokens de sesión desde BD)
-- =====================================================
CREATE TABLE sesiones (
  id_session CHAR(40) PRIMARY KEY,
  id_usuario INT NOT NULL,
  user_agent VARCHAR(255),
  ip VARCHAR(45),
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expiracion TIMESTAMP NULL,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Auditoría simple
-- =====================================================
CREATE TABLE audit_logs (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  tabla VARCHAR(100) NOT NULL,
  operacion ENUM('INSERT','UPDATE','DELETE') NOT NULL,
  referencia_id INT NULL,
  usuario VARCHAR(80) NULL,
  detalles JSON NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Vistas útiles para el frontend
-- =====================================================
CREATE OR REPLACE VIEW vw_productos_stock AS
SELECT id_producto, nombre, sabor, precio, stock,
  CASE
    WHEN stock > 30 THEN 'Disponible'
    WHEN stock BETWEEN 16 AND 30 THEN 'Medio'
    ELSE 'Bajo'
  END AS estado_stock
FROM productos;

CREATE OR REPLACE VIEW vw_ventas_resumen AS
SELECT v.id_venta, v.fecha, v.id_cliente, c.nombre AS cliente_nombre, v.id_vendedor, vd.nombre AS vendedor_nombre, v.total, v.estado
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
LEFT JOIN vendedores vd ON v.id_vendedor = vd.id_vendedor;

-- =====================================================
-- Seed de ejemplo (mínimo) - adaptar/rehacer en entorno real
-- =====================================================
INSERT INTO proveedores (empresa, contacto, telefono, correo, direccion) VALUES
  ('Lácteos Perú', 'María Torres', '923456789', 'contacto@lacteosperu.com', 'Av. Industria 45'),
  ('Cacao Andino', 'José Medina', '987654320', 'ventas@cacaoandino.com', 'Jr. Comercio 123');

INSERT INTO clientes (nombre, dni, telefono, direccion, correo, nota) VALUES
  ('Carlos López', '87654321', '987654321', 'Av. Lima 123', 'carlos@gmail.com', 'Cliente frecuente'),
  ('María Torres', '65432198', '912345678', 'Jr. Miraflores 432', 'maria@hotmail.com', '');

INSERT INTO vendedores (nombre, dni, telefono, correo, turno) VALUES
  ('Juan Pérez', '12345678', '999888777', 'juan@correo.com', 'Mañana'),
  ('Lucía Ramos', '87654322', '912333444', 'lucia@correo.com', 'Tarde');

INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor) VALUES
  ('Helado de Fresa', 'Fresa', 'Helado artesanal de fresa', 3.50, 30, 1),
  ('Helado de Chocolate', 'Chocolate', 'Chocolate oscuro premium', 3.75, 25, 2),
  ('Helado de Vainilla', 'Vainilla', 'Vainilla natural', 3.50, 20, 1);

-- Usuarios demo (usar hashes reales en producción; los valores son placeholders)
-- Puede generar hashes con PHP: password_hash('secret', PASSWORD_BCRYPT)
INSERT INTO usuarios (username, password, id_role, id_vendedor) VALUES
  ('admin', '$2y$10$EXAMPLE_HASH_ADMIN', 1, NULL),
  ('empleado', '$2y$10$EXAMPLE_HASH_EMP', 2, 1),
  ('cliente', '$2y$10$EXAMPLE_HASH_CLIENT', 3, NULL);

-- Ejemplo mínimo de venta + detalle (totales se pueden sincronizar con triggers abajo)
INSERT INTO ventas (id_cliente, id_vendedor) VALUES (1,1);
INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal) VALUES
  (LAST_INSERT_ID(), 1, 2, 3.50, 7.00);

/*
  TRIGGERS SUGERIDOS (comentados). Activar solo después de probar en una copia de datos.

  1) BEFORE INSERT ON detalle_ventas
     - Validar existencia de producto
     - Validar stock suficiente
     - Calcular subtotal = cantidad * precio_unit (y redondear)

  2) AFTER INSERT ON detalle_ventas
     - Reducir stock en productos
     - Actualizar ventas.total

  3) AFTER UPDATE ON detalle_ventas
     - Ajustar stock si cambió el id_producto o la cantidad
     - Recalcular totales de las ventas afectadas

  4) AFTER DELETE ON detalle_ventas
     - Restaurar stock
     - Recalcular ventas.total

  5) Triggers de auditoría: insertar filas en audit_logs

  Nota técnica: en MySQL usar DELIMITER para crear triggers. Ejemplos abajo.
*/

/*
DELIMITER $$

-- BEFORE INSERT: validar stock y calcular subtotal
CREATE TRIGGER trg_detalle_before_insert
BEFORE INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
  DECLARE current_stock INT;
  SELECT stock INTO current_stock FROM productos WHERE id_producto = NEW.id_producto FOR UPDATE;
  IF current_stock IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Producto no existe';
  END IF;
  IF NEW.cantidad > current_stock THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para el producto';
  END IF;
  SET NEW.subtotal = ROUND(NEW.cantidad * NEW.precio_unit, 2);
END$$

-- AFTER INSERT: disminuir stock y actualizar total venta
CREATE TRIGGER trg_detalle_after_insert
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
  UPDATE productos
    SET stock = stock - NEW.cantidad
    WHERE id_producto = NEW.id_producto;

  UPDATE ventas v
    SET v.total = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas dv WHERE dv.id_venta = NEW.id_venta)
    WHERE v.id_venta = NEW.id_venta;

  INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
    VALUES ('detalle_ventas','INSERT', NEW.id_detalle, USER(), JSON_OBJECT('id_venta',NEW.id_venta,'id_producto',NEW.id_producto,'cantidad',NEW.cantidad,'subtotal',NEW.subtotal));
END$$

-- AFTER UPDATE: ajustar stock si cambió id_producto o cantidad, y actualizar totales
CREATE TRIGGER trg_detalle_after_update
AFTER UPDATE ON detalle_ventas
FOR EACH ROW
BEGIN
  -- si se cambió producto o cantidad, restaurar stock del OLD y disminuir para NEW
  IF OLD.id_producto <> NEW.id_producto OR OLD.cantidad <> NEW.cantidad THEN
    -- restaurar stock del antiguo
    UPDATE productos SET stock = stock + OLD.cantidad WHERE id_producto = OLD.id_producto;
    -- disminuir stock del nuevo
    UPDATE productos SET stock = stock - NEW.cantidad WHERE id_producto = NEW.id_producto;
  END IF;

  -- actualizar total para venta antigua y nueva si cambió id_venta
  IF OLD.id_venta <> NEW.id_venta THEN
    UPDATE ventas v SET v.total = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas dv WHERE dv.id_venta = OLD.id_venta) WHERE v.id_venta = OLD.id_venta;
    UPDATE ventas v SET v.total = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas dv WHERE dv.id_venta = NEW.id_venta) WHERE v.id_venta = NEW.id_venta;
  ELSE
    UPDATE ventas v SET v.total = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas dv WHERE dv.id_venta = NEW.id_venta) WHERE v.id_venta = NEW.id_venta;
  END IF;

  INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
    VALUES ('detalle_ventas','UPDATE', NEW.id_detalle, USER(), JSON_OBJECT('old',JSON_OBJECT('id_producto',OLD.id_producto,'cantidad',OLD.cantidad),'new',JSON_OBJECT('id_producto',NEW.id_producto,'cantidad',NEW.cantidad)));
END$$

-- AFTER DELETE: restaurar stock y actualizar total
CREATE TRIGGER trg_detalle_after_delete
AFTER DELETE ON detalle_ventas
FOR EACH ROW
BEGIN
  UPDATE productos SET stock = stock + OLD.cantidad WHERE id_producto = OLD.id_producto;
  UPDATE ventas v SET v.total = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas dv WHERE dv.id_venta = OLD.id_venta) WHERE v.id_venta = OLD.id_venta;
  INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
    VALUES ('detalle_ventas','DELETE', OLD.id_detalle, USER(), JSON_OBJECT('id_venta',OLD.id_venta,'id_producto',OLD.id_producto,'cantidad',OLD.cantidad));
END$$

DELIMITER ;
*/

-- =====================================================
-- Recomendaciones de uso
-- =====================================================
-- 1) Crear backups antes de activar triggers en la BD productiva.
-- 2) Generar hashes de password con PHP: password_hash('password', PASSWORD_BCRYPT)
-- 3) Para el frontend React/PHP, use las vistas `vw_productos_stock` y `vw_ventas_resumen` para mostrar listados y resúmenes.

-- Fin del script


