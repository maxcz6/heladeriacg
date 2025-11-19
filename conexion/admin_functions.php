<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

// Funciones para métricas y estadísticas del dashboard
function obtenerMetricasSistema() {
    global $pdo;
    
    try {
        $ventas_totales = 0;
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE estado = 'Procesada'");
        $stmt->execute();
        $ventas_totales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
        $stmt->execute();
        $productos_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes");
        $stmt->execute();
        $clientes_totales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vendedores");
        $stmt->execute();
        $empleados_totales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ventas WHERE estado = 'Pendiente'");
        $stmt->execute();
        $ventas_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock < 10 AND activo = 1");
        $stmt->execute();
        $productos_bajos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) as total 
            FROM ventas 
            WHERE DATE(fecha) = CURDATE() AND estado = 'Procesada'
        ");
        $stmt->execute();
        $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
        $stmt->execute();
        $usuarios_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'ventas_totales' => $ventas_totales,
            'productos_activos' => $productos_activos,
            'clientes_totales' => $clientes_totales,
            'empleados_totales' => $empleados_totales,
            'ventas_pendientes' => $ventas_pendientes,
            'productos_bajos' => $productos_bajos,
            'ventas_hoy' => $ventas_hoy,
            'usuarios_activos' => $usuarios_activos
        ];
    } catch(PDOException $e) {
        error_log("Error al obtener métricas del sistema: " . $e->getMessage());
        return [
            'ventas_totales' => 0,
            'productos_activos' => 0,
            'clientes_totales' => 0,
            'empleados_totales' => 0,
            'ventas_pendientes' => 0,
            'productos_bajos' => 0,
            'ventas_hoy' => 0,
            'usuarios_activos' => 0
        ];
    }
}

function obtenerVentasRecientes($limite = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT v.id_venta, v.fecha, v.total, v.estado, 
                   c.nombre as cliente_nombre, 
                   vd.nombre as vendedor_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN vendedores vd ON v.id_vendedor = vd.id_vendedor
            ORDER BY v.fecha DESC
            LIMIT :limite
        ");
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener ventas recientes: " . $e->getMessage());
        return [];
    }
}

function obtenerProductosBajos($limite = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id_producto, nombre, stock
            FROM productos
            WHERE stock < 10 AND activo = 1
            ORDER BY stock ASC
            LIMIT :limite
        ");
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener productos bajos: " . $e->getMessage());
        return [];
    }
}

function obtenerReporteVentasDiarias() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(fecha) as dia, 
                   COALESCE(SUM(total), 0) as total
            FROM ventas 
            WHERE DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado = 'Procesada'
            GROUP BY DATE(fecha)
            ORDER BY DATE(fecha)
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener reporte de ventas diarias: " . $e->getMessage());
        return [];
    }
}

function obtenerProductosMasVendidos() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id_producto, p.nombre, SUM(dv.cantidad) as total_vendido
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE v.estado = 'Procesada'
            GROUP BY p.id_producto, p.nombre
            ORDER BY total_vendido DESC
            LIMIT 5
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener productos más vendidos: " . $e->getMessage());
        return [];
    }
}

// Funciones para gestión avanzada de productos
function obtenerProductosDetallados($filtro = []) {
    global $pdo;
    
    try {
        $sql = "
            SELECT p.*, pr.empresa as proveedor_nombre, 
                   CASE 
                       WHEN p.stock > 30 THEN 'Disponible' 
                       WHEN p.stock between 16 and 30 THEN 'Medio' 
                       ELSE 'Bajo' 
                   END AS estado_stock
            FROM productos p
            LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        ";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['activo'])) {
            $where_conditions[] = "p.activo = :activo";
            $params['activo'] = $filtro['activo'];
        }
        
        if (isset($filtro['bajo_stock'])) {
            $where_conditions[] = "p.stock < 10";
        }
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(p.nombre LIKE :busqueda OR p.sabor LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY p.nombre";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener productos detallados: " . $e->getMessage());
        return [];
    }
}

function actualizarStockProducto($id_producto, $nuevo_stock) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id_producto");
        $stmt->bindParam(':stock', $nuevo_stock);
        $stmt->bindParam(':id_producto', $id_producto);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar stock del producto: " . $e->getMessage());
        return false;
    }
}

function actualizarPrecioProducto($id_producto, $nuevo_precio) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE productos SET precio = :precio WHERE id_producto = :id_producto");
        $stmt->bindParam(':precio', $nuevo_precio);
        $stmt->bindParam(':id_producto', $id_producto);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar precio del producto: " . $e->getMessage());
        return false;
    }
}

// Funciones de gestión de empleados
function obtenerEmpleados($filtro = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM vendedores";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(nombre LIKE :busqueda OR dni LIKE :busqueda OR telefono LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY nombre";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener empleados: " . $e->getMessage());
        return [];
    }
}

function crearEmpleado($datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO vendedores (nombre, dni, telefono, correo, turno)
            VALUES (:nombre, :dni, :telefono, :correo, :turno)
        ");
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':turno', $datos['turno']);
        
        if ($stmt->execute()) {
            $id_vendedor = $pdo->lastInsertId();
            
            // Crear usuario para el empleado si se proporciona username
            if (isset($datos['username']) && !empty($datos['username']) && isset($datos['password'])) {
                $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
                
                $stmt_user = $pdo->prepare("
                    INSERT INTO usuarios (id_vendedor, username, password, id_role)
                    VALUES (:id_vendedor, :username, :password, 2)  -- role 2 = empleado
                ");
                
                $stmt_user->bindParam(':id_vendedor', $id_vendedor);
                $stmt_user->bindParam(':username', $datos['username']);
                $stmt_user->bindParam(':password', $password_hash);
                
                $stmt_user->execute();
            }
            
            return ['success' => true, 'id' => $id_vendedor, 'message' => 'Empleado creado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al crear empleado'];
        }
    } catch(PDOException $e) {
        error_log("Error al crear empleado: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function actualizarEmpleado($id_vendedor, $datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE vendedores 
            SET nombre = :nombre, dni = :dni, telefono = :telefono, 
                correo = :correo, turno = :turno
            WHERE id_vendedor = :id_vendedor
        ");
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':turno', $datos['turno']);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar empleado: " . $e->getMessage());
        return false;
    }
}

// Funciones de gestión de clientes
function obtenerClientes($filtro = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM clientes";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(nombre LIKE :busqueda OR dni LIKE :busqueda OR telefono LIKE :busqueda OR correo LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY nombre";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener clientes: " . $e->getMessage());
        return [];
    }
}

function crearCliente($datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, dni, telefono, direccion, correo, nota)
            VALUES (:nombre, :dni, :telefono, :direccion, :correo, :nota)
        ");
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':nota', $datos['nota']);
        
        if ($stmt->execute()) {
            $id_cliente = $pdo->lastInsertId();
            
            // Crear usuario para el cliente si se proporciona username
            if (isset($datos['username']) && !empty($datos['username']) && isset($datos['password'])) {
                $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
                
                $stmt_user = $pdo->prepare("
                    INSERT INTO usuarios (id_cliente, username, password, id_role)
                    VALUES (:id_cliente, :username, :password, 3)  -- role 3 = cliente
                ");
                
                $stmt_user->bindParam(':id_cliente', $id_cliente);
                $stmt_user->bindParam(':username', $datos['username']);
                $stmt_user->bindParam(':password', $password_hash);
                
                $stmt_user->execute();
            }
            
            return ['success' => true, 'id' => $id_cliente, 'message' => 'Cliente creado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al crear cliente'];
        }
    } catch(PDOException $e) {
        error_log("Error al crear cliente: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function actualizarCliente($id_cliente, $datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET nombre = :nombre, dni = :dni, telefono = :telefono, 
                direccion = :direccion, correo = :correo, nota = :nota
            WHERE id_cliente = :id_cliente
        ");
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':nota', $datos['nota']);
        $stmt->bindParam(':id_cliente', $id_cliente);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar cliente: " . $e->getMessage());
        return false;
    }
}

// Funciones de gestión de proveedores
function obtenerProveedores($filtro = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM proveedores";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(empresa LIKE :busqueda OR contacto LIKE :busqueda OR telefono LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY empresa";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener proveedores: " . $e->getMessage());
        return [];
    }
}

function crearProveedor($datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO proveedores (empresa, contacto, telefono, correo, direccion)
            VALUES (:empresa, :contacto, :telefono, :correo, :direccion)
        ");
        
        $stmt->bindParam(':empresa', $datos['empresa']);
        $stmt->bindParam(':contacto', $datos['contacto']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Proveedor creado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al crear proveedor'];
        }
    } catch(PDOException $e) {
        error_log("Error al crear proveedor: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function actualizarProveedor($id_proveedor, $datos) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE proveedores 
            SET empresa = :empresa, contacto = :contacto, telefono = :telefono, 
                correo = :correo, direccion = :direccion
            WHERE id_proveedor = :id_proveedor
        ");
        
        $stmt->bindParam(':empresa', $datos['empresa']);
        $stmt->bindParam(':contacto', $datos['contacto']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':id_proveedor', $id_proveedor);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar proveedor: " . $e->getMessage());
        return false;
    }
}

// Funciones de informes y reportes avanzados
function generarReporteVentas($fecha_inicio = null, $fecha_fin = null, $tipo = 'diario') {
    global $pdo;
    
    try {
        $sql = "";
        
        if ($tipo === 'diario') {
            $sql = "
                SELECT DATE(fecha) as periodo, 
                       COUNT(*) as cantidad_ventas,
                       COALESCE(SUM(total), 0) as total_ingresos
                FROM ventas
                WHERE estado = 'Procesada'
            ";
        } elseif ($tipo === 'semanal') {
            $sql = "
                SELECT YEARWEEK(fecha, 1) as periodo, 
                       COUNT(*) as cantidad_ventas,
                       COALESCE(SUM(total), 0) as total_ingresos
                FROM ventas
                WHERE estado = 'Procesada'
            ";
        } elseif ($tipo === 'mensual') {
            $sql = "
                SELECT DATE_FORMAT(fecha, '%Y-%m') as periodo, 
                       COUNT(*) as cantidad_ventas,
                       COALESCE(SUM(total), 0) as total_ingresos
                FROM ventas
                WHERE estado = 'Procesada'
            ";
        }
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
        } elseif ($fecha_inicio) {
            $sql .= " AND fecha >= :fecha_inicio";
        } elseif ($fecha_fin) {
            $sql .= " AND fecha <= :fecha_fin";
        }
        
        $sql .= " GROUP BY periodo ORDER BY periodo";
        
        $stmt = $pdo->prepare($sql);
        
        if ($fecha_inicio) {
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        }
        if ($fecha_fin) {
            $stmt->bindParam(':fecha_fin', $fecha_fin);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al generar reporte de ventas: " . $e->getMessage());
        return [];
    }
}

function generarReporteProductosVendidos($fecha_inicio = null, $fecha_fin = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT p.id_producto, p.nombre, p.sabor, 
                   SUM(dv.cantidad) as total_vendido,
                   SUM(dv.subtotal) as ingresos_totales,
                   COUNT(DISTINCT v.id_venta) as veces_vendido
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE v.estado = 'Procesada'
        ";
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND v.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        } elseif ($fecha_inicio) {
            $sql .= " AND v.fecha >= :fecha_inicio";
        } elseif ($fecha_fin) {
            $sql .= " AND v.fecha <= :fecha_fin";
        }
        
        $sql .= " GROUP BY p.id_producto, p.nombre ORDER BY total_vendido DESC";
        
        $stmt = $pdo->prepare($sql);
        
        if ($fecha_inicio) {
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        }
        if ($fecha_fin) {
            $stmt->bindParam(':fecha_fin', $fecha_fin);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al generar reporte de productos vendidos: " . $e->getMessage());
        return [];
    }
}

function generarReporteInventario() {
    global $pdo;
    
    try {
        $sql = "
            SELECT p.id_producto, p.nombre, p.sabor, 
                   p.stock, p.precio,
                   pr.empresa as proveedor,
                   CASE 
                       WHEN p.stock > 30 THEN 'Disponible' 
                       WHEN p.stock between 16 and 30 THEN 'Medio' 
                       ELSE 'Bajo' 
                   END AS estado_stock
            FROM productos p
            LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
            WHERE p.activo = 1
            ORDER BY p.stock ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al generar reporte de inventario: " . $e->getMessage());
        return [];
    }
}

function generarReporteVentasPorCliente($fecha_inicio = null, $fecha_fin = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT c.id_cliente, c.nombre as cliente_nombre,
                   COUNT(v.id_venta) as total_compras,
                   COALESCE(SUM(v.total), 0) as total_gastado,
                   MAX(v.fecha) as ultima_compra
            FROM clientes c
            LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
            WHERE v.estado = 'Procesada' OR v.estado IS NULL
        ";
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND v.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        } elseif ($fecha_inicio) {
            $sql .= " AND v.fecha >= :fecha_inicio";
        } elseif ($fecha_fin) {
            $sql .= " AND v.fecha <= :fecha_fin";
        }
        
        $sql .= " GROUP BY c.id_cliente, c.nombre ORDER BY total_gastado DESC";
        
        $stmt = $pdo->prepare($sql);
        
        if ($fecha_inicio) {
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        }
        if ($fecha_fin) {
            $stmt->bindParam(':fecha_fin', $fecha_fin);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al generar reporte de ventas por cliente: " . $e->getMessage());
        return [];
    }
}

// Funciones de gestión de usuarios y roles
function obtenerUsuarios($filtro = []) {
    global $pdo;
    
    try {
        $sql = "
            SELECT u.id_usuario, u.username, u.id_role, r.nombre as rol_nombre, 
                   u.activo, u.fecha_registro, 
                   COALESCE(v.nombre, c.nombre) as nombre_relacionado
            FROM usuarios u
            JOIN roles r ON u.id_role = r.id_role
            LEFT JOIN vendedores v ON u.id_vendedor = v.id_vendedor
            LEFT JOIN clientes c ON u.id_cliente = c.id_cliente
        ";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(u.username LIKE :busqueda OR r.nombre LIKE :busqueda OR COALESCE(v.nombre, c.nombre) LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (isset($filtro['rol'])) {
            $where_conditions[] = "r.nombre = :rol";
            $params['rol'] = $filtro['rol'];
        }
        
        if (isset($filtro['activo'])) {
            $where_conditions[] = "u.activo = :activo";
            $params['activo'] = $filtro['activo'];
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY u.fecha_registro DESC";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener usuarios: " . $e->getMessage());
        return [];
    }
}

function crearUsuario($datos) {
    global $pdo;
    
    try {
        // Verificar si el username ya existe
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->bindParam(':username', $datos['username']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
        }
        
        // Obtener id_role basado en el nombre del rol
        $stmt_role = $pdo->prepare("SELECT id_role FROM roles WHERE nombre = :rol_nombre");
        $stmt_role->bindParam(':rol_nombre', $datos['rol']);
        $stmt_role->execute();
        $role = $stmt_role->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            return ['success' => false, 'message' => 'Rol no válido'];
        }
        
        // Encriptar la contraseña
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        // Determinar id_cliente o id_vendedor si se proporciona
        $id_cliente = null;
        $id_vendedor = null;
        
        if (isset($datos['id_cliente']) && !empty($datos['id_cliente'])) {
            $id_cliente = $datos['id_cliente'];
        } elseif (isset($datos['id_vendedor']) && !empty($datos['id_vendedor'])) {
            $id_vendedor = $datos['id_vendedor'];
        }
        
        // Registrar el usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password, id_role, id_cliente, id_vendedor, activo)
            VALUES (:username, :password, :id_role, :id_cliente, :id_vendedor, :activo)
        ");
        $stmt->bindParam(':username', $datos['username']);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':id_role', $role['id_role']);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':activo', $datos['activo']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el usuario'];
        }
    } catch(PDOException $e) {
        error_log("Error en crearUsuario: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function actualizarUsuario($id_usuario, $datos) {
    global $pdo;
    
    try {
        $sql = "UPDATE usuarios SET ";
        $params = [];
        $set_parts = [];
        
        if (isset($datos['username'])) {
            $set_parts[] = "username = :username";
            $params['username'] = $datos['username'];
        }
        
        if (isset($datos['password']) && !empty($datos['password'])) {
            $set_parts[] = "password = :password";
            $params['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($datos['id_role'])) {
            $set_parts[] = "id_role = :id_role";
            $params['id_role'] = $datos['id_role'];
        }
        
        if (isset($datos['activo'])) {
            $set_parts[] = "activo = :activo";
            $params['activo'] = $datos['activo'];
        }
        
        if (empty($set_parts)) {
            return false;
        }
        
        $sql .= implode(', ', $set_parts);
        $sql .= " WHERE id_usuario = :id_usuario";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        return false;
    }
}

// Funciones de auditoría y seguridad
function registrarAuditoria($tabla, $operacion, $referencia_id, $detalles = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
            VALUES (:tabla, :operacion, :referencia_id, :usuario, :detalles)
        ");
        
        $stmt->bindParam(':tabla', $tabla);
        $stmt->bindParam(':operacion', $operacion);
        $stmt->bindParam(':referencia_id', $referencia_id);
        $stmt->bindParam(':usuario', $_SESSION['username']);
        $stmt->bindParam(':detalles', $detalles);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al registrar auditoría: " . $e->getMessage());
        return false;
    }
}

function obtenerLogsAuditoria($filtro = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM audit_logs";
        
        $params = [];
        $where_conditions = [];
        
        if (isset($filtro['tabla'])) {
            $where_conditions[] = "tabla = :tabla";
            $params['tabla'] = $filtro['tabla'];
        }
        
        if (isset($filtro['operacion'])) {
            $where_conditions[] = "operacion = :operacion";
            $params['operacion'] = $filtro['operacion'];
        }
        
        if (isset($filtro['usuario'])) {
            $where_conditions[] = "usuario = :usuario";
            $params['usuario'] = $filtro['usuario'];
        }
        
        if (isset($filtro['fecha_inicio'])) {
            $where_conditions[] = "fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filtro['fecha_inicio'];
        }
        
        if (isset($filtro['fecha_fin'])) {
            $where_conditions[] = "fecha <= :fecha_fin";
            $params['fecha_fin'] = $filtro['fecha_fin'];
        }
        
        if (isset($filtro['busqueda'])) {
            $where_conditions[] = "(tabla LIKE :busqueda OR operacion LIKE :busqueda OR usuario LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtro['busqueda'] . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY fecha DESC LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener logs de auditoría: " . $e->getMessage());
        return [];
    }
}

// Funciones para operaciones por lotes
function actualizarStockPorLote($productos) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id_producto");
        
        foreach ($productos as $producto) {
            $stmt->bindParam(':stock', $producto['stock']);
            $stmt->bindParam(':id_producto', $producto['id_producto']);
            $stmt->execute();
        }
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Stock actualizado exitosamente para ' . count($productos) . ' productos'];
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al actualizar stock por lote: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function desactivarProductosPorLote($ids) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "UPDATE productos SET activo = 0 WHERE id_producto IN ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        
        $pdo->commit();
        return ['success' => true, 'message' => count($stmt->rowCount()) . ' productos desactivados'];
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al desactivar productos por lote: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function crearProductosPorLote($productos) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO productos (nombre, sabor, descripcion, precio, stock, id_proveedor, activo)
            VALUES (:nombre, :sabor, :descripcion, :precio, :stock, :id_proveedor, :activo)
        ");

        foreach ($productos as $producto) {
            // Verificar si el proveedor existe
            $id_proveedor = $producto['id_proveedor'];
            if ($id_proveedor) {
                $proveedor_check = $pdo->prepare("SELECT id_proveedor FROM proveedores WHERE id_proveedor = :id_proveedor");
                $proveedor_check->bindParam(':id_proveedor', $id_proveedor);
                $proveedor_check->execute();

                // Si el proveedor no existe, usar NULL
                if ($proveedor_check->rowCount() == 0) {
                    $id_proveedor = null;
                }
            }

            $stmt->bindParam(':nombre', $producto['nombre']);
            $stmt->bindParam(':sabor', $producto['sabor']);
            $stmt->bindParam(':descripcion', $producto['descripcion']);
            $stmt->bindParam(':precio', $producto['precio']);
            $stmt->bindParam(':stock', $producto['stock']);
            $stmt->bindParam(':id_proveedor', $id_proveedor);
            $stmt->bindParam(':activo', $producto['activo']);
            $stmt->execute();
        }

        $pdo->commit();
        return ['success' => true, 'message' => count($productos) . ' productos creados exitosamente'];
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Error al crear productos por lote: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Funciones de seguridad
function cambiarPasswordUsuario($id_usuario, $password_actual, $password_nuevo, $password_confirmar) {
    global $pdo;
    
    try {
        // Verificar que las contraseñas nuevas coincidan
        if ($password_nuevo !== $password_confirmar) {
            return ['success' => false, 'message' => 'Las contraseñas nuevas no coinciden'];
        }
        
        // Obtener el hash actual de la contraseña
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Verificar la contraseña actual
        if (!password_verify($password_actual, $usuario['password'])) {
            return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
        }
        
        // Actualizar la contraseña
        $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar la contraseña'];
        }
    } catch(PDOException $e) {
        error_log("Error al cambiar contraseña: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function obtenerEstadisticasInventario() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total de productos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
        $stmt->execute();
        $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos con bajo stock
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock < 10 AND activo = 1");
        $stmt->execute();
        $stats['productos_bajo_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos con stock medio
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock BETWEEN 10 AND 30 AND activo = 1");
        $stmt->execute();
        $stats['productos_stock_medio'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos con stock alto
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock > 30 AND activo = 1");
        $stmt->execute();
        $stats['productos_stock_alto'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Valor total del inventario
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(stock * precio), 0) as valor_total FROM productos WHERE activo = 1");
        $stmt->execute();
        $stats['valor_inventario'] = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'];
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Error al obtener estadísticas de inventario: " . $e->getMessage());
        return [
            'total_productos' => 0,
            'productos_bajo_stock' => 0,
            'productos_stock_medio' => 0,
            'productos_stock_alto' => 0,
            'valor_inventario' => 0
        ];
    }
}

function obtenerEstadisticasVentas($periodo = 'mensual') {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total de ventas
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as ingresos FROM ventas WHERE estado = 'Procesada'");
        $stmt->execute();
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_ventas'] = $ventas['total'];
        $stats['ingresos_totales'] = $ventas['ingresos'];
        
        // Ventas del día
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as ingresos FROM ventas WHERE DATE(fecha) = CURDATE() AND estado = 'Procesada'");
        $stmt->execute();
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ventas_hoy'] = $ventas['total'];
        $stats['ingresos_hoy'] = $ventas['ingresos'];
        
        // Ventas del mes
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as ingresos FROM ventas WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE()) AND estado = 'Procesada'");
        $stmt->execute();
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ventas_mes'] = $ventas['total'];
        $stats['ingresos_mes'] = $ventas['ingresos'];
        
        // Ventas del año
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as ingresos FROM ventas WHERE YEAR(fecha) = YEAR(CURDATE()) AND estado = 'Procesada'");
        $stmt->execute();
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ventas_ano'] = $ventas['total'];
        $stats['ingresos_ano'] = $ventas['ingresos'];
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Error al obtener estadísticas de ventas: " . $e->getMessage());
        return [
            'total_ventas' => 0,
            'ingresos_totales' => 0,
            'ventas_hoy' => 0,
            'ingresos_hoy' => 0,
            'ventas_mes' => 0,
            'ingresos_mes' => 0,
            'ventas_ano' => 0,
            'ingresos_ano' => 0
        ];
    }
}
?>