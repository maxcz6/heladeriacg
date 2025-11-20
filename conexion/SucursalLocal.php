<?php
/**
 * GESTOR DE SUCURSAL LOCAL
 * Maneja la sucursal seleccionada y su almacenamiento
 * Permite sincronización con otras sucursales
 */

class SucursalLocal {
    private $pdo;
    private $tabla_config = 'configuracion_sucursal';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->crearTablaConfiguracion();
    }
    
    /**
     * Crear tabla de configuración si no existe
     */
    private function crearTablaConfiguracion() {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS `configuracion_sucursal` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_sucursal_actual` INT NOT NULL,
                    `fecha_seleccion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `modo_offline` TINYINT DEFAULT 1,
                    `ultimo_sincronizado` DATETIME NULL,
                    `datos_locales_json` LONGTEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Exception $e) {
            // Tabla ya existe
        }
    }
    
    /**
     * Obtener sucursal actual seleccionada
     */
    public function obtenerSucursalActual() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_sucursal_actual FROM $this->tabla_config 
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['id_sucursal_actual'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Establecer sucursal actual
     */
    public function establecerSucursalActual($id_sucursal) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tabla_config (id_sucursal_actual, modo_offline)
                VALUES (:id_sucursal, 1)
                ON DUPLICATE KEY UPDATE 
                id_sucursal_actual = :id_sucursal,
                fecha_seleccion = CURRENT_TIMESTAMP
            ");
            $stmt->bindParam(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener información de sucursal actual
     */
    public function obtenerDatosSucursalActual($pdo) {
        $id_sucursal = $this->obtenerSucursalActual();
        if (!$id_sucursal) return null;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM sucursales WHERE id_sucursal = :id
            ");
            $stmt->bindParam(':id', $id_sucursal, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Establecer modo offline
     */
    public function establecerModoOffline($activo) {
        try {
            $estado = $activo ? 1 : 0;
            $stmt = $this->pdo->prepare("
                UPDATE $this->tabla_config 
                SET modo_offline = :modo
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->bindParam(':modo', $estado, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener estado offline
     */
    public function estaEnModoOffline() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT modo_offline FROM $this->tabla_config 
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? (bool)$resultado['modo_offline'] : true;
        } catch (Exception $e) {
            return true;
        }
    }
    
    /**
     * Guardar datos locales (JSON)
     */
    public function guardarDatosLocales($datos_json) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tabla_config 
                SET datos_locales_json = :datos
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->bindParam(':datos', $datos_json);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener datos locales guardados
     */
    public function obtenerDatosLocales() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT datos_locales_json FROM $this->tabla_config 
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? json_decode($resultado['datos_locales_json'], true) : [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Registrar sincronización
     */
    public function registrarSincronizacion($id_sucursal_remota = null) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tabla_config 
                SET ultimo_sincronizado = NOW(),
                    modo_offline = 0
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener última sincronización
     */
    public function obtenerUltimaSincronizacion() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT ultimo_sincronizado FROM $this->tabla_config 
                ORDER BY fecha_seleccion DESC LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['ultimo_sincronizado'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
