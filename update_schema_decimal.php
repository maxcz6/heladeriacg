<?php
require 'c:/xampp/htdocs/heladeriacg/conexion/conexion.php';

try {
    // Modify stock column to support decimals
    $sql = "ALTER TABLE productos MODIFY stock DECIMAL(10,2)";
    $pdo->exec($sql);
    echo "Stock column modified successfully to DECIMAL(10,2)\n";

    // Modify detalle_ventas cantidad column to support decimals if it exists
    // Checking if detalle_ventas exists and has cantidad
    $stmt = $pdo->query("DESCRIBE detalle_ventas");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('cantidad', $columns)) {
        $sql = "ALTER TABLE detalle_ventas MODIFY cantidad DECIMAL(10,2)";
        $pdo->exec($sql);
        echo "detalle_ventas.cantidad modified successfully to DECIMAL(10,2)\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
