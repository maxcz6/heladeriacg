# EJEMPLOS PRÁCTICOS - Usar el Sistema de Admin Mejorado

## 🎯 Ejemplo 1: Tabla con Búsqueda y Ordenamiento

### HTML:
```html
<main class="admin-container">
    <!-- Título y botón crear -->
    <div class="page-header">
        <h1>Gestión de Productos</h1>
        <button class="btn btn-primary" onclick="openModal('modal-crear')">
            + Crear Producto
        </button>
    </div>

    <!-- Card con búsqueda -->
    <div class="card">
        <div class="card-header">
            <h2>Productos</h2>
        </div>
        <div class="card-body">
            <!-- Input de búsqueda con data-filter-table -->
            <input 
                type="search" 
                id="buscar-productos"
                data-filter-table="tabla-productos"
                placeholder="Buscar por nombre, código..."
                aria-label="Buscar productos"
            >
        </div>
    </div>

    <!-- Card con tabla -->
    <div class="card">
        <div class="card-header">
            <h2>Listado de Productos</h2>
            <button class="btn-sm btn-secondary" data-action="export">
                📥 Exportar
            </button>
        </div>
        <div class="card-body">
            <table id="tabla-productos" class="tabla-admin">
                <thead>
                    <tr>
                        <!-- aria-sort="none" hace el header clickeable para sort -->
                        <th aria-sort="none">Nombre</th>
                        <th aria-sort="none">Precio</th>
                        <th aria-sort="none">Stock</th>
                        <th aria-sort="none">Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Helado Fresa</td>
                        <td>S/ 5.00</td>
                        <td>150</td>
                        <td>Helados</td>
                        <td>
                            <button class="btn-sm btn-secondary" onclick="editProduct(1)">
                                ✏️ Editar
                            </button>
                            <button class="btn-sm btn-danger" onclick="confirmDelete() && deleteProduct(1)">
                                🗑️ Eliminar
                            </button>
                        </td>
                    </tr>
                    <!-- Más filas aquí -->
                </tbody>
            </table>
        </div>
    </div>
</main>
```

### Funcionalidades que se activan automáticamente:
- ✅ Escribir en búsqueda filtra la tabla en tiempo real
- ✅ Click en header ordena ascendente/descendente
- ✅ Anuncios para lectores de pantalla
- ✅ Alt+S enfoca la búsqueda
- ✅ Alt+E exporta como CSV

---

## 🎯 Ejemplo 2: Formulario con Validación

### HTML:
```html
<!-- Modal o página con formulario -->
<div id="modal-editar" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Producto</h2>
            <button class="close" aria-label="Cerrar diálogo">&times;</button>
        </div>

        <form method="POST" action="guardar.php">
            <div class="modal-body">
                <!-- Campo de texto requerido -->
                <div class="form-group">
                    <label for="nombre" aria-required="true">
                        Nombre del Producto *
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre"
                        required
                        placeholder="Ej: Helado de Vainilla"
                    >
                    <!-- El error se muestra automáticamente si no cumple -->
                </div>

                <!-- Campo de email -->
                <div class="form-group">
                    <label for="email">Email de Proveedor</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        placeholder="contacto@proveedor.com"
                    >
                </div>

                <!-- Campo de teléfono -->
                <div class="form-group">
                    <label for="telefono">Teléfono de Contacto</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono"
                        placeholder="+51 987654321"
                    >
                </div>

                <!-- Campo numérico requerido -->
                <div class="form-group">
                    <label for="precio" aria-required="true">
                        Precio (S/.) *
                    </label>
                    <input 
                        type="number" 
                        id="precio" 
                        name="precio"
                        required
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                    >
                </div>

                <!-- Campo de stock -->
                <div class="form-group">
                    <label for="stock" aria-required="true">
                        Stock Disponible *
                    </label>
                    <input 
                        type="number" 
                        id="stock" 
                        name="stock"
                        required
                        min="0"
                        placeholder="0"
                    >
                </div>

                <!-- Textarea con descripción -->
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea 
                        id="descripcion" 
                        name="descripcion"
                        rows="4"
                        placeholder="Descripción del producto..."
                    ></textarea>
                </div>

                <!-- Select dropdown -->
                <div class="form-group">
                    <label for="categoria" aria-required="true">
                        Categoría *
                    </label>
                    <select id="categoria" name="categoria" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="helados">Helados</option>
                        <option value="paletas">Paletas</option>
                        <option value="postres">Postres</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button 
                    type="button" 
                    class="btn btn-secondary" 
                    onclick="closeModal('modal-editar')"
                >
                    Cancelar
                </button>
                <button 
                    type="submit" 
                    class="btn btn-primary"
                    aria-label="Guardar cambios del producto"
                >
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script para abrir modal -->
<button 
    class="btn btn-secondary"
    onclick="openModal('modal-editar')"
    data-modal="modal-editar"
>
    ✏️ Editar Producto
</button>
```

### Validación automática:
- ✅ Al perder foco, valida el campo
- ✅ Si hay error, se vuelve rojo
- ✅ Muestra mensaje de error específico
- ✅ Si es correcto, se vuelve verde
- ✅ Al enviar, valida TODO antes
- ✅ Anuncios para screen readers
- ✅ ESC cierra el modal
- ✅ Focus trap dentro del modal

---

## 🎯 Ejemplo 3: Notificaciones desde PHP

### PHP:
```php
<?php
// archivo: guardar.php

session_start();
include '../../conexion/conexion.php';
include '../../conexion/admin_functions.php';

// Validar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

try {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoria = trim($_POST['categoria'] ?? '');

    // Validar datos básicos
    if (empty($nombre) || $precio <= 0 || empty($categoria)) {
        throw new Exception('Datos incompletos o inválidos');
    }

    // Guardar en base de datos
    $stmt = $pdo->prepare("
        INSERT INTO productos (nombre, precio, stock, categoria, fecha_creacion)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$nombre, $precio, $stock, $categoria]);
    
    // ✅ ÉXITO - Mostrar notificación de éxito
    $mensaje = "Producto '{$nombre}' guardado correctamente";
    
    echo '<script>
        // El script.js está cargado, podemos usar showNotification()
        window.addEventListener("DOMContentLoaded", function() {
            showNotification("' . addslashes($mensaje) . '", "success", 4000);
        });
        // Redirigir después de 1 segundo
        setTimeout(function() {
            window.location.href = "productos.php";
        }, 1000);
    </script>';

} catch (Exception $e) {
    // ❌ ERROR - Mostrar notificación de error
    
    echo '<script>
        window.addEventListener("DOMContentLoaded", function() {
            showNotification("Error: ' . addslashes($e->getMessage()) . '", "error", 5000);
        });
    </script>';
    
    // Opcional: volver a mostrar el formulario
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardar Producto</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
</head>
<body>
    <main class="admin-container">
        <p>Procesando...</p>
    </main>
    <script src="../../js/admin/script.js"></script>
</body>
</html>
```

### JavaScript alternativo (en la misma página):
```javascript
// Si estás en la página de productos y quieres guardar con AJAX
async function saveProduct(formData) {
    try {
        const response = await fetch('guardar.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) throw new Error('Error en servidor');
        
        // ✅ Éxito
        showNotification('Producto guardado correctamente', 'success', 4000);
        
        // Recargar tabla o cerrar modal
        closeModal('modal-editar');
        location.reload();
        
    } catch (error) {
        // ❌ Error
        showNotification('Error al guardar: ' + error.message, 'error', 5000);
    }
}
```

---

## 🎯 Ejemplo 4: Confirmación de Eliminación

### HTML:
```html
<!-- Tabla con botón eliminar -->
<table>
    <tbody>
        <tr>
            <td>Helado Fresa</td>
            <td>
                <!-- Opción 1: Confirmación simple -->
                <a href="eliminar.php?id=1" class="btn-sm btn-danger"
                   onclick="return confirmDelete('¿Eliminar este producto?')">
                    🗑️ Eliminar
                </a>
                
                <!-- Opción 2: Modal de confirmación -->
                <button class="btn-sm btn-danger" 
                        onclick="openModal('modal-confirmar-1')">
                    🗑️ Eliminar
                </button>
            </td>
        </tr>
    </tbody>
</table>

<!-- Modal de confirmación -->
<div id="modal-confirmar-1" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2>Confirmar Eliminación</h2>
            <button class="close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro de que desea eliminar <strong>Helado Fresa</strong>?</p>
            <p class="text-muted">Esta acción no puede deshacerse.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-confirmar-1')">
                Cancelar
            </button>
            <button class="btn btn-danger" onclick="deleteProduct(1)">
                Sí, Eliminar
            </button>
        </div>
    </div>
</div>
```

### JavaScript para eliminar:
```javascript
function deleteProduct(productId) {
    // Mostrar indicador de carga
    const btn = event.target;
    btn.setAttribute('aria-busy', 'true');
    btn.disabled = true;
    
    fetch('eliminar.php?id=' + productId, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Producto eliminado correctamente', 'success');
            closeModal('modal-confirmar-' + productId);
            
            // Recargar tabla después de 1 segundo
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error al eliminar: ' + data.message, 'error');
            btn.removeAttribute('aria-busy');
            btn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('Error de conexión', 'error');
        btn.removeAttribute('aria-busy');
        btn.disabled = false;
    });
}
```

---

## 🎯 Ejemplo 5: Página Completa de Admin

### `paginas/admin/productos.php`:
```php
<?php
session_start();
// Validar permisos admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../../paginas/publico/login.php');
    exit;
}

// Obtener productos de BD
include '../../conexion/conexion.php';
$stmt = $pdo->query("
    SELECT id, nombre, precio, stock, categoria, fecha_creacion
    FROM productos
    ORDER BY nombre
");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Heladería CG</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
</head>
<body>

<!-- HEADER Y NAV -->
<header class="admin-header">
    <button class="menu-toggle" aria-label="Alternar menú de navegación">
        ☰
    </button>
    <nav id="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="productos.php" class="active">Productos</a>
        <a href="empleados.php">Empleados</a>
        <a href="clientes.php">Clientes</a>
        <a href="ventas.php">Ventas</a>
        <a href="../../conexion/cerrar_sesion.php">Salir</a>
    </nav>
</header>

<!-- MAIN CONTENT -->
<main class="admin-container">
    <!-- Título y botón crear -->
    <div class="page-header">
        <h1>Gestión de Productos</h1>
        <button class="btn btn-primary" 
                onclick="openModal('modal-crear')"
                data-action="create">
            + Crear Producto
        </button>
    </div>

    <!-- Búsqueda -->
    <div class="card">
        <div class="card-header">
            <h2>Búsqueda y Filtros</h2>
        </div>
        <div class="card-body">
            <input type="search" 
                   id="buscar"
                   data-filter-table="tabla-productos"
                   placeholder="Buscar por nombre, código, categoría..."
                   aria-label="Buscar productos">
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="card">
        <div class="card-header">
            <h2>Productos (<?php echo count($productos); ?>)</h2>
            <button class="btn-sm btn-secondary" 
                    data-action="export"
                    onclick="exportToCSV('tabla-productos', 'productos-export.csv')">
                📥 Exportar
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-productos" class="tabla-admin">
                    <thead>
                        <tr>
                            <th aria-sort="none">Nombre</th>
                            <th aria-sort="none">Precio</th>
                            <th aria-sort="none">Stock</th>
                            <th aria-sort="none">Categoría</th>
                            <th aria-sort="none">Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td>S/ <?php echo number_format($prod['precio'], 2); ?></td>
                            <td><?php echo $prod['stock']; ?> unidades</td>
                            <td><?php echo htmlspecialchars($prod['categoria']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($prod['fecha_creacion'])); ?></td>
                            <td class="cell-acciones">
                                <button class="btn-sm btn-secondary" 
                                        onclick="openModal('modal-editar-<?php echo $prod['id']; ?>')">
                                    ✏️ Editar
                                </button>
                                <button class="btn-sm btn-danger"
                                        onclick="openModal('modal-eliminar-<?php echo $prod['id']; ?>')">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- MODAL CREAR PRODUCTO -->
<div id="modal-crear" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Producto</h2>
            <button class="close" aria-label="Cerrar diálogo">&times;</button>
        </div>
        <form method="POST" action="../../conexion/admin_functions.php">
            <div class="modal-body">
                <div class="form-group">
                    <label for="nombre" aria-required="true">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="precio" aria-required="true">Precio (S/.) *</label>
                    <input type="number" id="precio" name="precio" required min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label for="stock" aria-required="true">Stock *</label>
                    <input type="number" id="stock" name="stock" required min="0">
                </div>
                <div class="form-group">
                    <label for="categoria" aria-required="true">Categoría *</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="helados">Helados</option>
                        <option value="paletas">Paletas</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" 
                        onclick="closeModal('modal-crear')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="../../js/admin/script.js"></script>

</body>
</html>
```

---

## 💡 CONSEJOS Y BUENAS PRÁCTICAS

### ✅ DO (Hacer)
```html
<!-- ✅ Usar atributos correctamente -->
<input type="email" id="email" required aria-required="true">
<input type="search" data-filter-table="tabla-id">
<button onclick="openModal('id')" data-modal="id">Abrir</button>

<!-- ✅ Estructura semántica -->
<form> ... </form>
<table> ... </table>
<section class="card"> ... </section>

<!-- ✅ Labels vinculados -->
<label for="nombre">Nombre:</label>
<input id="nombre">

<!-- ✅ ARIA cuando sea necesario -->
<button aria-expanded="false" aria-label="Alternar menú">☰</button>
<div role="dialog" aria-modal="true">...</div>
```

### ❌ DON'T (No hacer)
```html
<!-- ❌ Inputs sin label -->
<input type="text" placeholder="Nombre">

<!-- ❌ Buttons sin tipo -->
<div onclick="..." class="btn">Click</div>

<!-- ❌ Validación sólo CSS -->
<input pattern="\d+">

<!-- ❌ Colores sólo para indicar estado -->
<input style="border: 3px red;">

<!-- ❌ Modales con body scroll -->
<div class="modal" style="position: relative;">
```

---

**Estos ejemplos cubren 90% de casos de uso en un admin panel típico.** 🎉

Para más detalles, consulta `CAMBIOS_JAVASCRIPT_ADMIN.md` y `GUIA_INTEGRACION_SCRIPT_ADMIN.md`.
