<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');

// Si ya hay sesión iniciada, redirigir al panel correspondiente
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
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
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/publico/estilos_login.css">
    <style>
        .login-links {
            text-align: center;
            margin-top: 1rem;
        }

        .login-links a {
            color: #0891b2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .login-links a:hover {
            color: #06b6d4;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <i class="fas fa-ice-cream logo-icon"></i>
                <h1>Concelato Gelateria</h1>
                <p>Sistema de Gestión</p>
            </div>

            <div class="form-container">
                <div class="form-toggle">
                    <button id="loginBtn" class="toggle-btn active" onclick="showLogin()">Iniciar Sesión</button>
                    <button id="registerBtn" class="toggle-btn" onclick="showRegister()">Registrarse</button>
                </div>

                <form id="loginForm" class="auth-form" action="login_process.php" method="POST">
                    <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="error-message">
                        <p style="color: #ef4444; text-align: center; margin-bottom: 1rem;"><?php echo $_SESSION['login_error']; ?></p>
                    </div>
                    <?php
                    unset($_SESSION['login_error']);
                    endif; ?>
                    <div class="input-group">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="submit-btn">Entrar</button>

                    <div class="help-text">
                        <p>Credenciales de prueba:</p>
                        <p>Admin: admin / admin</p>
                        <p>Empleado: empleado / empleado</p>
                        <p>Cliente: cliente / cliente</p>
                    </div>

                    <div class="login-links">
                        <p><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
                    </div>
                </form>

                <form id="registerForm" class="auth-form" style="display: none;" action="register_process.php" method="POST">
                    <div class="input-group">
                        <label for="new-username">Usuario</label>
                        <input type="text" id="new-username" name="username" required>
                    </div>

                    <div class="input-group">
                        <label for="new-password">Contraseña</label>
                        <input type="password" id="new-password" name="password" required>
                    </div>

                    <div class="input-group">
                        <label for="confirm-password">Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" name="confirm_password" required>
                    </div>

                    <div class="input-group">
                        <label for="rol">Tipo de Usuario</label>
                        <select id="rol" name="rol" required>
                            <option value="cliente">Cliente</option>
                            <option value="empleado">Empleado</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">Registrarse</button>

                    <div class="login-links">
                        <p><a href="#" onclick="showLogin()">¿Ya tienes cuenta? Inicia sesión</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginBtn').classList.add('active');
            document.getElementById('registerBtn').classList.remove('active');
        }

        function showRegister() {
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerBtn').classList.add('active');
            document.getElementById('loginBtn').classList.remove('active');
        }
    </script>
</body>
</html>