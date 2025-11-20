<?php
include 'conexion/conexion.php';

// Obtener todas las tablas
$result = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'heladeriacgbd'");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);

echo "=================================================================\n";
echo "VERIFICACIÓN DE REGISTROS EN LA BASE DE DATOS\n";
echo "=================================================================\n\n";
echo "Total de tablas: " . count($tables) . "\n\n";

$total_registros = 0;

foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $total_registros += $count;
    echo "✓ $table: $count registros\n";
}

echo "\n=================================================================\n";
echo "TOTAL DE REGISTROS EN LA BD: $total_registros\n";
echo "=================================================================\n";
?>
