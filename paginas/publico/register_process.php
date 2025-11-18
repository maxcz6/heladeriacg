<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $rol = $_POST['rol'];
    
    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = 'Las contraseñas no coinciden';
        header('Location: registro.php');
        exit();
    }
    
    // Verificar longitud de la contraseña
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: registro.php');
        exit();
    }
    
    // Preparar los datos para el registro
    $datos_usuario = [
        'username' => $username,
        'password' => $password,
        'rol' => $rol
    ];
    
    $resultado = registrarUsuario($datos_usuario);
    
    if ($resultado['success']) {
        $_SESSION['register_success'] = $resultado['message'];
        header('Location: login.php');
    } else {
        $_SESSION['register_error'] = $resultado['message'];
        header('Location: registro.php');
    }
    exit();
} else {
    header('Location: registro.php');
    exit();
}
?>