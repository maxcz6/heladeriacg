<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (iniciarSesion($username, $password)) {
        // Redirigir según el rol
        switch ($_SESSION['rol']) {
            case 'admin':
                header('Location: ../admin/index.php');
                break;
            case 'empleado':
                header('Location: ../empleado/index.php');
                break;
            case 'cliente':
                header('Location: ../cliente/index.php');
                break;
            default:
                header('Location: ../cliente/index.php');
                break;
        }
        exit();
    } else {
        // Mostrar error de autenticación
        $error = "Usuario o contraseña incorrectos";
        $_SESSION['login_error'] = $error;
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>