-- ================================================================================
-- BACKUP DE BASE DE DATOS - HELADERÍA CG
-- ================================================================================
-- SUCURSAL: Sucursal Chilca
-- ID SUCURSAL: 4
-- DIRECCIÓN: Calle Real 890 - Chilca
-- TELÉFONO: 064-231003
-- EMAIL: N/A
-- ================================================================================
-- FECHA DE BACKUP: 2025-11-20 15:52:07
-- USUARIO QUE CREÓ: admin (ID: 1)
-- BASE DE DATOS: heladeriacgbd
-- ================================================================================

-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: heladeriacgbd
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `tabla` varchar(100) NOT NULL,
  `operacion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `usuario` varchar(80) DEFAULT NULL,
  `detalles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles`)),
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_audit_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,'ventas','INSERT',1,'rosa.vendedor','{\"id_venta\": 1, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 1}','2025-11-20 14:15:50'),(2,'ventas','INSERT',2,'rosa.vendedor','{\"id_venta\": 2, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 1}','2025-11-20 14:15:50'),(3,'ventas','INSERT',3,NULL,'{\"id_venta\": 3, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 2}','2025-11-20 14:15:50'),(4,'ventas','INSERT',4,'patricia.vendedor','{\"id_venta\": 4, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 3}','2025-11-20 14:15:50'),(5,'ventas','INSERT',5,NULL,'{\"id_venta\": 5, \"total\": 0.00, \"estado\": \"Procesada\", \"id_sucursal\": 2}','2025-11-20 14:15:50'),(6,'cupones','INSERT',1,NULL,'{\"codigo\": \"NOELD65LTE\", \"tipo_descuento\": \"monto_fijo\", \"valor_descuento\": 5.00, \"fecha_inicio\": \"2025-11-20\", \"fecha_fin\": \"2025-12-20\"}','2025-11-20 14:20:25'),(7,'productos','UPDATE',1,NULL,'{\"id_producto\": 1, \"precio_anterior\": 4.50, \"precio_nuevo\": 4.50, \"stock_anterior\": 50, \"stock_nuevo\": 5}','2025-11-20 14:31:37'),(8,'productos','UPDATE',5,NULL,'{\"id_producto\": 5, \"precio_anterior\": 4.25, \"precio_nuevo\": 4.25, \"stock_anterior\": 40, \"stock_nuevo\": 4}','2025-11-20 14:31:41');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `dni` char(12) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `nota` text DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Juan Pérez Gómez','12345678','987654321','Jr. Junín 123','juan.perez@gmail.com','2025-11-20','Cliente VIP - descuento 10%'),(2,'María Rodríguez','23456789','912345678','Av. Ferrocarril 456','maria.r@hotmail.com','2025-11-20','Le gusta el helado de chocolate'),(3,'Carlos Mendoza','34567890','923456789','Calle Real 789','cmendoza@yahoo.com','2025-11-20','Alérgico a nueces'),(4,'Ana Torres Silva','45678901','934567890','Jr. Parra 234','ana.torres@outlook.com','2025-11-20','Prefiere sabores frutales'),(5,'Luis Campos','56789012','945678901','Av. Giraldez 567','luiscampos@gmail.com','2025-11-20','Cliente frecuente - programa de puntos');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_sucursal`
--

DROP TABLE IF EXISTS `configuracion_sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_sucursal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sucursal_actual` int(11) NOT NULL,
  `fecha_seleccion` timestamp NOT NULL DEFAULT current_timestamp(),
  `modo_offline` tinyint(4) DEFAULT 1,
  `ultimo_sincronizado` datetime DEFAULT NULL,
  `datos_locales_json` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_sucursal`
--

LOCK TABLES `configuracion_sucursal` WRITE;
/*!40000 ALTER TABLE `configuracion_sucursal` DISABLE KEYS */;
INSERT INTO `configuracion_sucursal` VALUES (1,4,'2025-11-20 14:20:51',0,'2025-11-20 09:20:52',NULL),(2,4,'2025-11-20 14:21:49',0,'2025-11-20 09:41:14',NULL);
/*!40000 ALTER TABLE `configuracion_sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cupones`
--

DROP TABLE IF EXISTS `cupones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cupones` (
  `id_cupon` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `tipo_descuento` enum('porcentaje','monto_fijo') NOT NULL DEFAULT 'porcentaje',
  `valor_descuento` decimal(10,2) NOT NULL CHECK (`valor_descuento` > 0),
  `monto_minimo` decimal(10,2) DEFAULT 0.00,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `usos_maximos` int(11) DEFAULT NULL COMMENT 'NULL = ilimitado',
  `usos_por_cliente` int(11) DEFAULT 1,
  `usos_actuales` int(11) DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_promocion` int(11) DEFAULT NULL COMMENT 'Vinculado a promoción específica',
  `aplica_productos` text DEFAULT NULL COMMENT 'JSON con IDs de productos específicos',
  `aplica_categorias` text DEFAULT NULL COMMENT 'JSON con categorías/sabores',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_cupon`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_codigo_activo` (`codigo`,`activo`),
  KEY `idx_fecha_fin` (`fecha_fin`),
  KEY `id_promocion` (`id_promocion`),
  KEY `creado_por` (`creado_por`),
  CONSTRAINT `cupones_ibfk_promocion` FOREIGN KEY (`id_promocion`) REFERENCES `promociones` (`id_promocion`) ON DELETE SET NULL,
  CONSTRAINT `cupones_ibfk_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupones`
--

LOCK TABLES `cupones` WRITE;
/*!40000 ALTER TABLE `cupones` DISABLE KEYS */;
INSERT INTO `cupones` VALUES (1,'NOELD65LTE','2','monto_fijo',5.00,5.00,'2025-11-20','2025-12-20',2,1,0,1,NULL,NULL,NULL,'2025-11-20 14:20:25',1);
/*!40000 ALTER TABLE `cupones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_cupones_insert` AFTER INSERT ON `cupones` FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (tabla, operacion, referencia_id, detalles)
    VALUES (
        'cupones',
        'INSERT',
        NEW.id_cupon,
        JSON_OBJECT(
            'codigo', NEW.codigo,
            'tipo_descuento', NEW.tipo_descuento,
            'valor_descuento', NEW.valor_descuento,
            'fecha_inicio', NEW.fecha_inicio,
            'fecha_fin', NEW.fecha_fin
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_cupon_desactivar_por_usos` AFTER UPDATE ON `cupones` FOR EACH ROW
BEGIN
    IF NEW.usos_maximos IS NOT NULL AND NEW.usos_actuales >= NEW.usos_maximos THEN
        UPDATE cupones
        SET activo = 0
        WHERE id_cupon = NEW.id_cupon AND activo = 1;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cupones_uso`
--

DROP TABLE IF EXISTS `cupones_uso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cupones_uso` (
  `id_uso` int(11) NOT NULL AUTO_INCREMENT,
  `id_cupon` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `monto_descuento` decimal(10,2) NOT NULL,
  `monto_original` decimal(10,2) NOT NULL,
  `monto_final` decimal(10,2) NOT NULL,
  `fecha_uso` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_sucursal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_uso`),
  KEY `idx_id_cupon` (`id_cupon`),
  KEY `idx_id_venta` (`id_venta`),
  KEY `idx_id_cliente` (`id_cliente`),
  KEY `idx_fecha_uso` (`fecha_uso`),
  KEY `cupones_uso_ibfk_4` (`id_sucursal`),
  CONSTRAINT `cupones_uso_ibfk_1` FOREIGN KEY (`id_cupon`) REFERENCES `cupones` (`id_cupon`) ON DELETE CASCADE,
  CONSTRAINT `cupones_uso_ibfk_2` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE,
  CONSTRAINT `cupones_uso_ibfk_3` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL,
  CONSTRAINT `cupones_uso_ibfk_4` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupones_uso`
--

LOCK TABLES `cupones_uso` WRITE;
/*!40000 ALTER TABLE `cupones_uso` DISABLE KEYS */;
/*!40000 ALTER TABLE `cupones_uso` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_cupon_incrementar_uso` AFTER INSERT ON `cupones_uso` FOR EACH ROW
BEGIN
    UPDATE cupones
    SET usos_actuales = usos_actuales + 1
    WHERE id_cupon = NEW.id_cupon;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unit` decimal(10,2) NOT NULL CHECK (`precio_unit` >= 0),
  `subtotal` decimal(10,2) NOT NULL CHECK (`subtotal` >= 0),
  PRIMARY KEY (`id_detalle`),
  KEY `idx_id_venta` (`id_venta`),
  KEY `idx_id_producto` (`id_producto`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (1,1,1,2,4.50,9.00),(2,1,3,1,4.00,4.00),(3,2,2,3,5.00,15.00),(4,3,4,2,3.50,7.00),(5,3,5,1,4.25,4.25),(6,4,1,1,4.50,4.50),(7,4,2,1,5.00,5.00),(8,5,3,4,4.00,16.00);
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_detalle_ventas_before_insert` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_validar_stock_detalle` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    DECLARE stock_actual INT;
    DECLARE stock_sucursal INT;
    DECLARE id_suc INT;
    
    SELECT stock INTO stock_actual 
    FROM productos 
    WHERE id_producto = NEW.id_producto;
    
    SELECT id_sucursal INTO id_suc 
    FROM ventas 
    WHERE id_venta = NEW.id_venta;
    
    IF id_suc IS NOT NULL THEN
        SELECT stock_sucursal INTO stock_sucursal
        FROM inventario_sucursal
        WHERE id_producto = NEW.id_producto 
        AND id_sucursal = id_suc;
        
        IF stock_sucursal IS NOT NULL AND stock_sucursal < NEW.cantidad THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insuficiente en la sucursal para este producto';
        END IF;
    END IF;
    
    IF stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para este producto';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_detalle_ventas_after_insert` AFTER INSERT ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_detalle_ventas_before_update` BEFORE UPDATE ON `detalle_ventas` FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unit;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_detalle_ventas_after_update` AFTER UPDATE ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = NEW.id_venta
    )
    WHERE id_venta = NEW.id_venta;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_detalle_ventas_after_delete` AFTER DELETE ON `detalle_ventas` FOR EACH ROW
BEGIN
    UPDATE ventas 
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0) 
        FROM detalle_ventas 
        WHERE id_venta = OLD.id_venta
    )
    WHERE id_venta = OLD.id_venta;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `inventario_sucursal`
--

DROP TABLE IF EXISTS `inventario_sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_sucursal` (
  `id_producto` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `stock_sucursal` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_producto`,`id_sucursal`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `inventario_sucursal_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventario_sucursal_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_sucursal`
--

LOCK TABLES `inventario_sucursal` WRITE;
/*!40000 ALTER TABLE `inventario_sucursal` DISABLE KEYS */;
INSERT INTO `inventario_sucursal` VALUES (1,1,20),(1,2,15),(2,1,18),(3,2,25),(4,3,30);
/*!40000 ALTER TABLE `inventario_sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `sabor` varchar(60) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
  `id_proveedor` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_producto`),
  KEY `idx_sabor` (`sabor`),
  KEY `idx_stock` (`stock`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `idx_productos_activo` (`activo`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Helado Artesanal','Lúcuma','Helado cremoso de lúcuma peruana',4.50,5,1,'2025-11-20',1),(2,'Helado Premium','Chocolate Belga','Chocolate importado de alta calidad',5.00,45,3,'2025-11-20',1),(3,'Helado Tropical','Mango','Mango fresco de la selva',4.00,60,2,'2025-11-20',1),(4,'Helado Clásico','Fresa','Fresas naturales seleccionadas',3.50,70,2,'2025-11-20',1),(5,'Helado Especial','Maracuyá','Fruta de la pasión peruana',4.25,4,2,'2025-11-20',1);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_productos_update` AFTER UPDATE ON `productos` FOR EACH ROW
BEGIN
    IF OLD.precio != NEW.precio OR OLD.stock != NEW.stock THEN
        INSERT INTO audit_logs (tabla, operacion, referencia_id, detalles)
        VALUES (
            'productos',
            'UPDATE',
            NEW.id_producto,
            JSON_OBJECT(
                'id_producto', NEW.id_producto,
                'precio_anterior', OLD.precio,
                'precio_nuevo', NEW.precio,
                'stock_anterior', OLD.stock,
                'stock_nuevo', NEW.stock
            )
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `promociones`
--

DROP TABLE IF EXISTS `promociones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promociones` (
  `id_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `descuento` decimal(5,2) NOT NULL CHECK (`descuento` between 0 and 100),
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_promocion`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `promociones_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promociones`
--

LOCK TABLES `promociones` WRITE;
/*!40000 ALTER TABLE `promociones` DISABLE KEYS */;
INSERT INTO `promociones` VALUES (1,1,15.00,'2025-11-20','2025-11-30',1,NULL),(2,2,20.00,'2025-11-20','2025-11-25',1,NULL),(3,3,10.00,'2025-11-18','2025-11-22',1,NULL),(4,4,25.00,'2025-11-19','2025-11-26',1,NULL),(5,5,12.00,'2025-11-20','2025-11-27',1,NULL);
/*!40000 ALTER TABLE `promociones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `empresa` varchar(100) NOT NULL,
  `contacto` varchar(80) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Lácteos del Valle','Ana García','987654321','ana@lacteosv.com','Av. Los Andes 456','2025-11-20 14:15:50'),(2,'Frutas Tropicales SAC','Luis Mendoza','912345678','luis@frutastrop.com','Jr. Amazonas 789','2025-11-20 14:15:50'),(3,'Chocolates Premium','Carmen Silva','923456789','carmen@chocpremium.com','Calle Real 234','2025-11-20 14:15:50'),(4,'Distribuidora Frost','Pedro Rojas','934567890','pedro@frost.com','Av. Industrial 567','2025-11-20 14:15:50'),(5,'Sabores Naturales','María López','945678901','maria@sabores.com','Jr. Comercio 890','2025-11-20 14:15:50');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_role` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin'),(3,'cliente'),(2,'empleado');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones`
--

DROP TABLE IF EXISTS `sesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones` (
  `id_session` char(40) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiracion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_session`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_limpiar_sesiones` BEFORE INSERT ON `sesiones` FOR EACH ROW
BEGIN
    DELETE FROM sesiones 
    WHERE expiracion < NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sucursales`
--

DROP TABLE IF EXISTS `sucursales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursales` (
  `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sucursales`
--

LOCK TABLES `sucursales` WRITE;
/*!40000 ALTER TABLE `sucursales` DISABLE KEYS */;
INSERT INTO `sucursales` VALUES (1,'Sucursal Plaza Mayor','Plaza de Armas 101','064-231000','plazamayor@heladeria.com','9:00 AM - 10:00 PM',1),(2,'Sucursal Real Plaza','Real Plaza 2do piso','064-231001','realplaza@heladeria.com','10:00 AM - 10:00 PM',1),(3,'Sucursal Constitución','Av. Constitución 567','064-231002','constitucion@heladeria.com','9:00 AM - 9:00 PM',1),(4,'Sucursal Chilca','Calle Real 890 - Chilca','064-231003','chilca@heladeria.com','10:00 AM - 8:00 PM',1),(5,'Sucursal El Tambo','Av. Huancavelica 345','064-231004','tambo@heladeria.com','9:00 AM - 9:00 PM',1);
/*!40000 ALTER TABLE `sucursales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_sucursal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  KEY `id_role` (`id_role`),
  KEY `id_sucursal` (`id_sucursal`),
  KEY `idx_usuarios_activo` (`activo`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_3` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON UPDATE CASCADE,
  CONSTRAINT `usuarios_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,NULL,NULL,'admin','$2y$10$OEdzwvpBdHbsnCW2ff9CfOaKBRTSRJzT4Tg/sVf71toQ3YpMglKy6',1,1,'2025-11-20 14:15:21',NULL),(2,1,NULL,'juan perez','$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy',3,1,'2025-11-20 14:15:50',NULL),(3,2,NULL,'maria rod','$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy',3,1,'2025-11-20 14:15:50',NULL),(4,NULL,1,'rosa vendedor','$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy',2,1,'2025-11-20 14:15:50',1),(5,NULL,3,'patricia vendedor','$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy',2,1,'2025-11-20 14:15:50',2),(6,NULL,NULL,'supervisor','$2y$10$zYnd/QjgDMXBIIP1vu5z0upccWtkzNVgXMj73VMJSbM1N0oZ2CYBy',1,1,'2025-11-20 14:15:50',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendedores`
--

DROP TABLE IF EXISTS `vendedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendedores` (
  `id_vendedor` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `dni` char(12) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `turno` enum('Mañana','Tarde','Noche') DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_sucursal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_vendedor`),
  UNIQUE KEY `dni` (`dni`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `vendedores_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendedores`
--

LOCK TABLES `vendedores` WRITE;
/*!40000 ALTER TABLE `vendedores` DISABLE KEYS */;
INSERT INTO `vendedores` VALUES (1,'Rosa Martínez','71234567','987111222','rosa.m@heladeria.com','Mañana','2025-11-20 14:15:50',1),(2,'Diego Sánchez','72345678','987222333','diego.s@heladeria.com','Tarde','2025-11-20 14:15:50',1),(3,'Patricia Vega','73456789','987333444','patricia.v@heladeria.com','Mañana','2025-11-20 14:15:50',2),(4,'Roberto Cruz','74567890','987444555','roberto.c@heladeria.com','Tarde','2025-11-20 14:15:50',3),(5,'Carmen Flores','75678901','987555666','carmen.f@heladeria.com','Noche','2025-11-20 14:15:50',2);
/*!40000 ALTER TABLE `vendedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Procesada','Anulada') NOT NULL DEFAULT 'Procesada',
  `nota` text DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT 1,
  `id_cupon` int(11) DEFAULT NULL,
  `descuento_cupon` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id_venta`),
  KEY `idx_fecha` (`fecha`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  KEY `id_sucursal` (`id_sucursal`),
  KEY `idx_ventas_estado_fecha` (`estado`,`fecha`),
  KEY `idx_id_cupon` (`id_cupon`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ventas_ibfk_cupon` FOREIGN KEY (`id_cupon`) REFERENCES `cupones` (`id_cupon`) ON DELETE SET NULL,
  CONSTRAINT `ventas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,'2025-11-20 10:30:00',1,1,13.00,'Procesada','Pago en efectivo',1,NULL,0.00),(2,'2025-11-20 11:45:00',2,1,15.00,'Procesada','Pago con tarjeta',1,NULL,0.00),(3,'2025-11-20 14:20:00',3,2,11.25,'Procesada',NULL,2,NULL,0.00),(4,'2025-11-20 16:00:00',4,3,9.50,'Procesada','Delivery',3,NULL,0.00),(5,'2025-11-20 18:30:00',5,2,16.00,'Procesada','Para llevar',2,NULL,0.00);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_ventas_insert` AFTER INSERT ON `ventas` FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (tabla, operacion, referencia_id, usuario, detalles)
    VALUES (
        'ventas',
        'INSERT',
        NEW.id_venta,
        (SELECT username FROM usuarios u 
         INNER JOIN vendedores v ON u.id_vendedor = v.id_vendedor 
         WHERE v.id_vendedor = NEW.id_vendedor LIMIT 1),
        JSON_OBJECT(
            'id_venta', NEW.id_venta,
            'total', NEW.total,
            'estado', NEW.estado,
            'id_sucursal', NEW.id_sucursal
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_venta_procesar_stock` AFTER UPDATE ON `ventas` FOR EACH ROW
BEGIN
    IF OLD.estado = 'Pendiente' AND NEW.estado = 'Procesada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
        SET p.stock = p.stock - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta;
        
        UPDATE inventario_sucursal inv
        INNER JOIN detalle_ventas dv ON inv.id_producto = dv.id_producto
        SET inv.stock_sucursal = inv.stock_sucursal - dv.cantidad
        WHERE dv.id_venta = NEW.id_venta 
        AND inv.id_sucursal = NEW.id_sucursal;
    END IF;
    
    IF OLD.estado = 'Procesada' AND NEW.estado = 'Anulada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
        SET p.stock = p.stock + dv.cantidad
        WHERE dv.id_venta = NEW.id_venta;
        
        UPDATE inventario_sucursal inv
        INNER JOIN detalle_ventas dv ON inv.id_producto = dv.id_producto
        SET inv.stock_sucursal = inv.stock_sucursal + dv.cantidad
        WHERE dv.id_venta = NEW.id_venta 
        AND inv.id_sucursal = NEW.id_sucursal;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_ventas_update` AFTER UPDATE ON `ventas` FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO audit_logs (tabla, operacion, referencia_id, detalles)
        VALUES (
            'ventas',
            'UPDATE',
            NEW.id_venta,
            JSON_OBJECT(
                'id_venta', NEW.id_venta,
                'estado_anterior', OLD.estado,
                'estado_nuevo', NEW.estado
            )
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `vw_cupones_activos`
--

DROP TABLE IF EXISTS `vw_cupones_activos`;
/*!50001 DROP VIEW IF EXISTS `vw_cupones_activos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_cupones_activos` AS SELECT 
 1 AS `id_cupon`,
 1 AS `codigo`,
 1 AS `descripcion`,
 1 AS `tipo_descuento`,
 1 AS `valor_descuento`,
 1 AS `monto_minimo`,
 1 AS `fecha_inicio`,
 1 AS `fecha_fin`,
 1 AS `usos_actuales`,
 1 AS `usos_maximos`,
 1 AS `uso_estado`,
 1 AS `estado`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vw_cupones_estadisticas`
--

DROP TABLE IF EXISTS `vw_cupones_estadisticas`;
/*!50001 DROP VIEW IF EXISTS `vw_cupones_estadisticas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_cupones_estadisticas` AS SELECT 
 1 AS `codigo`,
 1 AS `descripcion`,
 1 AS `tipo_descuento`,
 1 AS `valor_descuento`,
 1 AS `total_usos`,
 1 AS `clientes_unicos`,
 1 AS `total_descuentos`,
 1 AS `descuento_promedio`,
 1 AS `primer_uso`,
 1 AS `ultimo_uso`,
 1 AS `dias_vigencia`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vw_productos_stock`
--

DROP TABLE IF EXISTS `vw_productos_stock`;
/*!50001 DROP VIEW IF EXISTS `vw_productos_stock`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_productos_stock` AS SELECT 
 1 AS `id_producto`,
 1 AS `nombre`,
 1 AS `sabor`,
 1 AS `precio`,
 1 AS `stock`,
 1 AS `estado_stock`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vw_ventas_resumen`
--

DROP TABLE IF EXISTS `vw_ventas_resumen`;
/*!50001 DROP VIEW IF EXISTS `vw_ventas_resumen`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_ventas_resumen` AS SELECT 
 1 AS `id_venta`,
 1 AS `fecha`,
 1 AS `id_cliente`,
 1 AS `cliente_nombre`,
 1 AS `id_vendedor`,
 1 AS `vendedor_nombre`,
 1 AS `total`,
 1 AS `estado`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_cupones_activos`
--

/*!50001 DROP VIEW IF EXISTS `vw_cupones_activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_cupones_activos` AS select `c`.`id_cupon` AS `id_cupon`,`c`.`codigo` AS `codigo`,`c`.`descripcion` AS `descripcion`,`c`.`tipo_descuento` AS `tipo_descuento`,`c`.`valor_descuento` AS `valor_descuento`,`c`.`monto_minimo` AS `monto_minimo`,`c`.`fecha_inicio` AS `fecha_inicio`,`c`.`fecha_fin` AS `fecha_fin`,`c`.`usos_actuales` AS `usos_actuales`,`c`.`usos_maximos` AS `usos_maximos`,case when `c`.`usos_maximos` is null then 'Ilimitado' else concat(`c`.`usos_actuales`,'/',`c`.`usos_maximos`) end AS `uso_estado`,case when curdate() < `c`.`fecha_inicio` then 'Próximo' when curdate() > `c`.`fecha_fin` then 'Vencido' when `c`.`usos_maximos` is not null and `c`.`usos_actuales` >= `c`.`usos_maximos` then 'Agotado' else 'Activo' end AS `estado` from `cupones` `c` where `c`.`activo` = 1 order by `c`.`fecha_fin` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_cupones_estadisticas`
--

/*!50001 DROP VIEW IF EXISTS `vw_cupones_estadisticas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_cupones_estadisticas` AS select `c`.`codigo` AS `codigo`,`c`.`descripcion` AS `descripcion`,`c`.`tipo_descuento` AS `tipo_descuento`,`c`.`valor_descuento` AS `valor_descuento`,count(`cu`.`id_uso`) AS `total_usos`,count(distinct `cu`.`id_cliente`) AS `clientes_unicos`,sum(`cu`.`monto_descuento`) AS `total_descuentos`,avg(`cu`.`monto_descuento`) AS `descuento_promedio`,min(`cu`.`fecha_uso`) AS `primer_uso`,max(`cu`.`fecha_uso`) AS `ultimo_uso`,to_days(`c`.`fecha_fin`) - to_days(`c`.`fecha_inicio`) AS `dias_vigencia` from (`cupones` `c` left join `cupones_uso` `cu` on(`c`.`id_cupon` = `cu`.`id_cupon`)) group by `c`.`id_cupon`,`c`.`codigo`,`c`.`descripcion`,`c`.`tipo_descuento`,`c`.`valor_descuento`,`c`.`fecha_inicio`,`c`.`fecha_fin` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_productos_stock`
--

/*!50001 DROP VIEW IF EXISTS `vw_productos_stock`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_productos_stock` AS select `productos`.`id_producto` AS `id_producto`,`productos`.`nombre` AS `nombre`,`productos`.`sabor` AS `sabor`,`productos`.`precio` AS `precio`,`productos`.`stock` AS `stock`,case when `productos`.`stock` > 30 then 'Disponible' when `productos`.`stock` between 16 and 30 then 'Medio' else 'Bajo' end AS `estado_stock` from `productos` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_ventas_resumen`
--

/*!50001 DROP VIEW IF EXISTS `vw_ventas_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ventas_resumen` AS select `v`.`id_venta` AS `id_venta`,`v`.`fecha` AS `fecha`,`v`.`id_cliente` AS `id_cliente`,`c`.`nombre` AS `cliente_nombre`,`v`.`id_vendedor` AS `id_vendedor`,`vd`.`nombre` AS `vendedor_nombre`,`v`.`total` AS `total`,`v`.`estado` AS `estado` from ((`ventas` `v` left join `clientes` `c` on(`v`.`id_cliente` = `c`.`id_cliente`)) left join `vendedores` `vd` on(`v`.`id_vendedor` = `vd`.`id_vendedor`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-20  9:52:06
