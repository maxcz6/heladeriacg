# Cambios en js/admin/script.js - Mejoras de Accesibilidad y Funcionalidad

## 📋 Resumen de Cambios

Se ha completamente reescrito y mejorado el archivo `js/admin/script.js` con enfoque en **accesibilidad WCAG 2.1 AA**, **validación de formularios en tiempo real**, y **gestión mejorada de modales y notificaciones**.

---

## ✨ Nuevas Características Implementadas

### 1. **Sistema de Utilidades (Utils)**
- **`debounce(fn, delay)`**: Evita múltiples llamadas a función durante escritura rápida
- **`throttle(fn, delay)`**: Limita frecuencia de ejecución de funciones
- **`announce(message, priority)`**: Anuncios para lectores de pantalla con rol "status" y aria-live

### 2. **MenuToggle - Navegación con Accesibilidad**
```javascript
MenuToggle.init()
```
**Atributos ARIA agregados:**
- `aria-expanded`: Indica si el menú está abierto/cerrado
- `aria-label`: "Alternar menú de navegación"
- `aria-controls`: Vinculado al elemento de navegación

**Funcionalidades:**
- ✅ Toggle al hacer clic en hamburguesa
- ✅ Cierre con ESC
- ✅ Cierre al hacer clic afuera
- ✅ Cierre automático al cambiar tamaño de ventana (>1024px)
- ✅ Anuncios para lectores de pantalla

---

### 3. **FormValidator - Validación en Tiempo Real**

**Métodos Disponibles:**
```javascript
FormValidator.init()           // Inicializa todos los formularios
FormValidator.setupForm(form)  // Configura un formulario específico
FormValidator.validateInput(input) // Valida un input individual
FormValidator.validateFormSubmit(form) // Valida todo el formulario
```

**Validadores Integrados:**
- `required`: Campo obligatorio (no vacío)
- `email`: Formato de correo válido
- `phone`: Número telefónico válido (mínimo 8 caracteres)
- `number`: Valor numérico válido
- `minLength/maxLength`: Longitud de texto

**Atributos ARIA Añadidos:**
- `aria-invalid`: Indica si el campo es inválido
- `aria-label`: Etiqueta alternativa si no existe `<label>`
- `aria-required`: Indica si el campo es obligatorio
- `role="alert"` en mensajes de error

**Estilos CSS Aplicados Automáticamente:**
- `.error`: Borde rojo, fondo rojo claro
- `.success`: Borde verde, fondo verde claro

---

### 4. **ModalManager - Gestión de Diálogos con Focus Trap**

**Métodos Disponibles:**
```javascript
ModalManager.init()           // Inicializa todos los modales
ModalManager.openModal(modalId) // Abre un modal
ModalManager.closeModal(modal)  // Cierra un modal
ModalManager.trapFocus(modal)   // Activa atrapamiento de foco
```

**Atributos ARIA Agregados:**
- `role="dialog"`: Identifica como diálogo modal
- `aria-modal="true"`: Indica que es un modal
- `aria-hidden`: Controla visibilidad para lectores de pantalla

**Funcionalidades:**
- ✅ Focus trap: El Tab nunca sale del modal
- ✅ Cierre con ESC
- ✅ Cierre al hacer clic afuera
- ✅ Focus automático al primer elemento editable
- ✅ Devolución de foco cuando se cierra

---

### 5. **TableManager - Búsqueda y Filtrado Accesible**

**Uso en HTML:**
```html
<input type="search" data-filter-table="tabla-id" placeholder="Buscar...">
<table id="tabla-id">...</table>
```

**O con JavaScript:**
```javascript
filterTable('inputId', 'tableId');
```

**Funcionalidades:**
- ✅ Búsqueda en tiempo real (debounced 300ms)
- ✅ Anuncios de resultados para lectores de pantalla
- ✅ `aria-label` automático en input de búsqueda

---

### 6. **TableSorter - Ordenamiento de Tablas Accesible**

**Uso en HTML:**
```html
<th aria-sort="none">Nombre</th>
<th aria-sort="none">Email</th>
```

**Funcionalidades:**
- ✅ Clic para ordenar ascendente
- ✅ Clic nuevamente para ordenar descendente
- ✅ Soporte de teclado (Enter/Espacio)
- ✅ Actualización de `aria-sort` attribute
- ✅ Anuncios de orden para lectores de pantalla

---

### 7. **NotificationManager - Sistema de Notificaciones Accesible**

**Uso:**
```javascript
showNotification('Operación completada', 'success', 4000);
showNotification('Error al guardar', 'error', 4000);
```

**Atributos ARIA:**
- `role="status"` para notificaciones de éxito
- `role="alert"` para notificaciones de error
- `aria-live="polite"` para éxito
- `aria-live="assertive"` para error
- `aria-atomic="true"` para anunciar mensaje completo

**Características:**
- ✅ Animación de entrada/salida suave
- ✅ Auto-desaparición configurable
- ✅ Múltiples notificaciones apiladas
- ✅ Anuncios a lectores de pantalla

---

### 8. **KeyboardShortcuts - Atajos de Teclado**

**Atajos Disponibles:**
- **Alt+S**: Enfoque en búsqueda
- **Alt+C**: Abre formulario de crear
- **Alt+E**: Exporta tabla

**Uso en HTML:**
```html
<input data-filter-table="tabla-id"> <!-- Alt+S -->
<button class="btn-primary" data-action="create">Crear</button> <!-- Alt+C -->
<button data-action="export">Exportar</button> <!-- Alt+E -->
```

---

## 🎨 Mejoras de Accesibilidad (WCAG 2.1 AA)

### Navegación por Teclado
- ✅ Tab navega por todos los elementos interactivos
- ✅ Shift+Tab navega hacia atrás
- ✅ Enter/Espacio activan botones
- ✅ ESC cierra modales
- ✅ Focus trap en modales

### Indicadores Visuales
- ✅ Outline azul en modo navegación por teclado (`body.keyboard-nav *:focus`)
- ✅ Offset de 2px para mejor visibilidad
- ✅ Contraste mínimo 4.5:1

### Screen Readers
- ✅ Atributos ARIA completos (`aria-expanded`, `aria-invalid`, `aria-live`, etc.)
- ✅ Labels vinculados a inputs
- ✅ Anuncios para cambios dinámicos
- ✅ Roles semánticos (`dialog`, `status`, `alert`, etc.)

### Móvil y Táctil
- ✅ Botones mínimo 44x44px (CSS: `min-height: 44px`, `min-width: 44px`)
- ✅ Espaciado adecuado entre elementos interactivos
- ✅ Sin hover-only controls

---

## 📊 Funciones Globales Disponibles

```javascript
// Menu
MenuToggle.init()

// Forms
FormValidator.init()
validateForm(formId)  // Compatibilidad con código anterior

// Modals
openModal(modalId)    // Abre un modal
closeModal(modalId)   // Cierra un modal

// Tables
filterTable(inputId, tableId)
TableManager.init()
TableSorter.init()

// Export
exportToCSV(tableId, filename)

// Notifications
showNotification(message, type, duration)

// Utilities
Utils.debounce(fn, delay)
Utils.throttle(fn, delay)
Utils.announce(message, priority)
```

---

## 🚀 Inicialización Automática

Todas las funciones se inicializan automáticamente al cargar el documento:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    MenuToggle.init();
    FormValidator.init();
    ModalManager.init();
    TableManager.init();
    TableSorter.init();
    KeyboardShortcuts.init();
    NotificationManager.init();
    // ... más setup
});
```

**No requiere configuración adicional si usas las clases CSS estándar.**

---

## 🔗 Compatibilidad con CSS

El script funciona optimamente con las clases CSS definidas en `css/admin/estilos_admin.css`:

- `.btn`, `.btn-primary`: Botones
- `.menu-toggle`: Hamburguesa
- `.modal`: Modales
- `.form-group`, `.form-error`: Validación de formularios
- `th[aria-sort]`: Headers de tabla sorteable
- `.mensaje`, `.mensaje.success`, `.mensaje.error`: Notificaciones
- `[data-filter-table]`: Input para búsqueda

---

## 📝 Ejemplo de Uso Completo

### HTML:
```html
<!-- Menú -->
<header class="admin-header">
    <button class="menu-toggle">☰</button>
    <nav id="admin-nav">...</nav>
</header>

<!-- Tabla con búsqueda y sort -->
<input type="search" data-filter-table="tabla-productos">
<table id="tabla-productos">
    <thead>
        <tr>
            <th aria-sort="none">Nombre</th>
            <th aria-sort="none">Precio</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Helado Vainilla</td>
            <td>$5</td>
        </tr>
    </tbody>
</table>

<!-- Formulario -->
<form>
    <input type="text" id="nombre" required>
    <input type="email" id="email" required>
    <button type="submit" class="btn-primary">Guardar</button>
</form>

<!-- Modal -->
<div id="modal-editar" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Contenido del modal...</p>
    </div>
</div>
```

### JavaScript:
```javascript
// Todo funciona automáticamente al cargar
// No necesita configuración adicional
```

---

## 🐛 Backward Compatibility

El script mantiene compatibilidad con código anterior:
- ✅ `openModal()` y `closeModal()` siguen funcionando
- ✅ `validateForm()` sigue disponible
- ✅ `filterTable()` sigue disponible
- ✅ `confirmDelete()` sigue disponible
- ✅ `exportToCSV()` sigue disponible
- ✅ `showNotification()` mejorada pero compatible

---

## 🎯 Próximos Pasos

1. **Integración**: Incluir este script en todas las páginas de admin
2. **Testing**: Probar con NVDA/JAWS y navegación por teclado
3. **Refinamiento**: Ajustar mensajes y textos según feedback
4. **Extensión**: Agregar más validadores o funcionalidades según necesidad

---

**Versión**: 2.0 (Mejorada con A11y)  
**Fecha**: 2024  
**Compatibilidad**: Modern Browsers (Chrome, Firefox, Safari, Edge)
