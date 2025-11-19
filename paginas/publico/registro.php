<?php
/**
 * Página de Registro de Usuario
 * Redirecciona a login.php donde está integrado el formulario de registro
 * en las pestañas de formulario
 */
session_start();

// Redirigir a la página de login con tab de registro activo
header('Location: login.php?tab=register');
exit();
?>