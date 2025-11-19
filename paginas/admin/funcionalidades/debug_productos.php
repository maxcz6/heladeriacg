<?php
// Archivo de depuración para verificar la estructura de la tabla productos

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

echo "<h2>Depuración - Estructura de la tabla productos</h2>";

// Obtener información sobre la estructura de la tabla productos
$stmt = $pdo->query("DESCRIBE productos");
$estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Estructura de la tabla productos:</h3>";
foreach ($estructura as $columna) {
    echo "Campo: " . $columna['Field'] . ", Tipo: " . $columna['Type'] . ", Null: " . $columna['Null'] . ", Key: " . $columna['Key'] . "<br>";
}

// Obtener algunos registros de ejemplo
$stmt = $pdo->query("SELECT * FROM productos LIMIT 3");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Registros de ejemplo:</h3>";
foreach ($registros as $registro) {
    echo "<pre>";
    print_r($registro);
    echo "</pre>";
}
?>