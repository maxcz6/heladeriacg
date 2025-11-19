<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Concelato Gelatería - Helados Artesanales Premium en Huancayo">
    <title>Concelato Gelatería - Helados Artesanales Premium</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --color-primary: #06b6d4;
            --color-secondary: #0891b2;
            --color-accent: #f97316;
            --color-dark: #1f2937;
            --color-light: #f9fafb;
            --color-white: #ffffff;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #f3f4f6 50%, #f9fafb 100%);
            color: var(--color-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ============================================
           HEADER / NAVBAR
           ============================================ */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--color-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .logo i {
            color: var(--color-accent);
        }

        .nav {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav a {
            text-decoration: none;
            color: var(--color-dark);
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .nav a:hover {
            background: rgba(6, 182, 202, 0.1);
            color: var(--color-primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 202, 0.3);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        .hero {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: clamp(3rem, 8vw, 6rem) 1.5rem;
            text-align: center;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .hero h1 {
            font-size: clamp(2.2rem, 6vw, 3.5rem);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero p {
            font-size: clamp(1rem, 2vw, 1.3rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 600px;
        }

        .hero-btns {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: white;
            color: var(--color-secondary);
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: inherit;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 1rem 2rem;
            border: 2px solid white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: inherit;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--color-secondary);
        }

        /* ============================================
           ABOUT SECTION
           ============================================ */
        .about {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .about-content h2 {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--color-dark);
        }

        .about-content p {
            font-size: 1.05rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .about-img {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border-radius: 20px;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: white;
        }

        /* ============================================
           PRODUCTS SECTION
           ============================================ */
        .products {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 3rem;
            color: var(--color-dark);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            border-color: var(--color-primary);
        }

        .product-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: white;
        }

        .product-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--color-dark);
        }

        .product-card p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* ============================================
           SUCURSALES SECTION
           ============================================ */
        .sucursales {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .sucursales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .sucursal-card {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .sucursal-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--color-primary);
        }

        .sucursal-card h3 {
            font-size: 1.3rem;
            color: var(--color-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sucursal-card p {
            margin-bottom: 0.8rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sucursal-card i {
            color: var(--color-primary);
            width: 20px;
        }

        /* ============================================
           PROMOCIONES SECTION
           ============================================ */
        .promociones {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 4rem 1.5rem;
            margin: 4rem 0;
        }

        .promociones-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .promociones h2 {
            text-align: center;
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 3rem;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .promo-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }

        .promo-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .promo-card .discount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .promo-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .promo-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .promo-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            word-break: break-word;
        }

        /* ============================================
           CONTACT SECTION
           ============================================ */
        .contact {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
            text-align: center;
        }

        .contact h2 {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
        }

        .contact p {
            font-size: 1.05rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .contact-item {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .contact-item i {
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .contact-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .contact-item p {
            font-size: 0.95rem;
            margin: 0;
            word-break: break-word;
        }

        /* ============================================
           FOOTER
           ============================================ */
        footer {
            background: #1f2937;
            color: white;
            padding: 3rem 1.5rem 1rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: var(--color-primary);
        }

        .footer-section a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--color-primary);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s;
            margin: 0;
        }

        .social-links a:hover {
            background: var(--color-secondary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            text-align: center;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .header-container {
                flex-wrap: wrap;
            }

            .nav {
                order: 3;
                width: 100%;
                flex-direction: column;
                gap: 0.5rem;
            }

            .about {
                grid-template-columns: 1fr;
            }

            .about-img {
                min-height: 250px;
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }

            .hero-btns {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 0.75rem 0;
            }

            .header-container {
                padding: 0 1rem;
            }

            .logo {
                font-size: 1.4rem;
            }

            .nav {
                gap: 1rem;
            }

            .nav a {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }

            .btn-login {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .hero-btns {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-primary,
            .btn-secondary {
                padding: 0.8rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-ice-cream"></i> Concelato
            </div>
            <nav class="nav">
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#productos">Productos</a>
                <a href="#sucursales">Sucursales</a>
                <a href="#promociones">Promociones</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <button class="btn-login" onclick="window.location.href='login.php'">
                <i class="fas fa-sign-in-alt"></i> Ingresar
            </button>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="inicio">
        <h1>Concelato Gelatería</h1>
        <p>Disfruta de los helados artesanales más deliciosos de Huancayo. Hecho con ingredientes premium y amor por el sabor.</p>
        <div class="hero-btns">
            <button class="btn-primary" onclick="window.location.href='login.php?tab=register'">
                <i class="fas fa-user-plus"></i> Registrarse
            </button>
            <button class="btn-secondary" onclick="document.getElementById('productos').scrollIntoView({behavior: 'smooth'})">
                <i class="fas fa-eye"></i> Ver Productos
            </button>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about" id="nosotros">
        <div class="about-content">
            <h2>Sobre Concelato</h2>
            <p>Somos una heladería artesanal con más de 10 años de experiencia en Huancayo, comprometidos con la calidad y la satisfacción de nuestros clientes.</p>
            <p>Nuestros helados se elaboran diariamente con ingredientes premium, sin conservantes artificiales y con recetas exclusivas que te harán volver por más.</p>
            <p>Contamos con múltiples sucursales estratégicamente ubicadas para tu conveniencia, ofertas especiales y un sistema de pedidos en línea para que disfrutes desde casa.</p>
        </div>
        <div class="about-img">
            <i class="fas fa-award"></i>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products" id="productos">
        <h2 class="section-title">Nuestros Productos</h2>
        <div class="products-grid">
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-snowflake"></i>
                </div>
                <h3>Helados Artesanales</h3>
                <p>Sabores exclusivos preparados diariamente con ingredientes premium.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-lollipop"></i>
                </div>
                <h3>Paletas Gourmet</h3>
                <p>Refrescantes paletas con frutas naturales y presentación elegante.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-cake-candles"></i>
                </div>
                <h3>Postres Especiales</h3>
                <p>Combinaciones deliciosas de helado con coberturas gourmet.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-cookie"></i>
                </div>
                <h3>Complementos</h3>
                <p>Adicionales para personalizar tu experiencia: frutos secos, salsas, galletas.</p>
            </div>
        </div>
    </section>

    <!-- SUCURSALES SECTION -->
    <section class="sucursales" id="sucursales">
        <h2 class="section-title">Nuestras Sucursales</h2>
        <div class="sucursales-grid">
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> Centro - Huancayo</h3>
                <p><i class="fas fa-map-pin"></i> Jirón Real 425, Huancayo</p>
                <p><i class="fas fa-phone"></i> (064) 223-4567</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 10:00 AM - 10:00 PM</p>
            </div>
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> Plaza Vea</h3>
                <p><i class="fas fa-map-pin"></i> Av. Giráldez 580, Huancayo</p>
                <p><i class="fas fa-phone"></i> (064) 215-8910</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 10:00 AM - 9:30 PM</p>
            </div>
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> El Tambo</h3>
                <p><i class="fas fa-map-pin"></i> Av. Ferrocarril 302, El Tambo</p>
                <p><i class="fas fa-phone"></i> (064) 241-3256</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 11:00 AM - 9:00 PM</p>
            </div>
        </div>
    </section>

    <!-- PROMOCIONES SECTION -->
    <section class="promociones" id="promociones">
        <div class="promociones-container">
            <h2>Promociones Especiales</h2>
            <div class="promo-grid">
                <div class="promo-card">
                    <div class="discount">50%</div>
                    <h3>Segundos Helados</h3>
                    <p>Compra 2 helados y el segundo a mitad de precio</p>
                    <div class="promo-code">PROMO50</div>
                </div>
                <div class="promo-card">
                    <div class="discount">3x2</div>
                    <h3>Combo Triple</h3>
                    <p>Lleva 3 helados, paga solo 2. Válido de lunes a miércoles</p>
                    <div class="promo-code">TRIPLE22</div>
                </div>
                <div class="promo-card">
                    <div class="discount">S/5</div>
                    <h3>Paletas Premium</h3>
                    <p>Todas nuestras paletas gourmet a solo S/ 5 este mes</p>
                    <div class="promo-code">PALETAS5</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact" id="contacto">
        <h2>Contáctanos</h2>
        <p>Estamos listos para atender tus consultas y sugerencias</p>
        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h3>Teléfono</h3>
                <p>(064) 223-4567</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>info@concelato.com</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Ubicación</h3>
                <p>Jirón Real 425, Huancayo</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <h3>Horario</h3>
                <p>10:00 AM - 10:00 PM</p>
            </div>
        </div>
        <button class="btn-primary" onclick="window.location.href='login.php'">
            <i class="fas fa-shopping-cart"></i> Haz tu Pedido Ahora
        </button>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Concelato</h3>
                <p>Helados artesanales premium en Huancayo. Disfruta de los mejores sabores con ingredientes de calidad.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Sobre Nosotros</a>
                <a href="#productos">Productos</a>
                <a href="#sucursales">Sucursales</a>
                <a href="#promociones">Promociones</a>
            </div>
            <div class="footer-section">
                <h3>Productos</h3>
                <a href="#">Helados Artesanales</a>
                <a href="#">Paletas Gourmet</a>
                <a href="#">Postres Especiales</a>
                <a href="#">Complementos</a>
            </div>
            <div class="footer-section">
                <h3>Información</h3>
                <a href="#">Políticas de Privacidad</a>
                <a href="#">Términos de Servicio</a>
                <a href="#">Contacto</a>
                <a href="#">Ubicaciones</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Concelato Gelatería. Todos los derechos reservados. Desarrollado por MXTEC</p>
        </div>
    </footer>

    <script>
        // Smooth scroll para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concelato Gelatería - Helados Artesanales Premium</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --color-primary: #06b6d4;
            --color-secondary: #0891b2;
            --color-accent: #f97316;
            --color-dark: #1f2937;
            --color-light: #f9fafb;
            --color-white: #ffffff;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #f3f4f6 50%, #f9fafb 100%);
            color: var(--color-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ============================================
           HEADER / NAVBAR
           ============================================ */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--color-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .logo i {
            color: var(--color-accent);
        }

        .nav {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav a {
            text-decoration: none;
            color: var(--color-dark);
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .nav a:hover {
            background: rgba(6, 182, 202, 0.1);
            color: var(--color-primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 202, 0.3);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        .hero {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: clamp(3rem, 8vw, 6rem) 1.5rem;
            text-align: center;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .hero h1 {
            font-size: clamp(2.2rem, 6vw, 3.5rem);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero p {
            font-size: clamp(1rem, 2vw, 1.3rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 600px;
        }

        .hero-btns {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: white;
            color: var(--color-secondary);
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 1rem 2rem;
            border: 2px solid white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--color-secondary);
        }

        /* ============================================
           ABOUT SECTION
           ============================================ */
        .about {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .about-content h2 {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--color-dark);
        }

        .about-content p {
            font-size: 1.05rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .about-img {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border-radius: 20px;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: white;
        }

        /* ============================================
           PRODUCTS SECTION
           ============================================ */
        .products {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 3rem;
            color: var(--color-dark);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            border-color: var(--color-primary);
        }

        .product-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: white;
        }

        .product-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--color-dark);
        }

        .product-card p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* ============================================
           SUCURSALES SECTION
           ============================================ */
        .sucursales {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .sucursales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .sucursal-card {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .sucursal-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--color-primary);
        }

        .sucursal-card h3 {
            font-size: 1.3rem;
            color: var(--color-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sucursal-card p {
            margin-bottom: 0.8rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sucursal-card i {
            color: var(--color-primary);
            width: 20px;
        }

        /* ============================================
           PROMOCIONES SECTION
           ============================================ */
        .promociones {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 4rem 1.5rem;
            margin: 4rem 0;
        }

        .promociones-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .promociones h2 {
            text-align: center;
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 3rem;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .promo-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }

        .promo-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .promo-card .discount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .promo-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .promo-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .promo-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            word-break: break-word;
        }

        /* ============================================
           CONTACT SECTION
           ============================================ */
        .contact {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 1.5rem;
            text-align: center;
        }

        .contact h2 {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
        }

        .contact p {
            font-size: 1.05rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .contact-item {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .contact-item i {
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .contact-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .contact-item p {
            font-size: 0.95rem;
            margin: 0;
            word-break: break-word;
        }

        /* ============================================
           FOOTER
           ============================================ */
        footer {
            background: #1f2937;
            color: white;
            padding: 3rem 1.5rem 1rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: var(--color-primary);
        }

        .footer-section a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--color-primary);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s;
            margin: 0;
        }

        .social-links a:hover {
            background: var(--color-secondary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            text-align: center;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .header-container {
                flex-wrap: wrap;
            }

            .nav {
                order: 3;
                width: 100%;
                flex-direction: column;
                gap: 0.5rem;
            }

            .about {
                grid-template-columns: 1fr;
            }

            .about-img {
                min-height: 250px;
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 0.75rem 0;
            }

            .header-container {
                padding: 0 1rem;
            }

            .logo {
                font-size: 1.4rem;
            }

            .nav {
                gap: 1rem;
            }

            .nav a {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }

            .btn-login {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .hero-btns {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-ice-cream"></i> Concelato
            </div>
            <nav class="nav">
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#productos">Productos</a>
                <a href="#sucursales">Sucursales</a>
                <a href="#promociones">Promociones</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <a href="login.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Ingresar
            </a>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="inicio">
        <h1>Concelato Gelatería</h1>
        <p>Disfruta de los helados artesanales más deliciosos de Huancayo. Hecho con ingredientes premium y amor por el sabor.</p>
        <div class="hero-btns">
            <a href="registro.php" class="btn-primary">
                <i class="fas fa-user-plus"></i> Registrarse
            </a>
            <a href="#productos" class="btn-secondary">
                <i class="fas fa-eye"></i> Ver Productos
            </a>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about" id="nosotros">
        <div class="about-content">
            <h2>Sobre Concelato</h2>
            <p>Somos una heladería artesanal con más de 10 años de experiencia en Huancayo, comprometidos con la calidad y la satisfacción de nuestros clientes.</p>
            <p>Nuestros helados se elaboran diariamente con ingredientes premium, sin conservantes artificiales y con recetas exclusivas que te harán volver por más.</p>
            <p>Contamos con múltiples sucursales estratégicamente ubicadas para tu conveniencia, ofertas especiales y un sistema de pedidos en línea para que disfrutes desde casa.</p>
        </div>
        <div class="about-img">
            <i class="fas fa-award"></i>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products" id="productos">
        <h2 class="section-title">Nuestros Productos</h2>
        <div class="products-grid">
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-snowflake"></i>
                </div>
                <h3>Helados Artesanales</h3>
                <p>Sabores exclusivos preparados diariamente con ingredientes premium.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-lollipop"></i>
                </div>
                <h3>Paletas Gourmet</h3>
                <p>Refrescantes paletas con frutas naturales y presentación elegante.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-cake-candles"></i>
                </div>
                <h3>Postres Especiales</h3>
                <p>Combinaciones deliciosas de helado con coberturas gourmet.</p>
            </div>
            <div class="product-card">
                <div class="product-icon">
                    <i class="fas fa-cookie"></i>
                </div>
                <h3>Complementos</h3>
                <p>Adicionales para personalizar tu experiencia: frutos secos, salsas, galletas.</p>
            </div>
        </div>
    </section>

    <!-- SUCURSALES SECTION -->
    <section class="sucursales" id="sucursales">
        <h2 class="section-title">Nuestras Sucursales</h2>
        <div class="sucursales-grid">
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> Centro - Huancayo</h3>
                <p><i class="fas fa-map-pin"></i> Jirón Real 425, Huancayo</p>
                <p><i class="fas fa-phone"></i> (064) 223-4567</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 10:00 AM - 10:00 PM</p>
            </div>
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> Plaza Vea</h3>
                <p><i class="fas fa-map-pin"></i> Av. Giráldez 580, Huancayo</p>
                <p><i class="fas fa-phone"></i> (064) 215-8910</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 10:00 AM - 9:30 PM</p>
            </div>
            <div class="sucursal-card">
                <h3><i class="fas fa-map-marker-alt"></i> El Tambo</h3>
                <p><i class="fas fa-map-pin"></i> Av. Ferrocarril 302, El Tambo</p>
                <p><i class="fas fa-phone"></i> (064) 241-3256</p>
                <p><i class="fas fa-clock"></i> Lun-Dom: 11:00 AM - 9:00 PM</p>
            </div>
        </div>
    </section>

    <!-- PROMOCIONES SECTION -->
    <section class="promociones" id="promociones">
        <div class="promociones-container">
            <h2>Promociones Especiales</h2>
            <div class="promo-grid">
                <div class="promo-card">
                    <div class="discount">50%</div>
                    <h3>Segundos Helados</h3>
                    <p>Compra 2 helados y el segundo a mitad de precio</p>
                    <div class="promo-code">PROMO50</div>
                </div>
                <div class="promo-card">
                    <div class="discount">3x2</div>
                    <h3>Combo Triple</h3>
                    <p>Lleva 3 helados, paga solo 2. Válido de lunes a miércoles</p>
                    <div class="promo-code">TRIPLE22</div>
                </div>
                <div class="promo-card">
                    <div class="discount">S/5</div>
                    <h3>Paletas Premium</h3>
                    <p>Todas nuestras paletas gourmet a solo S/ 5 este mes</p>
                    <div class="promo-code">PALETAS5</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact" id="contacto">
        <h2>Contáctanos</h2>
        <p>Estamos listos para atender tus consultas y sugerencias</p>
        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h3>Teléfono</h3>
                <p>(064) 223-4567</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>info@concelato.com</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Ubicación</h3>
                <p>Jirón Real 425, Huancayo</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <h3>Horario</h3>
                <p>10:00 AM - 10:00 PM</p>
            </div>
        </div>
        <a href="login.php" class="btn-primary">
            <i class="fas fa-shopping-cart"></i> Haz tu Pedido Ahora
        </a>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Concelato</h3>
                <p>Helados artesanales premium en Huancayo. Disfruta de los mejores sabores con ingredientes de calidad.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Sobre Nosotros</a>
                <a href="#productos">Productos</a>
                <a href="#sucursales">Sucursales</a>
                <a href="#promociones">Promociones</a>
            </div>
            <div class="footer-section">
                <h3>Productos</h3>
                <a href="#">Helados Artesanales</a>
                <a href="#">Paletas Gourmet</a>
                <a href="#">Postres Especiales</a>
                <a href="#">Complementos</a>
            </div>
            <div class="footer-section">
                <h3>Información</h3>
                <a href="#">Políticas de Privacidad</a>
                <a href="#">Términos de Servicio</a>
                <a href="#">Contacto</a>
                <a href="#">Ubicaciones</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Concelato Gelatería. Todos los derechos reservados. Desarrollado por MXTEC</p>
        </div>
    </footer>

    <script>
        // Smooth scroll para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
