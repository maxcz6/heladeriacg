<?php
// Determinar la página actual para la clase 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="admin-header">
    <div>
        <div>
            <div class="logo">
                <i class="fas fa-ice-cream"></i>
                <span>Concelato Admin</span>
            </div>
        </div>
        <nav id="admin-nav">
            <a href="/heladeriacg/paginas/admin/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
            <a href="/heladeriacg/paginas/admin/ventas.php" class="<?php echo $current_page == 'ventas.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
            </a>
            <a href="/heladeriacg/paginas/admin/productos.php" class="<?php echo $current_page == 'productos.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> <span>Productos</span>
            </a>
            <a href="/heladeriacg/paginas/admin/usuarios.php" class="<?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i> <span>Usuarios</span>
            </a>
            <a href="/heladeriacg/paginas/admin/clientes.php" class="<?php echo $current_page == 'clientes.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-friends"></i> <span>Clientes</span>
            </a>
            <a href="/heladeriacg/paginas/admin/empleados.php" class="<?php echo $current_page == 'empleados.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <span>Empleados</span>
            </a>
            <a href="/heladeriacg/paginas/admin/proveedores.php" class="<?php echo $current_page == 'proveedores.php' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> <span>Proveedores</span>
            </a>
            <a href="/heladeriacg/paginas/admin/reportes.php" class="<?php echo $current_page == 'reportes.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> <span>Reportes</span>
            </a>
            <a href="/heladeriacg/paginas/admin/promociones.php" class="<?php echo $current_page == 'promociones.php' ? 'active' : ''; ?>">
                <i class="fas fa-tag"></i> <span>Promociones</span>
            </a>
            <a href="/heladeriacg/paginas/admin/sucursales.php" class="<?php echo $current_page == 'sucursales.php' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i> <span>Sucursales</span>
            </a>
            <a href="/heladeriacg/paginas/admin/configuracion.php" class="<?php echo $current_page == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> <span>Configuración</span>
            </a>
            <a href="/heladeriacg/conexion/cerrar_sesion.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
            </a>
        </nav>
    </div>
</header>
