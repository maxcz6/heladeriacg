<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('admin');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/clientes_db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/SucursalLocal.php');

header('Content-Type: application/json');

/*
 * NOTE (sincronización):
 * Este sistema mantiene bases de datos locales independientes por sucursal.
 * Para sincronizar datos entre sucursales se usa un servidor central que
 * aloja o expone las bases/datos de cada local (o provee endpoints API).
 * La acción 'sincronizar' implementada en este archivo realiza un envío
 * básico (resúmenes/lotess) y registra la marca de sincronización local.
 *
 * Importante: aquí solo se registra y transmite datos de forma básica;
 * la lógica para intercambiar, aplicar cambios remotos, resolver
 * conflictos o realizar replicación bidireccional debe implementarse en
 * el servidor central y en la lógica cliente (cURL/API), y se mantiene
 * fuera del alcance del frontend. Este comentario sólo aparece en el
 * código del servidor (no se muestra al usuario final).
 */

$sucursal_local = new SucursalLocal($pdo);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'seleccionar_sucursal':
            $id_sucursal = intval($_POST['id_sucursal']);
            
            // Validar que la sucursal exista
            $stmt = $pdo->prepare("SELECT id_sucursal FROM sucursales WHERE id_sucursal = :id");
            $stmt->bindParam(':id', $id_sucursal, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $sucursal_local->establecerSucursalActual($id_sucursal);
                echo json_encode([
                    'success' => true,
                    'message' => 'Sucursal seleccionada correctamente',
                    'id_sucursal' => $id_sucursal
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ]);
            }
            break;
        
        case 'obtener_sucursal_actual':
            $id_actual = $sucursal_local->obtenerSucursalActual();
            $datos = $sucursal_local->obtenerDatosSucursalActual($pdo);
            
            echo json_encode([
                'success' => true,
                'id_sucursal' => $id_actual,
                'datos' => $datos,
                'modo_offline' => $sucursal_local->estaEnModoOffline(),
                'ultimo_sincronizado' => $sucursal_local->obtenerUltimaSincronizacion()
            ]);
            break;
        
        case 'cambiar_modo_offline':
            $modo = isset($_POST['modo']) ? (bool)$_POST['modo'] : true;
            $sucursal_local->establecerModoOffline($modo);
            
            echo json_encode([
                'success' => true,
                'modo_offline' => $modo,
                'message' => $modo ? 'Modo offline activado' : 'Conectando a red...'
            ]);
            break;
        
        case 'sincronizar':
            $id_sucursal = intval($_POST['id_sucursal'] ?? 0);
            
            // Obtener todos los datos de la sucursal
            $stmt = $pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM productos WHERE activo = 1) as productos,
                    (SELECT COUNT(*) FROM clientes) as clientes,
                    (SELECT COUNT(*) FROM vendedores) as vendedores,
                    (SELECT SUM(total) FROM ventas WHERE id_sucursal = :id AND estado = 'Procesada') as ventas_total
            ");
            $stmt->bindParam(':id', $id_sucursal, PDO::PARAM_INT);
            $stmt->execute();
            $datos_sync = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sucursal_local->registrarSincronizacion($id_sucursal);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sincronización completada',
                'datos' => $datos_sync,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'obtener_estado':
            $id_actual = $sucursal_local->obtenerSucursalActual();
            echo json_encode([
                'success' => true,
                'id_sucursal_actual' => $id_actual,
                'modo_offline' => $sucursal_local->estaEnModoOffline(),
                'ultimo_sincronizado' => $sucursal_local->obtenerUltimaSincronizacion()
            ]);
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no reconocida'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
