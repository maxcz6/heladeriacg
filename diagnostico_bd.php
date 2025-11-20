<?php
/**
 * Script de diagnóstico para verificar estructura de tablas
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Diagnóstico BD</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>";
echo "</head><body>";
echo "<h2>🔍 Diagnóstico de Base de Datos</h2>";
echo "<pre>";

try {
    // 1. Verificar tabla ventas
    echo "=== TABLA: ventas ===\n\n";
    $stmt = $pdo->query("DESCRIBE ventas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  • {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}\n";
    }
    
    echo "\n";
    
    // 2. Verificar tabla detalle_ventas
    echo "=== TABLA: detalle_ventas ===\n\n";
    $stmt = $pdo->query("DESCRIBE detalle_ventas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_id_detalle = false;
    $has_precio_unit = false;
    $has_subtotal = false;
    
    foreach ($columns as $col) {
        echo "  • {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}\n";
        
        if ($col['Field'] === 'id_detalle') $has_id_detalle = true;
        if ($col['Field'] === 'precio_unit') $has_precio_unit = true;
        if ($col['Field'] === 'subtotal') $has_subtotal = true;
    }
    
    echo "\n";
    
    if (!$has_id_detalle) {
        echo "<span class='warning'>⚠ Falta columna: id_detalle</span>\n";
    }
    if (!$has_precio_unit) {
        echo "<span class='warning'>⚠ Falta columna: precio_unit</span>\n";
    }
    if (!$has_subtotal) {
        echo "<span class='warning'>⚠ Falta columna: subtotal</span>\n";
    }
    
    echo "\n";
    
    // 3. Verificar tabla productos
    echo "=== TABLA: productos ===\n\n";
    $stmt = $pdo->query("DESCRIBE productos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  • {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "\n";
    
    // 4. Verificar tabla cupones
    echo "=== TABLA: cupones ===\n\n";
    $stmt = $pdo->query("DESCRIBE cupones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  • {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "\n";
    
    // 5. Probar inserción de prueba
    echo "=== PRUEBA DE INSERCIÓN ===\n\n";
    
    try {
        $pdo->beginTransaction();
        
        // Intentar crear una venta de prueba
        $stmt = $pdo->prepare("
            INSERT INTO ventas (id_cliente, id_vendedor, total, estado, nota)
            VALUES (1, 1, 10.00, 'Pendiente', 'Prueba')
        ");
        $stmt->execute();
        $id_venta_prueba = $pdo->lastInsertId();
        
        echo "<span class='success'>✅ Venta de prueba creada: ID $id_venta_prueba</span>\n";
        
        // Intentar crear detalle
        $stmt = $pdo->prepare("
            INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit, subtotal)
            VALUES (:id_venta, 1, 1, 10.00, 10.00)
        ");
        $stmt->bindParam(':id_venta', $id_venta_prueba);
        $stmt->execute();
        
        echo "<span class='success'>✅ Detalle de venta creado</span>\n";
        
        // Rollback para no guardar datos de prueba
        $pdo->rollback();
        echo "<span class='success'>✅ Rollback exitoso (datos de prueba eliminados)</span>\n";
        
    } catch (PDOException $e) {
        $pdo->rollback();
        echo "<span class='error'>❌ Error en prueba: {$e->getMessage()}</span>\n";
    }
    
    echo "\n";
    
    // 6. Verificar cupón específico
    echo "=== CUPÓN: NOELD65LTE ===\n\n";
    
    $stmt = $pdo->prepare("SELECT * FROM cupones WHERE codigo = 'NOELD65LTE'");
    $stmt->execute();
    $cupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cupon) {
        echo "<span class='success'>✅ Cupón encontrado</span>\n";
        echo "  • ID: {$cupon['id_cupon']}\n";
        echo "  • Código: {$cupon['codigo']}\n";
        echo "  • Descuento: {$cupon['valor_descuento']}\n";
        echo "  • Tipo: {$cupon['tipo_descuento']}\n";
        echo "  • Activo: " . ($cupon['activo'] ? 'Sí' : 'No') . "\n";
        echo "  • Fecha inicio: {$cupon['fecha_inicio']}\n";
        echo "  • Fecha fin: {$cupon['fecha_fin']}\n";
        echo "  • Usos: {$cupon['usos_actuales']}/{$cupon['usos_maximos']}\n";
    } else {
        echo "<span class='error'>❌ Cupón no encontrado</span>\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "<span class='success'>✅ DIAGNÓSTICO COMPLETADO</span>\n";
    echo "========================================\n";

} catch(PDOException $e) {
    echo "<span class='error'>❌ ERROR: {$e->getMessage()}</span>\n";
}

echo "</pre>";
echo "</body></html>";
?>
