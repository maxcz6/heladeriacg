<?php
/**
 * Script para verificar la estructura actual de cupones y adaptarla
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verificación Cupones</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>";
echo "</head><body>";
echo "<h2>🔍 Verificación de Estructura de Cupones</h2>";
echo "<pre>";

try {
    // 1. Verificar tabla cupones
    echo "=== TABLA: cupones ===\n\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'cupones'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='success'>✅ Tabla 'cupones' existe</span>\n\n";
        
        // Mostrar estructura actual
        echo "Estructura actual:\n";
        $stmt = $pdo->query("DESCRIBE cupones");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $columnNames = [];
        foreach ($columns as $col) {
            $columnNames[] = $col['Field'];
            $null = $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $col['Default'] !== null ? "DEFAULT '{$col['Default']}'" : '';
            echo "  • {$col['Field']}: {$col['Type']} {$null} {$default}\n";
        }
        
        echo "\n";
        
        // Verificar si necesitamos hacer cambios
        $needsUpdate = false;
        $updates = [];
        
        // Mapeo de columnas esperadas
        $expectedColumns = [
            'id_cupon' => ['type' => 'int', 'null' => false],
            'codigo' => ['type' => 'varchar', 'null' => false],
            'descuento' => ['type' => 'decimal', 'null' => false],
            'tipo_descuento' => ['type' => 'enum', 'null' => false],
            'fecha_inicio' => ['type' => 'date', 'null' => false],
            'fecha_fin' => ['type' => 'date', 'null' => false],
            'usos_maximos' => ['type' => 'int', 'null' => true],
            'usos_actuales' => ['type' => 'int', 'null' => true],
            'activo' => ['type' => 'tinyint', 'null' => true],
            'descripcion' => ['type' => 'text', 'null' => true]
        ];
        
        echo "Verificando columnas necesarias:\n";
        foreach ($expectedColumns as $colName => $specs) {
            if (!in_array($colName, $columnNames)) {
                echo "<span class='warning'>  ⚠ Falta columna: $colName</span>\n";
                $needsUpdate = true;
                
                // Preparar ALTER TABLE
                switch ($colName) {
                    case 'descuento':
                        $updates[] = "ADD COLUMN `descuento` DECIMAL(10,2) NOT NULL DEFAULT 0";
                        break;
                    case 'tipo_descuento':
                        $updates[] = "ADD COLUMN `tipo_descuento` ENUM('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje'";
                        break;
                    case 'fecha_inicio':
                        $updates[] = "ADD COLUMN `fecha_inicio` DATE NOT NULL";
                        break;
                    case 'fecha_fin':
                        $updates[] = "ADD COLUMN `fecha_fin` DATE NOT NULL";
                        break;
                    case 'usos_maximos':
                        $updates[] = "ADD COLUMN `usos_maximos` INT(11) DEFAULT NULL";
                        break;
                    case 'usos_actuales':
                        $updates[] = "ADD COLUMN `usos_actuales` INT(11) DEFAULT 0";
                        break;
                    case 'activo':
                        $updates[] = "ADD COLUMN `activo` TINYINT(1) DEFAULT 1";
                        break;
                    case 'descripcion':
                        $updates[] = "ADD COLUMN `descripcion` TEXT DEFAULT NULL";
                        break;
                }
            } else {
                echo "<span class='success'>  ✅ Columna existe: $colName</span>\n";
            }
        }
        
        echo "\n";
        
        // Aplicar actualizaciones si es necesario
        if ($needsUpdate && count($updates) > 0) {
            echo "<span class='warning'>⚠ Se necesitan actualizaciones. Aplicando cambios...</span>\n\n";
            
            foreach ($updates as $update) {
                try {
                    $sql = "ALTER TABLE cupones $update";
                    echo "Ejecutando: $sql\n";
                    $pdo->exec($sql);
                    echo "<span class='success'>  ✅ Actualización exitosa</span>\n";
                } catch (PDOException $e) {
                    echo "<span class='error'>  ❌ Error: {$e->getMessage()}</span>\n";
                }
            }
            echo "\n";
        } else {
            echo "<span class='success'>✅ Todas las columnas necesarias están presentes</span>\n\n";
        }
        
        // Mostrar datos actuales
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Total de registros: $total\n\n";
        
        if ($total > 0) {
            echo "Cupones existentes:\n";
            $stmt = $pdo->query("SELECT * FROM cupones LIMIT 10");
            $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($cupones as $cupon) {
                echo "  • ID: {$cupon['id_cupon']}";
                if (isset($cupon['codigo'])) echo " | Código: {$cupon['codigo']}";
                if (isset($cupon['descuento'])) echo " | Descuento: {$cupon['descuento']}";
                if (isset($cupon['tipo_descuento'])) echo " ({$cupon['tipo_descuento']})";
                if (isset($cupon['activo'])) echo " | Activo: " . ($cupon['activo'] ? 'Sí' : 'No');
                echo "\n";
            }
        }
        
    } else {
        echo "<span class='error'>❌ Tabla 'cupones' NO existe</span>\n";
        echo "Creando tabla...\n\n";
        
        $sql = "CREATE TABLE `cupones` (
            `id_cupon` INT(11) NOT NULL AUTO_INCREMENT,
            `codigo` VARCHAR(50) NOT NULL,
            `descuento` DECIMAL(10,2) NOT NULL,
            `tipo_descuento` ENUM('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje',
            `fecha_inicio` DATE NOT NULL,
            `fecha_fin` DATE NOT NULL,
            `usos_maximos` INT(11) DEFAULT NULL,
            `usos_actuales` INT(11) DEFAULT 0,
            `activo` TINYINT(1) DEFAULT 1,
            `descripcion` TEXT DEFAULT NULL,
            PRIMARY KEY (`id_cupon`),
            UNIQUE KEY `codigo` (`codigo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        echo "<span class='success'>✅ Tabla creada exitosamente</span>\n";
    }
    
    echo "\n";
    echo "=== TABLA: cupones_uso ===\n\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'cupones_uso'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='success'>✅ Tabla 'cupones_uso' existe</span>\n\n";
        
        $stmt = $pdo->query("DESCRIBE cupones_uso");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Estructura:\n";
        foreach ($columns as $col) {
            echo "  • {$col['Field']}: {$col['Type']}\n";
        }
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones_uso");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "\nTotal de registros: $total\n";
    } else {
        echo "<span class='warning'>⚠ Tabla 'cupones_uso' NO existe (opcional)</span>\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "<span class='success'>✅ VERIFICACIÓN COMPLETADA</span>\n";
    echo "========================================\n";
    
    // Probar una consulta
    echo "\nProbando consulta de cupones activos:\n";
    try {
        $stmt = $pdo->query("
            SELECT codigo, descuento, tipo_descuento, fecha_inicio, fecha_fin, activo
            FROM cupones
            WHERE activo = 1
            LIMIT 5
        ");
        $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($cupones) > 0) {
            echo "<span class='success'>✅ Consulta exitosa. Cupones encontrados:</span>\n";
            foreach ($cupones as $cupon) {
                $tipo = $cupon['tipo_descuento'] === 'porcentaje' ? '%' : 'S/.';
                echo "  • {$cupon['codigo']}: {$cupon['descuento']}{$tipo}\n";
            }
        } else {
            echo "<span class='warning'>⚠ No hay cupones activos</span>\n";
        }
    } catch (PDOException $e) {
        echo "<span class='error'>❌ Error en consulta: {$e->getMessage()}</span>\n";
    }

} catch(PDOException $e) {
    echo "<span class='error'>❌ ERROR GENERAL: {$e->getMessage()}</span>\n";
    echo "\nCódigo de error: {$e->getCode()}\n";
}

echo "</pre>";
echo "</body></html>";
?>
