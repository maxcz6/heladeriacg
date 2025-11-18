<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concelato Gelateria - Helados Artesanales</title>
    <link rel="stylesheet" href="/heladeriacg/css/publico/estilos_landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-ice-cream"></i>
                Concelato Gelateria
            </div>
            <nav>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#productos">Productos</a></li>
                    <li><a href="#sucursales">Sucursales</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </nav>
            <button class="login-btn" onclick="window.location.href='login.php'">
                Iniciar Sesión
            </button>
        </div>
    </header>

    <section class="hero" id="inicio">
        <div class="container">
            <h1>Helados Artesanales de Calidad</h1>
            <p>Descubre sabores únicos hechos con ingredientes naturales y amor artesanal</p>
            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <div class="cta-buttons">
                <a href="../../paginas/cliente/realizar_pedido.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Hacer Pedido
                </a>
                <a href="#productos" class="btn btn-secondary">
                    <i class="fas fa-gel"></i> Ver Productos
                </a>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Sistema
                </a>
            </div>
        </div>
    </section>

    <section class="features" id="productos">
        <div class="container">
            <h2>Nuestros Productos</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-ice-cream"></i>
                    </div>
                    <h3 class="feature-title">Helados Artesanales</h3>
                    <p>Elaborados con ingredientes naturales y sabores únicos que deleitarán tu paladar.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <h3 class="feature-title">Productos Premium</h3>
                    <p>Selección especial de helados con ingredientes de alta calidad y sabores exclusivos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3 class="feature-title">Fidelidad</h3>
                    <p>Sistema de fidelidad para clientes regulares con promociones exclusivas.</p>
                </div>
            </div>
        </div>
    </section>

    <footer id="contacto">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Horario</h3>
                    <p>Lun-Vie: 10:00 - 22:00</p>
                    <p>Sáb-Dom: 09:00 - 23:00</p>
                </div>
                <div class="footer-section">
                    <h3>Ubicación</h3>
                    <p>Av. Principal 123</p>
                    <p>Centro de la ciudad</p>
                    <p>Huancayo, Perú</p>
                </div>
                <div class="footer-section">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-phone"></i> +51 999 888 777</p>
                    <p><i class="fas fa-envelope"></i> info@concelatogelateria.com</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Simple animation on scroll
        window.addEventListener('scroll', function() {
            const elements = document.querySelectorAll('.feature-card, .hero, .cta-buttons');
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;

                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        });

        // Initialize elements with fade-in effect
        document.querySelectorAll('.feature-card, .hero, .cta-buttons').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
    </script>
</body>
</html>