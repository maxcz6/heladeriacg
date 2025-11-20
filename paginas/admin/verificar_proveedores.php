<?php
/**
 * Script para verificar proveedores en la base de datos
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Verificación de Proveedores</title>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #0891b2; margin-bottom: 10px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #0891b2; color: white; }
    tr:nth-child(even) { background: #f9fafb; }
    .warning { background: #fef2f2 !important; color: #991b1b; }
    .info { background: #dbeafe; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0891b2; }
    .back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #0891b2; color: white; text-decoration: none; border-radius: 6px; }
    .back-btn:hover { background: #0e7490; }
</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h1>🏢 Verificación de Proveedores</h1>";

try {
    // Obtener todos los proveedores
    $stmt = $pdo->prepare("SELECT * FROM proveedores ORDER BY id_proveedor");
    $stmt->execute();
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>";
    echo "<strong>Total de proveedores:</strong> " . count($proveedores);
    echo "</div>";
    
    if (empty($proveedores)) {
        echo "<p style='color: red;'>⚠️ No hay proveedores registrados en la base de datos</p>";
    } else {
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Empresa</th>";
        echo "<th>Contacto</th>";
        echo "<th>Teléfono</th>";
        echo "<th>Correo</th>";
        echo "<th>Dirección</th>";
        echo "<th>Fecha Registro</th>";
        echo "<th>Estado</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($proveedores as $prov) {
            // Detectar nombres extraños
            $esRaro = strlen($prov['empresa']) < 5;
            $rowClass = $esRaro ? 'class="warning"' : '';
            
            echo "<tr $rowClass>";
            echo "<td><strong>" . htmlspecialchars($prov['id_proveedor']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($prov['empresa']) . ($esRaro ? " ⚠️" : "") . "</td>";
            echo "<td>" . htmlspecialchars($prov['contacto']) . "</td>";
            echo "<td>" . htmlspecialchars($prov['telefono']) . "</td>";
            echo "<td>" . htmlspecialchars($prov['correo'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($prov['direccion'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($prov['fecha_registro']) . "</td>";
            echo "<td>" . ($esRaro ? "⚠️ Nombre sospechoso" : "✓ OK") . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    }
    
    // Ahora mostrar qué productos usan cada proveedor
    echo "<h2 style='margin-top: 40px;'>📦 Productos por Proveedor</h2>";
    
    $stmtProductos = $pdo->prepare("
        SELECT 
            pr.id_proveedor,
            pr.empresa,
            COUNT(p.id_producto) as total_productos,
            GROUP_CONCAT(p.nombre SEPARATOR ', ') as productos
        FROM proveedores pr
        LEFT JOIN productos p ON pr.id_proveedor = p.id_proveedor
        GROUP BY pr.id_proveedor, pr.empresa
        ORDER BY pr.id_proveedor
    ");
    $stmtProductos->execute();
    $prodPorProv = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ID Proveedor</th>";
    echo "<th>Empresa</th>";
    echo "<th>Total Productos</th>";
    echo "<th>Productos</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($prodPorProv as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['id_proveedor']) . "</td>";
        echo "<td>" . htmlspecialchars($item['empresa']) . "</td>";
        echo "<td>" . htmlspecialchars($item['total_productos']) . "</td>";
        echo "<td>" . htmlspecialchars($item['productos'] ?? 'Ninguno') . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    
    echo "<div class='info' style='margin-top: 30px;'>";
    echo "<strong>💡 Nota:</strong> Si ves proveedores con nombres extraños (como 'leche', 'Cac', etc.), ";
    echo "significa que los datos se ingresaron incorrectamente. Debes editarlos desde la página de proveedores ";
    echo "o eliminarlos y crearlos correctamente.";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<a href='productos.php' class='back-btn'>← Volver a Productos</a>";
echo " ";
echo "<a href='proveedores.php' class='back-btn' style='background: #10b981;'>Ir a Proveedores</a>";

echo "</div>";
echo "</body></html>";
?>
