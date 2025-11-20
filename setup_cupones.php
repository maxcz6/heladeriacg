<?php
/**
 * Script para verificar y corregir la estructura de la tabla cupones
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

echo "<h2>Verificación y Corrección de Tabla Cupones</h2>";
echo "<pre>";

try {
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'cupones'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        echo "❌ La tabla 'cupones' NO existe.\n";
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
        echo "✅ Tabla 'cupones' creada exitosamente.\n\n";
    } else {
        echo "✅ La tabla 'cupones' existe.\n\n";
        
        // Mostrar estructura actual
        echo "=== ESTRUCTURA ACTUAL ===\n";
        $stmt = $pdo->query("DESCRIBE cupones");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $existingColumns = [];
        foreach ($columns as $col) {
            $existingColumns[] = $col['Field'];
            echo "  • {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
        
        // Verificar y agregar columnas faltantes
        $requiredColumns = [
            'id_cupon' => "INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY",
            'codigo' => "VARCHAR(50) NOT NULL UNIQUE",
            'descuento' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'tipo_descuento' => "ENUM('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje'",
            'fecha_inicio' => "DATE NOT NULL",
            'fecha_fin' => "DATE NOT NULL",
            'usos_maximos' => "INT(11) DEFAULT NULL",
            'usos_actuales' => "INT(11) DEFAULT 0",
            'activo' => "TINYINT(1) DEFAULT 1",
            'descripcion' => "TEXT DEFAULT NULL"
        ];
        
        echo "=== VERIFICANDO COLUMNAS ===\n";
        $modified = false;
        
        foreach ($requiredColumns as $columnName => $columnDef) {
            if (!in_array($columnName, $existingColumns)) {
                echo "  ⚠ Columna '$columnName' no existe. Agregando...\n";
                
                // Preparar definición para ALTER TABLE
                $alterDef = $columnDef;
                // Remover PRIMARY KEY y UNIQUE de la definición para ALTER
                $alterDef = str_replace(' PRIMARY KEY', '', $alterDef);
                $alterDef = str_replace(' UNIQUE', '', $alterDef);
                
                try {
                    $pdo->exec("ALTER TABLE cupones ADD COLUMN `$columnName` $alterDef");
                    echo "    ✅ Columna '$columnName' agregada.\n";
                    $modified = true;
                } catch (PDOException $e) {
                    echo "    ❌ Error al agregar '$columnName': " . $e->getMessage() . "\n";
                }
            } else {
                echo "  ✅ Columna '$columnName' existe.\n";
            }
        }
        
        if ($modified) {
            echo "\n✅ Estructura de tabla actualizada.\n\n";
        } else {
            echo "\n✅ Todas las columnas necesarias existen.\n\n";
        }
    }
    
    // Verificar índices
    echo "=== VERIFICANDO ÍNDICES ===\n";
    $stmt = $pdo->query("SHOW INDEX FROM cupones");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasUniqueCode = false;
    foreach ($indexes as $index) {
        if ($index['Column_name'] === 'codigo' && $index['Non_unique'] == 0) {
            $hasUniqueCode = true;
        }
        echo "  • {$index['Key_name']} en {$index['Column_name']}\n";
    }
    
    if (!$hasUniqueCode) {
        echo "\n⚠ Creando índice UNIQUE para 'codigo'...\n";
        try {
            $pdo->exec("ALTER TABLE cupones ADD UNIQUE KEY `codigo` (`codigo`)");
            echo "✅ Índice creado.\n";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "=== DATOS ACTUALES ===\n";
    echo "Total de cupones: $count\n\n";
    
    if ($count == 0) {
        echo "⚠ No hay cupones. Insertando cupones de ejemplo...\n\n";
        
        $cupones = [
            [
                'codigo' => 'BIENVENIDO10',
                'descuento' => 10.00,
                'tipo' => 'porcentaje',
                'descripcion' => 'Cupón de bienvenida - 10% de descuento',
                'dias' => 30
            ],
            [
                'codigo' => 'VERANO2024',
                'descuento' => 15.00,
                'tipo' => 'porcentaje',
                'descripcion' => 'Promoción de verano - 15% de descuento',
                'dias' => 60
            ],
            [
                'codigo' => 'DESCUENTO5',
                'descuento' => 5.00,
                'tipo' => 'fijo',
                'descripcion' => 'Descuento fijo de S/. 5.00',
                'dias' => 90
            ],
            [
                'codigo' => 'PRIMERACOMPRA',
                'descuento' => 20.00,
                'tipo' => 'porcentaje',
                'descripcion' => 'Primera compra - 20% de descuento',
                'dias' => 30
            ],
            [
                'codigo' => 'ESPECIAL50',
                'descuento' => 50.00,
                'tipo' => 'porcentaje',
                'descripcion' => 'Cupón especial - 50% de descuento (limitado)',
                'dias' => 15,
                'usos_maximos' => 10
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO cupones (codigo, descuento, tipo_descuento, fecha_inicio, fecha_fin, usos_maximos, usos_actuales, activo, descripcion)
            VALUES (:codigo, :descuento, :tipo, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :dias DAY), :usos_maximos, 0, 1, :descripcion)
        ");

        foreach ($cupones as $cupon) {
            try {
                $stmt->execute([
                    ':codigo' => $cupon['codigo'],
                    ':descuento' => $cupon['descuento'],
                    ':tipo' => $cupon['tipo'],
                    ':dias' => $cupon['dias'],
                    ':usos_maximos' => $cupon['usos_maximos'] ?? null,
                    ':descripcion' => $cupon['descripcion']
                ]);
                echo "  ✅ Cupón '{$cupon['codigo']}' creado\n";
            } catch (PDOException $e) {
                echo "  ❌ Error al crear '{$cupon['codigo']}': " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    // Mostrar cupones activos
    echo "=== CUPONES ACTIVOS ===\n";
    $stmt = $pdo->query("
        SELECT codigo, descuento, tipo_descuento, fecha_inicio, fecha_fin, usos_maximos, usos_actuales, activo, descripcion
        FROM cupones
        WHERE activo = 1 AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
        ORDER BY codigo
    ");
    
    $cupones_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($cupones_activos) > 0) {
        foreach ($cupones_activos as $cupon) {
            $tipo = $cupon['tipo_descuento'] === 'porcentaje' ? '%' : 'S/.';
            $usos = $cupon['usos_maximos'] ? " ({$cupon['usos_actuales']}/{$cupon['usos_maximos']} usos)" : '';
            echo "  ✅ {$cupon['codigo']}: -{$cupon['descuento']}{$tipo}{$usos}\n";
            echo "     Válido: {$cupon['fecha_inicio']} a {$cupon['fecha_fin']}\n";
            if ($cupon['descripcion']) {
                echo "     {$cupon['descripcion']}\n";
            }
            echo "\n";
        }
    } else {
        echo "  ⚠ No hay cupones activos en este momento.\n";
        
        // Mostrar todos los cupones
        echo "\n=== TODOS LOS CUPONES ===\n";
        $stmt = $pdo->query("SELECT * FROM cupones ORDER BY codigo");
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($todos) > 0) {
            foreach ($todos as $cupon) {
                $estado = $cupon['activo'] ? 'Activo' : 'Inactivo';
                echo "  • {$cupon['codigo']}: {$estado}, válido {$cupon['fecha_inicio']} a {$cupon['fecha_fin']}\n";
            }
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "✅ CONFIGURACIÓN COMPLETADA\n";
    echo "========================================\n";
    echo "\nPuedes probar los cupones en la página de pedidos.\n";

} catch(PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nDetalles del error:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "</pre>";
?>
