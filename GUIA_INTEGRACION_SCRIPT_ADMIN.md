# Guía de Integración: js/admin/script.js en Páginas Admin

## 📚 Cómo Integrar el Script Mejorado

### Paso 1: Incluir en el `<head>` de tu página PHP

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
    
    <!-- Tu CSS específico si necesitas -->
    <link rel="stylesheet" href="../../css/admin/productos.css">
</head>
<body>
```

### Paso 2: Estructura HTML Correcta

```html
<header class="admin-header">
    <button class="menu-toggle" aria-label="Alternar menú">☰</button>
    <nav id="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="productos.php" class="active">Productos</a>
        <a href="empleados.php">Empleados</a>
        <a href="clientes.php">Clientes</a>
    </nav>
</header>

<main class="admin-container">
    <!-- Contenido aquí -->
</main>
```

### Paso 3: Incluir el Script ANTES del cierre de `</body>`

```php
    <!-- Scripts -->
    <script src="../../js/admin/script.js"></script>
</body>
</html>
```

---

## 🎯 Patrones de Uso por Sección

### 1. BÚSQUEDA EN TABLAS

```html
<!-- Input con data-filter-table -->
<div class="form-group">
    <label for="buscar-productos">Buscar productos:</label>
    <input 
        type="search" 
        id="buscar-productos"
        data-filter-table="tabla-productos"
        placeholder="Nombre, código, etc..."
        aria-label="Buscar en tabla de productos"
    >
</div>

<!-- Tabla con ID coincidente -->
<table id="tabla-productos" class="tabla-admin">
    <thead>
        <tr>
            <th aria-sort="none">Nombre</th>
            <th aria-sort="none">Precio</th>
            <th aria-sort="none">Stock</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Helado Fresa</td>
            <td>$5.00</td>
            <td>150</td>
            <td>
                <button class="btn-sm btn-secondary">Editar</button>
                <button class="btn-sm btn-danger">Eliminar</button>
            </td>
        </tr>
    </tbody>
</table>
```

**Lo que sucede automáticamente:**
- ✅ Se detecta `data-filter-table`
- ✅ Se busca en la tabla mientras escribes
- ✅ Se anuncia cantidad de resultados

---

### 2. ORDENAMIENTO DE TABLAS

```html
<table id="tabla-ventas">
    <thead>
        <tr>
            <!-- aria-sort proporciona soporte de ordenamiento -->
            <th aria-sort="none">Fecha</th>
            <th aria-sort="none">Cliente</th>
            <th aria-sort="none">Monto</th>
            <th aria-sort="none">Estado</th>
        </tr>
    </thead>
    <tbody>
        <!-- contenido -->
    </tbody>
</table>
```

**Interacción:**
- 🖱️ Click en header → ordena ascendente
- 🖱️ Click nuevamente → ordena descendente
- ⌨️ Tab + Enter/Espacio → mismo efecto

---

### 3. VALIDACIÓN DE FORMULARIOS

```html
<form method="POST">
    <!-- Campo requerido -->
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
    </div>

    <!-- Email validado -->
    <div class="form-group">
        <label for="email">Email de Contacto</label>
        <input 
            type="email" 
            id="email" 
            name="email"
            placeholder="contacto@ejemplo.com"
        >
    </div>

    <!-- Teléfono validado -->
    <div class="form-group">
        <label for="telefono">Teléfono</label>
        <input 
            type="tel" 
            id="telefono" 
            name="telefono"
            placeholder="+51 987654321"
        >
    </div>

    <!-- Número validado -->
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
        >
    </div>

    <!-- Textarea requerido -->
    <div class="form-group">
        <label for="descripcion" aria-required="true">
            Descripción *
        </label>
        <textarea 
            id="descripcion" 
            name="descripcion"
            required
            rows="4"
        ></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        Guardar Producto
    </button>
</form>
```

**Lo que sucede automáticamente:**
- ✅ Validación en tiempo real (al perder foco)
- ✅ Estilos de error (rojo) y éxito (verde)
- ✅ Mensajes de error automáticos
- ✅ Anuncio para lectores de pantalla
- ✅ Prevención de envío si hay errores

---

### 4. MODALES/DIÁLOGOS

```html
<!-- Botón para abrir modal -->
<button 
    class="btn btn-secondary" 
    onclick="openModal('modal-confirmar')"
    data-modal="modal-confirmar"
>
    Eliminar Producto
</button>

<!-- Modal -->
<div id="modal-confirmar" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmar Eliminación</h2>
            <button class="close" aria-label="Cerrar diálogo">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro de que desea eliminar este producto?</p>
            <p class="text-muted">Esta acción no puede deshacerse.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-confirmar')">
                Cancelar
            </button>
            <button class="btn btn-danger" onclick="deleteProduct()">
                Eliminar
            </button>
        </div>
    </div>
</div>
```

**Funcionalidades:**
- ✅ ESC cierra el modal
- ✅ Click afuera cierra
- ✅ Focus trap (Tab no sale)
- ✅ Focus automático en primer input
- ✅ Anuncios para screen readers

---

### 5. NOTIFICACIONES

```php
<?php
// En tu PHP después de una operación
if ($producto_guardado) {
    echo '<script>showNotification("Producto guardado correctamente", "success", 4000);</script>';
} else {
    echo '<script>showNotification("Error al guardar producto", "error", 4000);</script>';
}
?>
```

**O desde JavaScript:**
```javascript
// Éxito
showNotification('15 productos exportados', 'success');

// Error
showNotification('No se pudo conectar con la base de datos', 'error');

// Información
showNotification('Procesando datos...', 'info');

// Advertencia
showNotification('Esta operación podría tardar', 'warning');
```

---

### 6. ATAJOS DE TECLADO

Los usuarios pueden usar:
- **Alt+S**: Enfoque en búsqueda
- **Alt+C**: Abre formulario de crear
- **Alt+E**: Exporta tabla

Asegúrate de usar los atributos correctos:
```html
<!-- Para búsqueda -->
<input type="search" data-filter-table="tabla-id">

<!-- Para crear -->
<button class="btn-primary" data-action="create">Crear</button>

<!-- Para exportar -->
<button data-action="export">Exportar</button>
```

---

## 🔧 Ejemplo Completo: Página de Productos

```php
<?php
session_start();
// Validar sesión admin
include '../../conexion/sesion.php';

// Obtener productos
$stmt = $pdo->query("SELECT * FROM productos ORDER BY nombre");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Heladería CG</title>
    <link rel="stylesheet" href="../../css/admin/estilos_admin.css">
</head>
<body>
    <header class="admin-header">
        <button class="menu-toggle" aria-label="Alternar menú">☰</button>
        <nav id="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="productos.php" class="active">Productos</a>
            <a href="empleados.php">Empleados</a>
            <a href="clientes.php">Clientes</a>
            <a href="../../conexion/cerrar_sesion.php">Salir</a>
        </nav>
    </header>

    <main class="admin-container">
        <div class="page-header">
            <h1>Gestión de Productos</h1>
            <button 
                class="btn btn-primary" 
                onclick="openModal('modal-crear')"
                data-action="create"
            >
                + Crear Producto
            </button>
        </div>

        <!-- Búsqueda -->
        <div class="card">
            <div class="card-header">
                <h2>Búsqueda y Filtros</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="buscar">Buscar Producto:</label>
                    <input 
                        type="search"
                        id="buscar"
                        data-filter-table="tabla-productos"
                        placeholder="Nombre, código, etc..."
                    >
                </div>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="card">
            <div class="card-header">
                <h2>Productos (<?php echo count($productos); ?>)</h2>
                <button class="btn-sm btn-secondary" data-action="export">
                    📥 Exportar
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-productos" class="tabla-admin">
                        <thead>
                            <tr>
                                <th aria-sort="none">Nombre</th>
                                <th aria-sort="none">Categoría</th>
                                <th aria-sort="none">Precio</th>
                                <th aria-sort="none">Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                                <td>S/ <?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo $producto['stock']; ?> unidades</td>
                                <td>
                                    <button 
                                        class="btn-sm btn-secondary"
                                        onclick="openModal('modal-editar-<?php echo $producto['id']; ?>')"
                                    >
                                        Editar
                                    </button>
                                    <button 
                                        class="btn-sm btn-danger"
                                        onclick="if(confirmDelete()) { deleteProduct(<?php echo $producto['id']; ?>); }"
                                    >
                                        Eliminar
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

    <!-- Modal Crear/Editar -->
    <div id="modal-crear" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Producto</h2>
                <button class="close" aria-label="Cerrar">&times;</button>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-crear')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script -->
    <script src="../../js/admin/script.js"></script>
</body>
</html>
```

---

## ✅ Checklist de Integración

- [ ] Script incluido antes de `</body>`
- [ ] CSS estilos_admin.css incluido
- [ ] Estructura HTML semántica (header, nav, main)
- [ ] Botones tienen clases `.btn`, `.btn-primary`, etc.
- [ ] Inputs búsqueda tienen `data-filter-table`
- [ ] Tablas tienen `id` y `aria-sort` en headers
- [ ] Formularios tienen estructura `.form-group`
- [ ] Modales tienen clase `.modal` y `id`
- [ ] Botones modales usan `onclick="openModal()"` / `closeModal()`

---

## 🐛 Troubleshooting

**Problema**: Las búsquedas no funcionan
- Solución: Verifica que el `data-filter-table` coincida con el `id` de la tabla

**Problema**: El ordenamiento no funciona
- Solución: Asegúrate de que los headers `<th>` tengan `aria-sort="none"`

**Problema**: Los formularios no validan
- Solución: Usa `required` en inputs y estructura `.form-group` correcta

**Problema**: Los modales no cierran con ESC
- Solución: Verifica que el modal tenga `id` y que `closeModal()` reciba el ID correcto

**Problema**: Notificaciones no aparecen
- Solución: Asegúrate de que `showNotification()` se llame DESPUÉS de `DOMContentLoaded`

---

**¿Preguntas?** Revisa `CAMBIOS_JAVASCRIPT_ADMIN.md` para documentación detallada.
