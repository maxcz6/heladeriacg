<?php
/**
 * Script para verificar y mostrar los productos actuales en la base de datos
 * Esto te ayudará a identificar qué datos están mal
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Verificación de Productos</title>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #0891b2; color: white; }
    tr:nth-child(even) { background: #f9fafb; }
    .warning { background: #fef2f2 !important; }
    h1 { color: #0891b2; }
    .info { background: #dbeafe; padding: 15px; border-radius: 8px; margin: 20px 0; }
</style>";
echo "</head><body>";

echo "<h1>🔍 Verificación de Productos en la Base de Datos</h1>";

try {
    // Obtener todos los productos
    $stmt = $pdo->prepare("
        SELECT 
            p.id_producto,
            p.nombre,
            p.sabor,
            p.descripcion,
            p.precio,
            p.stock,
            p.id_proveedor,
            pr.empresa as proveedor_nombre,
            p.fecha_registro,
            p.activo
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
        ORDER BY p.id_producto
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>";
    echo "<strong>Total de productos encontrados:</strong> " . count($productos);
    echo "</div>";
    
    if (empty($productos)) {
        echo "<p style='color: red; font-size: 18px;'>⚠️ No hay productos en la base de datos</p>";
        echo "<p>Debes agregar productos usando el formulario en la página de administración.</p>";
    } else {
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Nombre</th>";
        echo "<th>Sabor</th>";
        echo "<th>Descripción</th>";
        echo "<th>Precio</th>";
        echo "<th>Stock</th>";
        echo "<th>ID Prov</th>";
        echo "<th>Proveedor</th>";
        echo "<th>Fecha Reg</th>";
        echo "<th>Activo</th>";
        echo "<th>Estado</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($productos as $producto) {
            // Detectar problemas
            $problemas = [];
            
            if (empty($producto['nombre']) || strlen($producto['nombre']) < 3) {
                $problemas[] = "Nombre muy corto o vacío";
            }
            
            if (empty($producto['sabor'])) {
                $problemas[] = "Sabor vacío";
            }
            
            if (!is_numeric($producto['precio']) || $producto['precio'] <= 0) {
                $problemas[] = "Precio inválido";
            }
            
            if (!is_numeric($producto['stock']) || $producto['stock'] < 0) {
                $problemas[] = "Stock inválido";
            }
            
            // Marcar fila con problemas
            $rowClass = !empty($problemas) ? 'class="warning"' : '';
            
            echo "<tr $rowClass>";
            echo "<td>" . htmlspecialchars($producto['id_producto']) . "</td>";
            echo "<td>" . htmlspecialchars($producto['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($producto['sabor']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($producto['descripcion'], 0, 30)) . "...</td>";
            echo "<td>S/. " . number_format($producto['precio'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($producto['stock']) . "L</td>";
            echo "<td>" . htmlspecialchars($producto['id_proveedor'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($producto['proveedor_nombre'] ?? 'Sin proveedor') . "</td>";
            echo "<td>" . htmlspecialchars($producto['fecha_registro']) . "</td>";
            echo "<td>" . ($producto['activo'] ? '✓ Sí' : '✗ No') . "</td>";
            echo "<td>" . (!empty($problemas) ? '⚠️ ' . implode(', ', $problemas) : '✓ OK') . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        
        // Contar problemas
        $productosConProblemas = 0;
        foreach ($productos as $producto) {
            if (empty($producto['nombre']) || strlen($producto['nombre']) < 3 ||
                empty($producto['sabor']) ||
                !is_numeric($producto['precio']) || $producto['precio'] <= 0 ||
                !is_numeric($producto['stock']) || $producto['stock'] < 0) {
                $productosConProblemas++;
            }
        }
        
        if ($productosConProblemas > 0) {
            echo "<div class='info' style='background: #fef2f2; border: 2px solid #ef4444;'>";
            echo "<strong style='color: #dc2626;'>⚠️ ATENCIÓN:</strong> Se encontraron <strong>$productosConProblemas productos con datos incorrectos</strong> (marcados en rojo).";
            echo "<br><br>";
            echo "<strong>Opciones para solucionar:</strong>";
            echo "<ol>";
            echo "<li>Edita cada producto manualmente desde la interfaz de admin</li>";
            echo "<li>Elimina los productos con datos incorrectos y créalos de nuevo</li>";
            echo "<li>Contacta al desarrollador para un script de limpieza masiva</li>";
            echo "</ol>";
            echo "</div>";
        } else {
            echo "<div class='info' style='background: #d1fae5; border: 2px solid #10b981;'>";
            echo "<strong style='color: #059669;'>✓ EXCELENTE:</strong> Todos los productos tienen datos correctos.";
            echo "</div>";
        }
    }
    
    // Mostrar estructura de la tabla
    echo "<h2>📋 Estructura de la tabla 'productos'</h2>";
    $stmtColumns = $pdo->query("DESCRIBE productos");
    $columns = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<br><br>";
echo "<a href='productos.php' style='display: inline-block; padding: 10px 20px; background: #0891b2; color: white; text-decoration: none; border-radius: 6px;'>← Volver a Productos</a>";

echo "</body></html>";
?>
