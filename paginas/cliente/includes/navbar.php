<?php
// Detectar página actual para marcar como activa
$current_page = basename($_SERVER['PHP_SELF']);
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
?>

<header class="cliente-header">
    <div class="header-content-cliente">
        <button class="menu-toggle-cliente" id="menu-toggle" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="cliente-nav">
            <i class="fas fa-bars"></i>
        </button>
        <div class="logo-cliente">
            <i class="fas fa-ice-cream"></i>
            <span>Concelato</span>
        </div>
        <nav id="cliente-nav" class="cliente-nav">
            <ul>
                <li><a href="index.php" <?php echo $current_page === 'index.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> <span>Inicio</span>
                </a></li>
                <li><a href="pedidos.php" <?php echo $current_page === 'pedidos.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-shopping-cart"></i> <span>Mis Pedidos</span>
                </a></li>
                <li><a href="estado_pedido.php" <?php echo $current_page === 'estado_pedido.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-truck"></i> <span>Estado Pedido</span>
                </a></li>
                <li><a href="invitado.php" <?php echo $current_page === 'invitado.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-ice-cream"></i> <span>Nuestros Sabores</span>
                </a></li>
            </ul>
        </nav>
        <?php if ($logueado): ?>
        <button class="logout-btn-cliente" onclick="cerrarSesion()">
            <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
        </button>
        <?php else: ?>
        <a href="../publico/login.php" class="btn-primary-cliente">
            <i class="fas fa-sign-in-alt"></i> <span>Iniciar Sesión</span>
        </a>
        <?php endif; ?>
    </div>
</header>

<script>
    // Toggle mobile menu
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menu-toggle');
        const nav = document.getElementById('cliente-nav');
        
        if (menuToggle && nav) {
            menuToggle.addEventListener('click', function() {
                nav.classList.toggle('active');
                menuToggle.setAttribute('aria-expanded', 
                    menuToggle.getAttribute('aria-expanded') === 'false' ? 'true' : 'false');
            });

            // Cerrar menú cuando se hace click en un enlace
            const navLinks = nav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    nav.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }
    });

    function cerrarSesion() {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = '../../conexion/cerrar_sesion.php';
        }
    }
</script>
