<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recuperar contraseña - Concelato Gelatería">
    <title>Recuperar Contraseña - Concelato Gelatería</title>
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
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Recuperar Contraseña</h1>
                <p>Restablece el acceso a tu cuenta</p>
            </div>

            <!-- RECOVERY FORM -->
            <form id="recoveryForm" method="POST" action="#" onsubmit="handleRecovery(event)">
                <div class="input-group">
                    <label for="identifier">Email o Usuario</label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        placeholder="Ingresa tu email o nombre de usuario"
                        required
                        autocomplete="username"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Enviar Instrucciones
                </button>

                <!-- HELP TEXT -->
                <div class="help-text">
                    <h3 style="margin-bottom: 0.8rem; color: #1f2937;">
                        <i class="fas fa-info-circle" style="color: var(--color-primary); margin-right: 0.5rem;"></i>
                        Cómo funciona:
                    </h3>
                    <ol>
                        <li>Ingresa tu email o nombre de usuario</li>
                        <li>Recibirás un enlace de recuperación por email</li>
                        <li>Haz clic en el enlace para crear una nueva contraseña</li>
                        <li>Inicia sesión con tus nuevas credenciales</li>
                    </ol>
                </div>

                <!-- BACK LINK -->
                <div class="auth-link">
                    <p>
                        <a href="login.php">
                            <i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script src="/heladeriacg/js/publico/script.js"></script>
    <script>
        /**
         * Manejar el formulario de recuperación de contraseña
         */
        function handleRecovery(event) {
            event.preventDefault();

            const identifier = document.getElementById('identifier').value.trim();
            const recoveryForm = document.getElementById('recoveryForm');

            // Validaciones básicas
            if (!identifier) {
                showNotification('Por favor ingresa tu email o usuario', 'error');
                return;
            }

            // Validar email si contiene @
            if (identifier.includes('@')) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(identifier)) {
                    showNotification('Email inválido', 'error');
                    return;
                }
            }

            // Simular envío - en producción, hacer petición AJAX
            const submitBtn = recoveryForm.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            // Simular espera
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                showNotification('¡Instrucciones enviadas! Revisa tu email', 'success', 4000);
                
                // Limpiar formulario
                recoveryForm.reset();
                
                // Redirigir después de 2 segundos
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }, 1500);
        }
    </script>
</body>
</html>