<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/publico/estilos_recuperar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <div class="logo-container">
                <i class="fas fa-ice-cream logo-icon"></i>
                <h1>Concelato Gelateria</h1>
                <p>Recuperar Contraseña</p>
            </div>
            
            <form id="recoveryForm" class="recovery-form">
                <div class="input-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required placeholder="Ingresa tu correo electrónico">
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Enviar Instrucciones
                </button>
                
                <div class="back-link">
                    <p><a href="login.php"><i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión</a></p>
                </div>
            </form>
            
            <div class="instructions">
                <h3>Instrucciones:</h3>
                <ol>
                    <li>Ingresa tu correo electrónico registrado</li>
                    <li>Te enviaremos un enlace para restablecer tu contraseña</li>
                    <li>Sigue las instrucciones en el correo para crear una nueva contraseña</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('recoveryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const correo = document.getElementById('correo').value;
            
            // Aquí se haría la validación real con PHP
            alert('Se ha enviado un enlace de recuperación a: ' + correo + '\n\nPor favor, revisa tu bandeja de entrada y sigue las instrucciones.');
            window.location.href = 'login.php';
        });
    </script>
</body>
</html>