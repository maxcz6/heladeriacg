<?php
// Configuración para XAMPP
$servername = "localhost";
$username = "root";  // Usuario por defecto de XAMPP
$password = "";      // Contraseña por defecto de XAMPP (vacía)
$dbname = "heladeriacgbd";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa a la base de datos";
} catch(PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>