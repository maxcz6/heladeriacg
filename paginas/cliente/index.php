<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/heladeriacg/conexion/sesion.php');
verificarSesion();
verificarRol('cliente');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Cliente - Concelato Gelateria</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/estilos_cliente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="client-container">
        <header class="client-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-ice-cream"></i>
                    Concelato Gelateria - Cliente
                </div>
                <nav>
                    <ul>
                        <li><a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Mis Pedidos</a></li>
                        <li><a href="estado_pedido.php"><i class="fas fa-truck"></i> Estado Pedido</a></li>
                        <li><a href="../../paginas/publico/index.php"><i class="fas fa-home"></i> Inicio</a></li>
                    </ul>
                </nav>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </header>

        <main class="client-main">
            <div class="welcome-section">
                <h1>¡Bienvenido a Concelato Gelateria!</h1>
                <p>Disfruta de nuestros deliciosos helados artesanales</p>
            </div>

            <div class="products-section">
                <h2>Nuestros Sabores</h2>
                
                <div class="category">
                    <h3>Sabores Clásicos</h3>
                    <div class="products-grid">
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Vainilla</h3>
                            <p class="description">Vainilla natural con esencia pura</p>
                            <p class="price">S/. 7.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Chocolate</h3>
                            <p class="description">Chocolate oscuro premium</p>
                            <p class="price">S/. 9.00</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Fresa</h3>
                            <p class="description">Helado artesanal de fresa con trozos de fruta</p>
                            <p class="price">S/. 8.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Nata</h3>
                            <p class="description">Crema de leche fresca con vainilla</p>
                            <p class="price">S/. 8.00</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="category">
                    <h3>Sabores Premium</h3>
                    <div class="products-grid">
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #a855f7, #8b5cf6);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Pistacho</h3>
                            <p class="description">Nueces tostadas de alta calidad</p>
                            <p class="price">S/. 12.00</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #a855f7, #8b5cf6);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Tiramisú</h3>
                            <p class="description">Café espresso y queso mascarpone</p>
                            <p class="price">S/. 11.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #a855f7, #8b5cf6);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Ferrero Rocher</h3>
                            <p class="description">Trozos de avellanas y chocolate</p>
                            <p class="price">S/. 13.00</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #a855f7, #8b5cf6);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Cheesecake</h3>
                            <p class="description">Sabor a tarta de queso con trozos de galleta</p>
                            <p class="price">S/. 10.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="category">
                    <h3>Sabores Frutales</h3>
                    <div class="products-grid">
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Mango</h3>
                            <p class="description">Pulpa de mango fresco</p>
                            <p class="price">S/. 8.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Limón</h3>
                            <p class="description">Zumo de limón fresco natural</p>
                            <p class="price">S/. 7.50</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Maracuyá</h3>
                            <p class="description">Pulpa de maracuyá orgánico</p>
                            <p class="price">S/. 9.00</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-ice-cream"></i>
                            </div>
                            <h3>Helado de Coco</h3>
                            <p class="description">Pasta de coco natural</p>
                            <p class="price">S/. 8.75</p>
                            <button class="order-btn" onclick="window.location.href='realizar_pedido.php'">
                                <i class="fas fa-shopping-cart"></i> Pedir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="promo-section">
                <h2>Promociones Actuales</h2>
                <div class="promo-cards">
                    <div class="promo-card">
                        <h3>2x1 en helados medianos</h3>
                        <p>Aplican sabores clásicos</p>
                        <p class="validity">Válido hasta fin de mes</p>
                    </div>
                    <div class="promo-card">
                        <h3>Tercer helado gratis</h3>
                        <p>En compras de 2 o más helados grandes</p>
                        <p class="validity">Válido toda la semana</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../../conexion/cerrar_sesion.php';
            }
        }
    </script>
</body>
</html>