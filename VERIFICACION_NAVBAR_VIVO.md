# 🚀 GUÍA RÁPIDA - VERIFICAR NAVBAR EN VIVO

## Paso 1: Abrir el Admin Panel

```
URL: http://localhost/heladeriacg/paginas/admin/index.php
```

O si está en diferente servidor:
```
URL: http://[TU_SERVIDOR]/heladeriacg/paginas/admin/index.php
```

---

## Paso 2: Verificaciones RÁPIDAS (30 segundos)

### En Desktop (1200px+)
```
✓ ¿Ves "Concelato Admin" con icono de helado?
✓ ¿Los 10 menu items están en línea horizontal?
✓ ¿Ves el botón rojo "Cerrar Sesión"?
✓ ¿El link "Dashboard" está en NEGRITA/CYAN?

Si todo es SÍ → ✅ Desktop OK
```

### En Mobile (reducir ventana < 768px)
```
✓ ¿El logo ahora muestra SOLO el icono?
✓ ¿Hay un botón con 3 líneas (≡) arriba?
✓ ¿Los items del menú están OCULTOS?
✓ ¿Al clickear el botón (≡) se abre un menú vertical?

Si todo es SÍ → ✅ Mobile OK
```

---

## Paso 3: Pruebas de Interactividad

### En Desktop
```javascript
// Abrir DevTools (F12)
// Ir a Console
// Escribir:

window.MenuToggle  // Debe mostrar objeto

// Luego pasar mouse sobre un link
// Debe cambiar a color CYAN y tener fondo cyan claro
```

### En Mobile
```
1. Clickear el botón ≡ (hamburger)
   → El menú debe ABRIRSE con animación suave

2. Clickear en "Productos"
   → Debe ir a productos.php
   → El menú debe CERRARSE
   → "Productos" debe estar en NEGRITA/CYAN

3. Clickear en el icono ≡ nuevamente
   → El menú debe CERRARSE con animación

4. Presionar ESC
   → El menú debe CERRARSE (si estaba abierto)
```

---

## Paso 4: Verificaciones por Página

### Navegar Entre Páginas
```
index.php        → ✅ "Dashboard" debe estar active (CYAN + Bold)
productos.php    → ✅ "Productos" debe estar active
ventas.php       → ✅ "Ventas" debe estar active
empleados.php    → ✅ "Empleados" debe estar active
clientes.php     → ✅ "Clientes" debe estar active
proveedores.php  → ✅ "Proveedores" debe estar active
usuarios.php     → ✅ "Usuarios" debe estar active
promociones.php  → ✅ "Promociones" debe estar active
sucursales.php   → ✅ "Sucursales" debe estar active
configuracion.php → ✅ "Configuración" debe estar active
```

---

## Paso 5: Verificación de Console (Sin Errores)

### Abrir DevTools (F12)
```
1. Ir a la pestaña "Console"
2. ¿Ves algún error en ROJO?
   
   NO → ✅ Perfecto! No hay errores

   SÍ → ⚠️ Revisar qué error aparece
```

### Comandos de Verificación
```javascript
// En Console escribir:

// Verificar que MenuToggle existe
window.MenuToggle !== undefined
// Debe retornar: true

// Verificar que el header existe
document.querySelector('.admin-header') !== null
// Debe retornar: true

// Verificar que el nav existe
document.querySelector('#admin-nav') !== null
// Debe retornar: true

// Ver el estado actual del menú
document.querySelector('.admin-header').classList.contains('nav-open')
// Si menú cerrado: false
// Si menú abierto: true

// Ver aria-expanded
document.querySelector('.menu-toggle').getAttribute('aria-expanded')
// Debe ser: 'false' o 'true'
```

---

## Paso 6: Verificación Responsive

### Usar Chrome DevTools Responsive Design (F12 → Ctrl+Shift+M)

```
Tamaño 375px × 667px (iPhone SE)
├─ Logo: SOLO icono (sin texto)
├─ Hamburger: VISIBLE (≡)
├─ Menu items: OCULTOS
├─ Textos: Pequeños pero legibles
└─ Touch targets: Fáciles de tocar

Tamaño 768px × 1024px (iPad)
├─ Logo: Icono + texto (comprimido)
├─ Hamburger: VISIBLE
├─ Menu items: Dropdown vertical
├─ Espaciado: Reducido
└─ Responsive: ✅

Tamaño 1024px × 768px (Tablet landscape)
├─ Logo: Icono + texto
├─ Hamburger: VISIBLE (pero pequeño)
├─ Menu items: Dropdown vertical
├─ Fuentes: Medianas
└─ Responsive: ✅

Tamaño 1280px × 720px (Desktop)
├─ Logo: Icono + texto (normal)
├─ Hamburger: OCULTO
├─ Menu items: Todos en FILA HORIZONTAL
├─ Fuentes: Normales
└─ Responsive: ✅
```

---

## Paso 7: Verificación de Accesibilidad

### Navegación por Teclado

```
1. Presionar TAB varias veces
   → Debe navegar por:
      - Hamburger (si visible)
      - Cada link del menú
      - Logout button

2. Presionar TAB en menú cerrado
   → Debe pasar de hamburger al siguiente elemento

3. Presionar ENTER en un link
   → Debe seguir el link

4. Presionar ESC (en menú abierto)
   → El menú debe cerrarse
   → El foco debe retornar al hamburger
```

### Con Screen Reader (simulación)

```
ChromeVox (Extensión Chrome)
├─ Hamburger: "Alternar menú de navegación, botón"
├─ Links: "Dashboard, enlace" "Productos, enlace", etc.
├─ Logout: "Cerrar Sesión, enlace"
└─ Estados: aria-expanded "false/true" anunciado

NVDA (Windows)
├─ Mismo comportamiento que ChromeVox
└─ Aria labels debidamente anunciados
```

---

## Paso 8: Verificación Visual

### Colores Esperados

```
Logo:       CYAN (#0891b2)
Links hover: CYAN background light + CYAN text
Active link: BOLD + CYAN text
Logout:     Gradiente ROJO → NARANJA
Focus:      Outline CYAN 2px
```

### Fuentes Esperadas

```
Logo:       Poppins 600 (bold)
Links:      Poppins 400 (regular)
Active link: Poppins 600 (bold)
Logout:     Poppins 600 (bold)
```

### Espaciado Esperado

```
Desktop:    Padding normal, gap 8px entre items
Tablet:     Padding reducido, gap 8px
Mobile:     Ultra-compacto, items full-width
```

---

## Paso 9: Checklist Rápido ✅

```
VISUAL
─────────────────────────────────
☐ Logo visible en todos los tamaños
☐ Menú hamburger aparece en mobile
☐ 10 items de navegación consistentes
☐ Logout button con estilos especiales
☐ Colores correctos (cyan para activo)
☐ Fuentes legibles en todos los breakpoints

INTERACTIVIDAD
─────────────────────────────────
☐ Hamburger abre/cierra menú
☐ Links son clickeables
☐ Logout es clickeable
☐ Menú se cierra al clickear un link
☐ Menú se cierra al clickear fuera
☐ Menú se cierra con ESC

ACCESIBILIDAD
─────────────────────────────────
☐ Navegación por TAB funciona
☐ Focus visible en todos los elementos
☐ ARIA labels anunciados
☐ ESC cierra menú
☐ Touch targets ≥ 44px

RESPONSIVE
─────────────────────────────────
☐ Mobile small (< 480px): OK
☐ Mobile (480-768px): OK
☐ Tablet (768-1024px): OK
☐ Desktop (≥ 1024px): OK
☐ Textos escalan suavemente

NAVEGACIÓN
─────────────────────────────────
☐ Todos los 10 links funcionan
☐ Cada página muestra su link como active
☐ Logout redirecciona a cerrar_sesion.php
☐ No hay errores en console

TOTAL: ___/47 checklist items completados
```

---

## Paso 10: Si Algo No Funciona

### "El menú hamburger no abre en mobile"

```
1. Abre DevTools (F12)
2. Ve a Console
3. Busca errores en ROJO
4. Verifica: document.querySelector('.menu-toggle') !== null
   → Debe retornar: true
5. Verifica: window.MenuToggle !== undefined
   → Debe retornar: true
6. Limpia cache: Ctrl+Shift+Delete (selecciona todo)
7. Recarga la página
```

### "El texto no es responsivo en algunos breakpoints"

```
1. Abre DevTools (F12)
2. Ve a la pestaña Sources
3. Busca: estilos_admin.css
4. Busca: clamp(
5. Verifica que los clamp() esté correctamente escritos
6. La fórmula debe ser: clamp(MIN, PREFERRED, MAX)
7. Ejemplos correctos:
   - clamp(0.85rem, 1.5vw, 1rem)
   - clamp(1.1rem, 3vw, 1.4rem)
```

### "Los colores no se ven correctos"

```
1. Abre DevTools (F12)
2. Ve a Elements/Inspector
3. Clickea en un elemento del navbar
4. Ve el CSS en la sección de estilos
5. Busca: color: y verifica el valor
6. Cyan debería ser: #0891b2
7. Rojo logout debería ser: #ef4444
8. Si no ves los valores, revisa que estilos_admin.css esté vinculado
```

### "Los links no me llevan a las páginas"

```
1. Abre DevTools (F12)
2. Ve a Elements/Inspector
3. Busca los <a> tags en el navbar
4. Verifica que tengan href= correcto
5. Ejemplos correctos:
   - <a href="index.php">
   - <a href="productos.php">
   - <a href="ventas.php">
6. Verifica que NO tengan href="#" o href=""
```

---

## Paso 11: Comparación de Resultados

### ✅ CORRECTO: Cómo debería verse

```
DESKTOP (1200px+)
═══════════════════════════════════════════════════════
[🍦 Concelato Admin] Dashboard Productos Ventas... [Logout]
───────────────────────────────────────────────────────
Todos los links en FILA HORIZONTAL
Color de "Dashboard": CYAN + BOLD (active)
Logout button: Rojo gradiente a naranja

MOBILE (768px)
═══════════════════════════════════════════════════════
[≡] [🍦]
├─ Dashboard
├─ Productos
├─ Ventas
├─ Empleados
├─ Clientes
├─ Proveedores
├─ Usuarios
├─ Promociones
├─ Sucursales
├─ Configuración
├─ [Logout] (full-width rojo)
───────────────────────────────────────────────────────
Menú VERTICAL
Items full-width
Logout button rojo en la parte inferior
```

### ❌ INCORRECTO: Problemas comunes

```
✗ Logo muestra "Concelato Gelateria" en mobile
  → Debería mostrar solo el icono 🍦

✗ Hamburger menu no visible en mobile (< 768px)
  → Debería aparecer un botón ≡

✗ Menu items no se cierran al hacer click
  → Debería cerrarse automáticamente

✗ Los links no están en color CYAN cuando están activos
  → Debería estar en CYAN y BOLD

✗ Logout button no es rojo/naranja
  → Debería tener gradiente rojo-naranja

✗ En DevTools aparecen errores en RED
  → Revisar qué error es exactamente
```

---

## Paso 12: Contacto/Soporte

Si encuentras problemas:

1. **Verifica primero:**
   - ¿Está limpio el cache del navegador?
   - ¿Actualizaste la página (F5)?
   - ¿Aparecen errores en DevTools Console (F12)?

2. **Información útil a documentar:**
   - Navegador y versión
   - Tamaño de pantalla
   - Qué no funciona exactamente
   - Errores que ves en Console
   - Screenshot del problema

3. **Archivos a revisar:**
   - `/heladeriacg/css/admin/estilos_admin.css` (CSS)
   - `/heladeriacg/js/admin/script.js` (JavaScript)
   - `/heladeriacg/paginas/admin/[page].php` (HTML)

---

## Resumen en 1 Minuto ⚡

```
1. Abre http://localhost/heladeriacg/paginas/admin/
2. ¿Ves el navbar con "Concelato Admin" y 10 links?
   SÍ → ✅ HTML OK
3. ¿En desktop están todos en fila? En mobile hay ≡?
   SÍ → ✅ CSS Responsive OK
4. ¿Clickear ≡ abre menú? ¿ESC lo cierra?
   SÍ → ✅ JavaScript OK
5. ¿Navega entre páginas sin errores?
   SÍ → ✅ Accesibilidad OK
6. ¿Abre F12 Console sin errores en ROJO?
   SÍ → ✅ TODO PERFECTO!
```

---

**Última verificación:** Diciembre 2024
**Status:** ✅ Listo para usar
