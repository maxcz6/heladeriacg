<?php
/**
 * HEADER ADMIN - Componente reutilizable
 * Uso: include('_header.php'); (después de <body>)
 * Asegúrate de pasar $current_page para marcar activo
 */

// Por defecto, marcar como Dashboard si no se especifica
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

// Mapeo de páginas a URLs
$pages = [
    'dashboard' => ['url' => 'index.php', 'icon' => 'fa-chart-line', 'label' => 'Dashboard'],
    'productos' => ['url' => 'productos.php', 'icon' => 'fa-box', 'label' => 'Productos'],
    'ventas' => ['url' => 'ventas.php', 'icon' => 'fa-shopping-cart', 'label' => 'Ventas'],
    'empleados' => ['url' => 'empleados.php', 'icon' => 'fa-users', 'label' => 'Empleados'],
    'clientes' => ['url' => 'clientes.php', 'icon' => 'fa-user-friends', 'label' => 'Clientes'],
    'proveedores' => ['url' => 'proveedores.php', 'icon' => 'fa-truck', 'label' => 'Proveedores'],
    'usuarios' => ['url' => 'usuarios.php', 'icon' => 'fa-user-cog', 'label' => 'Usuarios'],
    'promociones' => ['url' => 'promociones.php', 'icon' => 'fa-tag', 'label' => 'Promociones'],
    'sucursales' => ['url' => 'sucursales.php', 'icon' => 'fa-store', 'label' => 'Sucursales'],
    'configuracion' => ['url' => 'configuracion.php', 'icon' => 'fa-cog', 'label' => 'Configuración'],
];
?>

<!-- Header con navegación mejorada y responsiva -->
<header class="admin-header">
    <div>
        <button class="menu-toggle" aria-label="Alternar menú de navegación" aria-expanded="false" aria-controls="admin-nav">
            <i class="fas fa-bars"></i>
        </button>
        <div class="logo">
            <i class="fas fa-ice-cream"></i>
            <span>Concelato Admin</span>
        </div>
        <nav id="admin-nav">
            <?php foreach ($pages as $key => $page): ?>
            <a href="<?php echo $page['url']; ?>" <?php echo $key === $current_page ? 'class="active"' : ''; ?>>
                <i class="fas <?php echo $page['icon']; ?>"></i> 
                <span><?php echo $page['label']; ?></span>
            </a>
            <?php endforeach; ?>
            <a href="../../conexion/cerrar_sesion.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> 
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </div>
</header>
