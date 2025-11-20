<?php
require 'c:/xampp/htdocs/heladeriacg/conexion/conexion.php';
try {
    $stmt = $pdo->query("DESCRIBE productos");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        if ($row['Field'] === 'stock') {
            echo "Stock Type: " . $row['Type'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
