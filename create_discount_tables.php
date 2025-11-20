<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

try {
    // Create descuentos table
    $sql_descuentos = "
    CREATE TABLE IF NOT EXISTS descuentos (
        id_descuento INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        tipo ENUM('porcentaje', 'monto_fijo') NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        fecha_inicio DATETIME NOT NULL,
        fecha_fin DATETIME,
        activo BOOLEAN DEFAULT TRUE,
        uso_maximo INT DEFAULT NULL,
        veces_usado INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_descuentos);
    echo "Table 'descuentos' created successfully.\n";
    
    // Create cupones table
    $sql_cupones = "
    CREATE TABLE IF NOT EXISTS cupones (
        id_cupon INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        id_descuento INT NOT NULL,
        activo BOOLEAN DEFAULT TRUE,
        uso_maximo INT DEFAULT 1,
        veces_usado INT DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_vencimiento DATETIME,
        FOREIGN KEY (id_descuento) REFERENCES descuentos(id_descuento) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_cupones);
    echo "Table 'cupones' created successfully.\n";
    
    // Create descuentos_productos table for specific product discounts
    $sql_descuentos_productos = "
    CREATE TABLE IF NOT EXISTS descuentos_productos (
        id_descuento_producto INT AUTO_INCREMENT PRIMARY KEY,
        id_descuento INT NOT NULL,
        id_producto INT NOT NULL,
        FOREIGN KEY (id_descuento) REFERENCES descuentos(id_descuento) ON DELETE CASCADE,
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_descuentos_productos);
    echo "Table 'descuentos_productos' created successfully.\n";
    
    // Create descuentos_clientes table for customer-specific discounts
    $sql_descuentos_clientes = "
    CREATE TABLE IF NOT EXISTS descuentos_clientes (
        id_descuento_cliente INT AUTO_INCREMENT PRIMARY KEY,
        id_descuento INT NOT NULL,
        id_cliente INT NOT NULL,
        FOREIGN KEY (id_descuento) REFERENCES descuentos(id_descuento) ON DELETE CASCADE,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_descuentos_clientes);
    echo "Table 'descuentos_clientes' created successfully.\n";
    
    // Insert some sample discount records if none exist
    $stmt_check = $pdo->query("SELECT COUNT(*) FROM descuentos");
    if ($stmt_check->fetchColumn() == 0) {
        $sql_insert_sample = "INSERT INTO descuentos (nombre, descripcion, tipo, valor, fecha_inicio, fecha_fin, activo, uso_maximo) VALUES
        ('Descuento de Bienvenida', 'Descuento especial para nuevos clientes', 'porcentaje', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1, 100),
        ('Promoción Temporada', 'Descuento por temporada alta', 'monto_fijo', 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 1, 50),
        ('Descuento por Volumen', 'Compre más y ahorre más', 'porcentaje', 15.00, NOW(), DATE_ADD(NOW(), INTERVAL 45 DAY), 1, 200)";
        
        $pdo->exec($sql_insert_sample);
        echo "Sample discount records inserted.\n";
        
        // Insert sample coupon
        $sql_insert_coupon = "INSERT INTO cupones (codigo, id_descuento, fecha_vencimiento) VALUES
        ('BIENVENIDA10', 1, DATE_ADD(NOW(), INTERVAL 30 DAY)),
        ('TEMPORADA2', 2, DATE_ADD(NOW(), INTERVAL 15 DAY))";
        
        $pdo->exec($sql_insert_coupon);
        echo "Sample coupon records inserted.\n";
    }
    
    echo "All discount and coupon tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}

// Clean up the file after execution
if (file_exists(__FILE__)) {
    unlink(__FILE__);
}
?>