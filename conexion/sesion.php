<?php
// Iniciar sesión sólo si no hay una activa (evita Notice si ya se llamó session_start en otra parte)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/conexion.php');

function iniciarSesion($username, $password) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT u.id_usuario, u.username, u.password, u.id_role, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.id_role = r.id_role
            WHERE u.username = :username AND u.activo = 1
        ");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Iniciar sesión con los datos del usuario
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol_nombre'];  // Guardamos el nombre del rol
            $_SESSION['logueado'] = true;

            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Error en iniciarSesion: " . $e->getMessage());
        return false;
    }
}

function registrarUsuario($datos) {
    global $pdo;

    try {
        // Verificar si el username ya existe
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->bindParam(':username', $datos['username']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
        }

        // Obtener id_role basado en el nombre del rol
        $stmt_role = $pdo->prepare("SELECT id_role FROM roles WHERE nombre = :rol_nombre");
        $stmt_role->bindParam(':rol_nombre', $datos['rol']);
        $stmt_role->execute();
        $role = $stmt_role->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            return ['success' => false, 'message' => 'Rol no válido'];
        }

        // Encriptar la contraseña
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);

        // Registrar el usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, id_role) VALUES (:username, :password, :id_role)");
        $stmt->bindParam(':username', $datos['username']);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':id_role', $role['id_role']);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el usuario'];
        }
    } catch(PDOException $e) {
        error_log("Error en registrarUsuario: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error en el servidor'];
    }
}

function verificarSesion() {
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        header('Location: ../paginas/publico/login.php');
        exit();
    }
}

function cerrarSesion() {
    session_destroy();
    header('Location: ../paginas/publico/login.php');
    exit();
}

function verificarRol($rol) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
        header('Location: ../publico/login.php');
        exit();
    }
}
?>