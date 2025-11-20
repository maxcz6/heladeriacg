<?php
// Leer el archivo de backup
$archivo_backup = 'c:\xampp\htdocs\heladeriacg\backups\backup_Sucursal_Chilca_2025-11-20_15-52-06.sql';
$contenido_backup = file_get_contents($archivo_backup);

echo "=================================================================\n";
echo "VALIDACIÓN DE REGISTROS EN BACKUP\n";
echo "=================================================================\n\n";

// Tablas con datos según la verificación anterior
$tablas_esperadas = [
    'audit_logs' => 8,
    'clientes' => 5,
    'configuracion_sucursal' => 2,
    'cupones' => 1,
    'detalle_ventas' => 8,
    'inventario_sucursal' => 5,
    'productos' => 5,
    'promociones' => 5,
    'proveedores' => 5,
    'roles' => 3,
    'sucursales' => 5,
    'usuarios' => 6,
    'vendedores' => 5,
    'ventas' => 5,
];

$total_esperado = 0;
$total_encontrado = 0;

foreach ($tablas_esperadas as $tabla => $registros_esperados) {
    $total_esperado += $registros_esperados;
    
    // Buscar el patrón INSERT INTO `tabla` VALUES
    $patron = "INSERT INTO `$tabla` VALUES";
    
    if (strpos($contenido_backup, $patron) !== false) {
        // Contar valores entre paréntesis
        $patron_tabla = "/INSERT INTO `$tabla` VALUES(.+?)(?:;|\n\n)/s";
        if (preg_match($patron_tabla, $contenido_backup, $matches)) {
            // Contar paréntesis abiertos que indican registros
            $registros_encontrados = substr_count($matches[1], '(') - substr_count($matches[1], '((');
            $registros_encontrados = max(1, $registros_encontrados);
            
            $estado = ($registros_encontrados >= $registros_esperados) ? '✓' : '✗';
            echo "$estado $tabla: esperado=$registros_esperados, encontrado~$registros_encontrados\n";
            $total_encontrado += $registros_encontrados;
        } else {
            echo "✗ $tabla: No se encontró el patrón INSERT\n";
        }
    } else {
        echo "✗ $tabla: No está en el backup\n";
    }
}

echo "\n=================================================================\n";
echo "RESUMEN:\n";
echo "Total de registros esperados: $total_esperado\n";
echo "Total de registros aproximados encontrados: ~$total_encontrado\n";
echo "=================================================================\n";

if ($total_encontrado >= $total_esperado) {
    echo "\n✓ BACKUP COMPLETO: Todos los registros han sido guardados correctamente.\n";
} else {
    echo "\n✗ BACKUP INCOMPLETO: Faltan registros.\n";
}
?>
