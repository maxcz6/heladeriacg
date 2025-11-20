<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables in database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\nChecking for discount/promotion related tables:\n";
    $discountTables = array_filter($tables, function($table) {
        return stripos($table, 'descuento') !== false || 
               stripos($table, 'promocion') !== false || 
               stripos($table, 'cupon') !== false ||
               stripos($table, 'discount') !== false || 
               stripos($table, 'promotion') !== false || 
               stripos($table, 'coupon') !== false;
    });
    
    if (!empty($discountTables)) {
        echo "Found potential discount/promotion tables:\n";
        foreach ($discountTables as $table) {
            echo "- $table\n";
        }
        
        // Show structure of relevant tables
        foreach ($discountTables as $table) {
            echo "\nStructure for table '$table':\n";
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']}, {$column['Null']}, {$column['Key']})\n";
            }
        }
    } else {
        echo "No discount/promotion related tables found.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Clean up the temporary file
if (file_exists('tmp_tables.txt')) {
    unlink('tmp_tables.txt');
}
?>