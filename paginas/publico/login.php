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
    <meta name="description" content="Iniciar sesión - Concelato Gelatería">
    <title>Iniciar Sesión - Concelato Gelatería</title>
    <link rel="stylesheet" href="/heladeriacg/css/publico/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- LOGO SECTION -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-ice-cream"></i>
                </div>
                <h1>Concelato Gelatería</h1>
                <p>Sistema de Gestión Integral</p>
            </div>

            <!-- TOGGLE BUTTONS -->
            <div class="toggle-group">
                <button type="button" class="toggle-btn active" data-tab="login" onclick="toggleTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
                <button type="button" class="toggle-btn" data-tab="register" onclick="toggleTab('register')">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>
            </div>

            <!-- LOGIN FORM -->
            <form id="loginForm" action="login_process.php" method="POST" class="auth-form">
                <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['login_error']); ?></span>
                </div>
                <?php unset($_SESSION['login_error']); endif; ?>

                <div class="input-group">
                    <label for="login-username">Usuario o Email</label>
                    <input 
                        type="text" 
                        id="login-username" 
                        name="username" 
                        required 
                        placeholder="Ingresa tu usuario o email"
                        autocomplete="username"
                    >
                </div>

                <div class="input-group">
                    <label for="login-password">Contraseña</label>
                    <input 
                        type="password" 
                        id="login-password" 
                        name="password" 
                        required 
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>

                <div class="guest-login">
                    <p>¿Quieres probar sin registrarte?</p>
                    <button type="button" class="btn-guest" onclick="loginAsGuest()">
                        <i class="fas fa-user"></i> Ingresar como Invitado
                    </button>
                </div>

                <div class="auth-link">
                    <p><a href="recuperar.php"><i class="fas fa-lock"></i> ¿Olvidaste tu contraseña?</a></p>
                </div>
            </form>

            <!-- REGISTER FORM -->
            <form id="registerForm" action="register_process.php" method="POST" class="auth-form" style="display: none;">
                <?php if (isset($_SESSION['register_error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['register_error']); ?></span>
                </div>
                <?php unset($_SESSION['register_error']); endif; ?>

                <div class="input-group">
                    <label for="reg-username">Usuario</label>
                    <input 
                        type="text" 
                        id="reg-username" 
                        name="username" 
                        required 
                        placeholder="Elige un nombre de usuario"
                        autocomplete="username"
                    >
                </div>

                <div class="input-group">
                    <label for="reg-email">Email</label>
                    <input 
                        type="email" 
                        id="reg-email" 
                        name="email" 
                        required 
                        placeholder="Ingresa tu email"
                        autocomplete="email"
                    >
                </div>

                <div class="input-group">
                    <label for="reg-password">Contraseña</label>
                    <input
                        type="password"
                        id="reg-password"
                        name="password"
                        required
                        placeholder="Ingresa tu contraseña"
                        autocomplete="new-password"
                    >
                </div>

                <div class="input-group">
                    <label for="reg-confirm">Confirmar Contraseña</label>
                    <input 
                        type="password" 
                        id="reg-confirm" 
                        name="password_confirm" 
                        required 
                        placeholder="Confirma tu contraseña"
                        autocomplete="new-password"
                    >
                </div>

                <div class="input-group">
                    <label for="reg-role">Tipo de Usuario</label>
                    <select id="reg-role" name="rol" required>
                        <option value="">Selecciona tu rol</option>
                        <option value="cliente">Cliente - Comprar Helados</option>
                        <option value="empleado">Empleado - Gestionar Inventario</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>

                <div class="auth-link">
                    <p>¿Ya tienes cuenta? <a href="#" onclick="toggleTab('login'); return false;">Inicia sesión</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="/heladeriacg/js/publico/script.js"></script>
    <script>
        // Toggle between login and register forms
        function toggleTab(tab) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const toggleButtons = document.querySelectorAll('.toggle-btn');

            toggleButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.tab === tab) {
                    btn.classList.add('active');
                }
            });

            if (tab === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                // Clear register form
                registerForm.reset();
            } else {
                registerForm.style.display = 'block';
                loginForm.style.display = 'none';
                // Clear login form
                loginForm.reset();
            }
        }

        // Handle tab from URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'login';
            toggleTab(tab);
        });

        // Function to login as guest (redirects to guest area)
        function loginAsGuest() {
            window.location.href = '../cliente/invitado.php';
        }
    </script>
</body>
</html>