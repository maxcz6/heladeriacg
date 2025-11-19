# Recomendaciones UX/UI - Sistema Admin Concelato Gelatería

## 1. ESTRUCTURA GENERAL DEL SISTEMA

### Arquitectura de Información
- **Jerarquía clara**: Panel > Secciones > Elementos
- **Principio de progresión**: De general a específico, de resumen a detalle
- **Agrupación lógica**: Agrupar operaciones relacionadas (CRUD, reportes, configuración)
- **Entrada única de datos**: Un campo para cada tipo de información
- **Búsqueda y filtrado prominentes**: Accesibles sin desplazamiento
- **Consistencia terminológica**: Usar los mismos términos para las mismas acciones

### Patrones de Navegación
- **Sidebar persistente** (1200px+): Acceso rápido a todas las secciones
- **Navbar superior colapsable** (responsiva): Hamburguesa en móvil/tablet
- **Migas de pan**: Mostrar ubicación actual en la jerarquía
- **Navegación flotante en móvil**: Tabs o botones de acceso rápido
- **Breadcrumbs dinámicos**: Ejemplo: "Admin > Productos > Editar ID:123"

---

## 2. DISTRIBUCIÓN DE ELEMENTOS

### Layout Base (Grid System)
```
┌─────────────────────────────────────────┐
│         HEADER / NAVBAR SUPERIOR        │
├──────────────┬──────────────────────────┤
│   SIDEBAR    │    CONTENIDO PRINCIPAL   │
│  (Fijo o     │  (Flexible, scrollable)  │
│  colapsable) │                          │
└──────────────┴──────────────────────────┘
```

### Distribución de Contenido
- **Header**: Logo + navegación + búsqueda global + perfil usuario
- **Sidebar**: Menú principal (max 6-8 items principales)
- **Contenido**: 
  - Barra de acciones (filtros, búsqueda local, crear nuevo)
  - Tabla/Grid de datos
  - Modales para operaciones (sin dejar la página)
- **Footer**: Copyright, enlaces legales, estado del sistema

### Espaciado (Escala de 8px)
- **Micro**: 4px (separación entre elementos muy próximos)
- **Pequeño**: 8px (separación entre grupos pequeños)
- **Medio**: 16px (separación entre secciones)
- **Grande**: 24px (separación entre bloques principales)
- **Macro**: 32px+ (separación entre áreas grandes)

---

## 3. NAVEGACIÓN INTERNA

### Sidebar - Estructura Jerárquica
```
MENÚ PRINCIPAL (Nivel 1)
├─ Dashboard (con ícono, sin submenu)
├─ Gestión (expandible)
│  ├─ Productos
│  ├─ Clientes
│  ├─ Empleados
│  └─ Proveedores
├─ Ventas (expandible)
│  ├─ Registro de Ventas
│  ├─ Reportes
│  └─ Promociones
├─ Inventario (expandible)
│  ├─ Stock
│  └─ Operaciones
├─ Sucursales (sin submenu)
├─ Configuración (expandible)
│  ├─ Sistema
│  ├─ Usuarios
│  └─ Respaldos
└─ Ayuda/Docs
```

### Estados de Navegación
- **Activo**: Resalte visual claro, indicador de sección actual
- **Hover**: Cambio sutil de fondo, sin transiciones abruptas
- **Expandido/Colapsado**: Ícono animado indicando estado (chevron rotativo)
- **Indicadores visuales**: Punto/badge para notificaciones o cambios pendientes

### Navegación Móvil (< 768px)
- **Hamburguesa menu**: Abre sidebar como overlay
- **Cerrar al navegar**: Sidebar se cierra automáticamente tras seleccionar opción
- **Gestos de swipe**: Deslizar izquierda para abrir/cerrar (opcional)
- **Tabs flotantes**: Acceso rápido a secciones principales (max 4-5)

---

## 4. TIPOGRAFÍA Y JERARQUÍA VISUAL

### Escala Tipográfica (Sistema modular 1.2x)
```
H1: 32px (títulos de página)
H2: 26px (títulos de sección)
H3: 21px (subtítulos, tarjetas principales)
H4: 18px (encabezados de tabla, etiquetas importantes)
Body: 16px (texto base)
Small: 14px (texto secundario, metadatos)
Tiny: 12px (ayuda, notas, timestamps)
```

### Pesos de Fuente
- **700 (Bold)**: Títulos, encabezados de tabla, botones, etiquetas críticas
- **600 (Semibold)**: Subtítulos, negritas en párrafos, labels de formularios
- **400 (Regular)**: Texto base, párrafos, descripciones
- **300 (Light)**: Texto muy secundario, watermarks, decorativo

### Línea Base y Altura de Línea
- **Títulos**: line-height 1.2 (compacto)
- **Body**: line-height 1.5-1.6 (legibilidad)
- **Pequeño texto**: line-height 1.4 (compacto)

### Contraste y Legibilidad
- **Mínimo WCAG AA**: 4.5:1 para texto normal
- **Uso de negrita**: Para palabras clave, números importantes
- **Límite de caracteres**: 60-80 caracteres por línea en desktop
- **Justificación**: Left-aligned siempre (evitar justificado)

---

## 5. COMPONENTES BASE

### Botones
```
ESTADOS:
├─ Default: Fondo sólido, sin sombra
├─ Hover: Elevación sutil (1-2px), opacidad +10%
├─ Active: Opacidad +20%, sombra interior opcional
├─ Disabled: Opacidad 50%, cursor no-allowed
└─ Loading: Ícono spinner, texto deshabilitado

TAMAÑOS:
├─ Large (44px): Acciones críticas, móvil
├─ Medium (40px): Uso general, default
└─ Small (32px): Acciones secundarias, tablas

VARIANTES:
├─ Primary (fill): Acciones principales, CTA
├─ Secondary (outline): Acciones alternativas
├─ Tertiary (text): Acciones menores
└─ Danger (fill red): Eliminar, cancelar operaciones
```

### Formularios
```
ESTRUCTURA:
├─ Label (obligatorio para A11y)
│  └─ (Asterisco rojo para requerido, no solo indicación visual)
├─ Input/Select/Textarea
│  ├─ Border 1px (no fondo gris opaco)
│  ├─ Padding: 12px 16px
│  ├─ Border-radius: 6-8px
│  └─ Focus: Outline 2px offset 2px
├─ Helper text (si aplica)
│  └─ Tamaño pequeño, color neutral
└─ Error message (si hay validación)
   └─ Ícono + texto, color error

VALIDACIÓN EN TIEMPO REAL:
├─ No mostrar errores hasta blur o submit
├─ Indicador visual sutil (icono a la derecha)
├─ Mensaje claro y actionable
├─ Sin bloquear el formulario (submit habilitado)

AGRUPACIÓN:
├─ Fieldset para grupos relacionados
├─ Legend como título del grupo
└─ Max 2 columnas en desktop, 1 en móvil
```

### Tarjetas (Cards)
```
ESTRUCTURA:
├─ Header (opcional)
│  ├─ Título / Ícono
│  └─ Acciones (3 dots menu, cerrar)
├─ Content
│  ├─ Contenido principal
│  └─ Sin padding interno adicional (hereda del card)
├─ Footer (opcional)
│  └─ Botones de acción, links
└─ Dividers entre secciones (línea sutil)

ESTILOS:
├─ Border 1px (no sombra pesada)
├─ Border-radius: 8-12px
├─ Padding: 16-20px
├─ Transición hover: elevación sutil, border color

USOS:
├─ Stats/KPIs
├─ Resumen de datos
├─ Contenedores de formularios
└─ Listados de elementos
```

### Tablas
```
ESTRUCTURA:
├─ Encabezado
│  ├─ Alineamiento derecha para números
│  ├─ Bold weight (600-700)
│  └─ Altura 44px (mínima, para consistencia con botones)
├─ Filas
│  ├─ Altura 40-48px
│  ├─ Hover: fondo muy sutil (opacidad 3-5%)
│  └─ Striped: alternancia de filas (opcional, si muy larga)
├─ Checkbox/Acciones
│  └─ Columna pinned izquierda o derecha
└─ Responsive
   ├─ Scroll horizontal (< 768px)
   ├─ Stack mode (convertir a filas, una por item)
   └─ Collapse: ocultar columnas no críticas

INTERACCIÓN:
├─ Click en fila: abre detalle o editar
├─ Checkbox: seleccionar múltiples para acciones batch
└─ Ordenamiento: click en encabezado (ASC/DESC visual)

PAGINACIÓN:
├─ Fondo sutil
├─ Números centrados
├─ Prev/Next siempre visible
├─ Info: "Mostrando X-Y de Z resultados"
└─ Tamaño de página: Select arriba a la derecha
```

### Modales
```
ESTRUCTURA:
├─ Overlay (fondo oscuro translúcido, cubre viewport)
├─ Modal Box
│  ├─ Header
│  │  ├─ Título
│  │  └─ Botón cerrar (X)
│  ├─ Body
│  │  └─ Contenido scrollable si necesario
│  └─ Footer
│     ├─ Botón Cancelar (Secondary)
│     └─ Botón Confirmar (Primary/Danger según acción)
└─ Animación
   ├─ Entrada: Fade + Slide up (200ms)
   └─ Salida: Reverse (150ms)

TAMAÑOS:
├─ Small (400px): Confirmaciones, alertas
├─ Medium (600px): Formularios normales
└─ Large (900px): Tablas, contenido complejo

COMPORTAMIENTO:
├─ Click fuera = cerrar (si no hay datos unsaved)
├─ ESC = cerrar
├─ Focus trap (accesibilidad)
└─ No permitir interacción con contenido detrás
```

### Alertas / Notificaciones
```
ESTADOS:
├─ Success: ✓ Operación completada
├─ Error: ✗ Algo salió mal
├─ Warning: ⚠ Atención requerida
└─ Info: ℹ Información útil

UBICACIÓN:
├─ Toast (esquina superior derecha): Temporal, auto-dismiss
├─ Inline (dentro del contenido): Permanente hasta cerrar
├─ Banner (arriba de página): Crítica, no dismissible

ESTRUCTURA:
├─ Ícono (opcional pero recomendado)
├─ Título (si mensaje largo)
├─ Texto descriptivo
├─ Botón cerrar (X)
└─ Botón de acción (opcional)

DURACIÓN:
├─ Auto-dismiss: 4-5 segundos (si no es error)
├─ Manual dismiss: Errores y advertencias
└─ Persistente: Información crítica
```

### Badges / Tags
```
USOS:
├─ Estado (Active/Inactive, Pending, etc)
├─ Categoría o tipo
├─ Prioridad
└─ Conteo

VARIANTES:
├─ Filled: Para estados primarios
├─ Outline: Para estados secundarios
└─ Subtle: Para información menos importante

TAMAÑO:
├─ Pequeño: 20-24px altura (por defecto)
├─ Grande: 28-32px altura (si agrupado con texto grande)

POSICIÓN:
├─ Derecha del elemento (por defecto)
├─ Esquina superior derecha (en tarjetas)
└─ Inline con el texto (en tablas)
```

---

## 6. EXPERIENCIA EN DIFERENTES DISPOSITIVOS

### Desktop (1200px+)
- **Sidebar persistente**: Ancho 240-280px
- **Contenido**: Fluido, máximo 1400px
- **Grid**: 3-4 columnas para tarjetas
- **Tablas**: Todas las columnas visibles
- **Modales**: Centrados, máximo ancho 900px
- **Hover effects**: Completamente funcionales
- **Tipografía**: Tamaños completos

### Tablet (768px - 1199px)
- **Sidebar**: Colapsable (solo ícono visible)
- **Contenido**: Ancho completo - sidebar colapsado
- **Grid**: 2 columnas para tarjetas
- **Tablas**: Scroll horizontal o stack mode
- **Modales**: Ancho 80% de viewport
- **Touch targets**: Mínimo 44x44px
- **Tipografía**: Reducción gradual (95% de desktop)

### Móvil (< 768px)
- **Sidebar**: Overlay (hamburger menu)
- **Contenido**: Ancho completo, padding 12-16px
- **Grid**: 1 columna (full width)
- **Tablas**: Stack mode (filas apiladas)
- **Modales**: Full width - 12px padding
- **Botones**: 44x44px mínimo, ancho completo si posible
- **Tipografía**: 90% de desktop, line-height aumentada
- **Espaciado**: Más generoso (24px entre secciones)

### Breakpoints Recomendados
```
Mobile:    0 - 479px
Tablet:    480px - 767px
Desktop:   768px - 1199px
Wide:      1200px+
```

---

## 7. RECOMENDACIONES DE RENDIMIENTO Y FLUIDEZ

### Optimización de Carga
- **CSS crítico inline**: Header y estilos base en `<head>`
- **Defer JS no-crítico**: Scripts al final, async para analytics
- **Lazy loading**: Imágenes y contenido fuera del viewport
- **Bundle optimization**: Separar CSS por ruta (admin.css, public.css)
- **Minificación**: Todos los archivos en producción

### Animaciones y Transiciones
- **Velocidad base**: 200ms (transiciones normales)
- **Rápidas**: 100ms (microinteracciones)
- **Lentas**: 300ms (movimientos grandes)
- **Easing**: ease-out para entrada, ease-in para salida
- **GPU acceleration**: transform y opacity solamente
- **Evitar**: Animaciones que bloqueen interacción

### Micro-interacciones
- **Hover**: 100ms, cambio de opacidad/transform
- **Click/Press**: Feedback visual inmediato (200ms)
- **Carga**: Skeleton screens o spinners discretos
- **Transición de vistas**: Fade (200ms) o slide (300ms)
- **Validación**: Ícono o color sutil, sin animación agresiva

### Lazy Loading y Virtual Scrolling
- **Tablas largas**: Implementar virtual scrolling (500+ filas)
- **Imágenes**: Lazy load si existen
- **Infinite scroll**: Evitar, usar paginación explícita
- **Contenido desplegable**: Lazy load detalles al expandir

---

## 8. ACCESIBILIDAD (WCAG 2.1 AA)

### Estructura Semántica
- **Headings jerárquicos**: H1 → H2 → H3 (sin saltos)
- **Landmarks**: `<header>`, `<nav>`, `<main>`, `<aside>`, `<footer>`
- **Botones reales**: `<button>` tag, no divs con onClick
- **Links vs Botones**: `<a>` para navegación, `<button>` para acciones
- **Listas**: `<ul>`, `<ol>`, `<li>` para agrupaciones lógicas
- **Tablas**: `<thead>`, `<tbody>`, `scope="col"` en `<th>`

### Formularios Accesibles
- **Label + Input**: Asociar con `for="id"` o `<label><input></label>`
- **Required**: Atributo HTML `required`, no solo visual
- **Validación**: Aria-invalid, aria-describedby para errores
- **Fieldset**: Agrupar inputs relacionados con `<fieldset>` y `<legend>`
- **Placeholder**: No reemplaza label, es pista solamente

### Navegación por Teclado
- **Tab order**: Lógico, de arriba a abajo, izquierda a derecha
- **Focusable elements**: min-height 44px, outline visible
- **Skip links**: "Saltar al contenido principal" (opcional pero recomendado)
- **Atajos**: Alt+S (Search), Alt+C (Create) - documentar
- **Modales**: Focus trap, volver al triggerer al cerrar

### Contraste y Color
- **Ratio mínimo**: 4.5:1 texto, 3:1 elementos gráficos
- **No depender solo de color**: Usar también ícono, patrón o texto
- **Focus indicator**: Outline claro, no removido completamente
- **Modo de alto contraste**: Funciona con system preferences

### Iconografía y Imágenes
- **Ícono + Texto**: En botones, siempre acompañados
- **Ícono solo**: Incluir `aria-label` o `title`
- **SVG**: Incluir `<title>` dentro del SVG
- **Imágenes decorativas**: `alt=""` (vacío)
- **Imágenes informativas**: `alt="descripción clara"`

### Lectores de Pantalla
- **Skip navigation**: Primer link de la página (oculto pero accesible)
- **ARIA landmarks**: role="navigation", role="main", etc.
- **Aria-label**: Para elementos sin texto visible
- **Aria-live**: Para actualizaciones dinámicas (tablas, notificaciones)
- **Aria-describedby**: Asociar descripciones adicionales
- **Alt text**: Todos los elementos no-decorativos

---

## 9. MINIMALISMO Y DISEÑO MODULAR

### Principios de Minimalismo
- **Eliminar visual noise**: Solo elementos funcionales
- **Espaciado generoso**: Respiración visual entre elementos
- **Una acción clara por pantalla**: CTA principal obviamente destacada
- **Ocultar complejidad**: Detalles en modales, no en página principal
- **Información progresiva**: Mostrar más detalles al expandir/hacer hover
- **Consistencia visual**: Mismos componentes reutilizables

### Diseño Modular
- **Componentes reutilizables**:
  - Button (primario, secundario, peligro, tamaños)
  - Card (variantes: stats, form, list)
  - Input (text, number, select, checkbox, radio)
  - Badge (estados, colores)
  - Modal (sizes, tipos)
  - Alert (tipos, posiciones)
  - Table (con soreo, paginación)
  - Sidebar + Navbar
  
- **CSS classes**: Convención BEM o similar
  ```
  .component-name { }
  .component-name__element { }
  .component-name__element--modifier { }
  ```

- **Reutilización**: Evitar duplicar estilos, usar clases base + modificadores

### Escalabilidad
- **Sistema de variables CSS**: Colores, tamaños, espaciado (custom properties)
- **Utilities**: Clases para casos puntuales (margin, padding, display)
- **Breakpoints variables**: Media queries reutilizables
- **Tipografía escalable**: Usar clamp() para responsividad fluida
- **Flexibilidad**: Componentes que funcionen en diferentes contextos

---

## 10. RECOMENDACIONES DE IMPLEMENTACIÓN ESPECÍFICAS

### Estructura de Carpetas CSS
```
css/
├─ base/
│  ├─ reset.css (normalización)
│  ├─ typography.css (fuentes, tamaños)
│  └─ colors.css (variables de color)
├─ components/
│  ├─ buttons.css
│  ├─ forms.css
│  ├─ tables.css
│  ├─ modals.css
│  ├─ cards.css
│  └─ alerts.css
├─ layout/
│  ├─ header.css
│  ├─ sidebar.css
│  ├─ grid.css
│  └─ responsive.css
└─ pages/
   ├─ admin.css
   ├─ productos.css
   └─ [página].css
```

### Prioridades de Implementación
1. **Header + Sidebar** (navegación estable)
2. **Componentes base** (buttons, forms, tables)
3. **Páginas principales** (dashboard, listados)
4. **Modales y formularios** (CRUD operations)
5. **Refinamientos visuales** (microinteracciones)
6. **Responsive** (media queries y mobile)

### Testing
- **Navegación**: Tab key en todas las páginas
- **Zoom**: 200% en browser, aún usable
- **Responsividad**: 320px, 480px, 768px, 1200px
- **Velocidad**: Lighthouse score 80+
- **Accesibilidad**: Axe DevTools sin warnings críticos

---

## RESUMEN: Enfoque Aplicable

Este sistema admin debe:
1. ✓ Ser **rápido** en carga inicial (JS mínimo, CSS crítico)
2. ✓ Ser **fluido** en interacción (transiciones 200-300ms, GPU acceleration)
3. ✓ Ser **claro** en información (jerarquía visual, espaciado)
4. ✓ Ser **accesible** a todos (keyboard, screen readers, contrast)
5. ✓ Ser **responsive** a cualquier dispositivo (mobile-first)
6. ✓ Ser **consistente** en toda la aplicación (componentes reutilizables)
7. ✓ Ser **mantenible** para desarrollo futuro (modular, documentado)

**Objetivo final**: Sistema que "desaparece" en segundo plano, permitiendo que el usuario se enfoque en las tareas, no en el software.
