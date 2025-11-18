<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/publico/estilos_registro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-container">
                <i class="fas fa-ice-cream logo-icon"></i>
                <h1>Concelato Gelateria</h1>
                <p>Registro de Usuario</p>
            </div>

            <?php if (isset($_SESSION['register_error'])): ?>
            <div class="error-message">
                <p style="color: #ef4444; text-align: center; margin-bottom: 1rem;"><?php echo $_SESSION['register_error']; ?></p>
            </div>
            <?php
            unset($_SESSION['register_error']);
            endif; ?>

            <form id="registerForm" class="register-form" action="register_process.php" method="POST">
                <div class="input-group">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
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

                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>

                <div class="login-link">
                    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>