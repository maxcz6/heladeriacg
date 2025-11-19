# Guía de Testing - Barra de Navegación Responsiva

## Verificación de Funcionalidad

### 1. TESTING EN DESKTOP (1200px+)

#### Visual
- [ ] Logo visible completamente ("Concelato Admin")
- [ ] Los 10 nav items visibles en fila horizontal
- [ ] Botón hamburger OCULTO
- [ ] Logout button con gradiente rojo-naranja visible
- [ ] Espaciado consistente entre elementos

#### Interactividad
- [ ] Al pasar mouse sobre links, cambio de color a cyan
- [ ] Al pasar mouse sobre logout, efecto elevación (translateY -2px)
- [ ] Botón hamburger no aparece

---

### 2. TESTING EN TABLET (1024px)

#### Visual
- [ ] Logo aún visible pero con max-width 150px
- [ ] Nav items visibles pero con padding reducido
- [ ] Fuentes ligeramente más pequeñas (clamp escalando)
- [ ] Espaciado entre items reducido

#### Interactividad
- [ ] Links clickeables
- [ ] Hamburger aún NO visible
- [ ] Hover effects funcionan

---

### 3. TESTING EN MOBILE TABLET (768px)

#### Visual
- [ ] Hamburger menu VISIBLE (botón con 3 líneas)
- [ ] Logo completo visible
- [ ] Nav items COLAPSADOS (no visibles)
- [ ] Altura del header reducida

#### Interactividad
- [ ] Click en hamburger → menu se abre
- [ ] Menú despliega verticalmente
- [ ] Click en link → menú se cierra
- [ ] Click fuera del menu → se cierra
- [ ] ESC key → menu se cierra
- [ ] Logout visible en el dropdown

---

### 4. TESTING EN MOBILE SMALL (480px)

#### Visual
- [ ] Logo solo con icono (span texto oculto)
- [ ] Hamburger 40x40px mínimo
- [ ] Nav items en dropdown vertical, full-width
- [ ] Fuentes en mínimo pero legibles
- [ ] Espaciado ultra-compacto

#### Interactividad
- [ ] Mismo comportamiento que 768px
- [ ] Touch targets ≥ 44x44px
- [ ] Fácil de tocar en smartphone

---

### 5. TESTING DE ACCESIBILIDAD

#### Keyboard Navigation
- [ ] TAB navega entre elementos (hamburger, links, logout)
- [ ] TAB dentro del menú abierto navega los items
- [ ] SHIFT+TAB va hacia atrás
- [ ] ENTER/SPACE activa links
- [ ] ESC cierra menú

#### Screen Reader (NVDA / JAWS)
- [ ] Hamburger anunciado como "Alternar menú de navegación, botón"
- [ ] aria-expanded "false" cuando cerrado
- [ ] aria-expanded "true" cuando abierto
- [ ] Links anunciados con icono y texto
- [ ] Logout anunciado como "Cerrar Sesión, enlace"

#### Focus Visible
- [ ] Outline cyan de 2px visible en todos los elementos
- [ ] Cuando se abre menú, foco en primer item
- [ ] Cuando se cierra menú, foco retorna a hamburger

---

### 6. TESTING DE RESPONSIVENESS

#### Viewport Width Tests
```
Ancho → Comportamiento esperado
360px  → Mobile small (logo icon-only, hamburger visible)
480px  → Mobile small (ultra-compacto)
768px  → Mobile tablet (hamburger, dropdown vertical)
1024px → Tablet (nav items visibles, reducido)
1200px → Desktop (todo visible, normal)
1920px → Desktop grande (todo visible, normal)
```

#### Font Size Tests
```javascript
// En DevTools Console
console.log(window.getComputedStyle(
  document.querySelector('.logo')
).fontSize);

// Debe escalar suavemente de ~17.6px a ~22.4px
```

---

### 7. TESTING DE PÁGINAS ESPECÍFICAS

#### Verificar por Página
- [ ] index.php - Dashboard active
- [ ] productos.php - Productos active
- [ ] ventas.php - Ventas active
- [ ] empleados.php - Empleados active
- [ ] clientes.php - Clientes active
- [ ] proveedores.php - Proveedores active
- [ ] usuarios.php - Usuarios active
- [ ] promociones.php - Promociones active
- [ ] sucursales.php - Sucursales active
- [ ] configuracion.php - Configuración active
- [ ] reportes.php - (Sin active específico, es accesible)
- [ ] operaciones_lote.php - (Sin active específico, es accesible)

#### Por Página Verificar
- [ ] Header tiene la estructura correcta
- [ ] Script.js está incluido (`<script src="/heladeriacg/js/admin/script.js"></script>`)
- [ ] El link correcto tiene `class="active"`
- [ ] Logout button es funcional

---

### 8. TESTING DE NAVEGACIÓN ENTRE PÁGINAS

#### Flujo de Usuario
1. [ ] Abrir index.php
2. [ ] Clickear "Productos" → debe ir a productos.php
3. [ ] Clickear "Ventas" → debe ir a ventas.php
4. [ ] En mobile, menú debe cerrarse automáticamente
5. [ ] El nuevo link debe estar "active"
6. [ ] No debe haber errores en console

---

### 9. TESTING DE LOGOUT

#### Funcionalidad
- [ ] Botón logout visible en todas las páginas
- [ ] Click en logout → modal de confirmación
- [ ] Confirmar → redirige a cerrar_sesion.php
- [ ] Cancelar → permanece en la misma página
- [ ] En mobile, logout es full-width en el dropdown

---

### 10. TESTING CROSS-BROWSER

#### Navegadores a Probar
- [ ] Chrome/Chromium (Windows, Mac, Linux, Android)
- [ ] Firefox (Windows, Mac, Linux)
- [ ] Safari (Mac, iOS)
- [ ] Edge (Windows, Android)
- [ ] Samsung Internet (Android)

#### Por Navegador Verificar
- [ ] Estilos CSS aplicados correctamente
- [ ] Animaciones suaves (no glitchy)
- [ ] JavaScript funciona sin errores
- [ ] ARIA attributes soportados

---

### 11. CONSOLE.LOG CHECKS

```javascript
// En DevTools Console, no debe haber errores

// Verificar que MenuToggle se inicializó
window.MenuToggle !== undefined // true

// Verificar elementos del DOM
document.querySelector('.menu-toggle') // debe retornar elemento
document.querySelector('#admin-nav') // debe retornar nav
document.querySelector('.admin-header') // debe retornar header
```

---

### 12. PERFORMANCE CHECKS

#### Lighthouse Audit
- [ ] Performance ≥ 90
- [ ] Accessibility ≥ 90
- [ ] Best Practices ≥ 90
- [ ] SEO ≥ 90

#### Network Tab (DevTools)
- [ ] script.js carga sin errores
- [ ] estilos_admin.css carga sin errores
- [ ] No hay 404 errors
- [ ] Tiempo de carga < 2s

---

### 13. CASOS ESPECIALES

#### En Conexión Lenta
- [ ] Menu toggle funciona incluso si script.js tarda
- [ ] Estilos se aplican correctamente sin JS
- [ ] No hay FOUC (Flash of Unstyled Content)

#### En Navegador Antiguo
- [ ] Fallback para clamp() (algunos browsers viejos)
- [ ] Menu funciona sin JavaScript (graceful degradation)
- [ ] Estilos CSS3 no rompen layout

#### En Pantalla Grande (4K)
- [ ] clamp() respeta el máximo (1.4rem para logo)
- [ ] No hay distorsión de fuentes
- [ ] Espaciado correcto

---

### 14. MOBILE-SPECIFIC TESTS

#### Touch Interaction (en device real o emulador)
- [ ] Tap en hamburger abre menu
- [ ] Tap en link lo sigue
- [ ] Tap fuera del menu lo cierra
- [ ] Touch targets son fáciles de tocar (44x44px)

#### Mobile Landscape
- [ ] En landscape 480px ancho × 800px alto
- [ ] Menu aún funciona
- [ ] No hay overflow horizontal
- [ ] Layout adapta correctamente

---

### 15. VALIDACIÓN HTML/CSS

#### W3C Validator
```
Herramienta: https://validator.w3.org/
- [ ] No hay errores HTML críticos
- [ ] Las ARIA attributes son válidas
- [ ] Las etiquetas semánticas son correctas
```

#### CSS Validator
```
Herramienta: https://jigsaw.w3.org/css-validator/
- [ ] No hay errores CSS críticos
- [ ] clamp() es válido en target browsers
- [ ] Variables CSS son válidas
```

---

## COMANDOS ÚTILES PARA TESTING

### Chrome DevTools
```javascript
// Ver si MenuToggle está activo
window.MenuToggle

// Simular click en hamburger
document.querySelector('.menu-toggle').click()

// Ver aria-expanded actual
document.querySelector('.menu-toggle').getAttribute('aria-expanded')

// Ver si nav está abierto
document.querySelector('.admin-header').classList.contains('nav-open')

// Font size del logo (en cada breakpoint)
window.getComputedStyle(document.querySelector('.logo')).fontSize
```

### Testing Device Sizes
```
Ctrl+Shift+M en Chrome/Firefox = Modo responsive

Presets comunes:
- iPhone SE: 375x667
- iPhone 12: 390x844
- iPhone 14 Pro: 393x852
- Pixel 5: 393x851
- Samsung Galaxy S21: 360x800
- iPad: 768x1024
- iPad Pro: 1024x1366
```

---

## REPORTE DE TESTING

### Template a Completar
```
FECHA: ___________
NAVEGADOR: ___________
DISPOSITIVO: ___________
VIEWPORT: ___________px × ___________px

VISUAL:
- [ ] Logo visible
- [ ] Nav items visibles/ocultos correctamente
- [ ] Hamburger visible/oculto correctamente
- [ ] Fuentes legibles
- [ ] Espaciado consistente

INTERACTIVIDAD:
- [ ] Links funcionan
- [ ] Menu abre/cierra
- [ ] Logout funciona
- [ ] Hover effects funcionan

ACCESIBILIDAD:
- [ ] Keyboard navigation funciona
- [ ] Focus visible
- [ ] ARIA attributes correctos

PROBLEMAS ENCONTRADOS:
_________________________________
_________________________________

ESTADO: ✅ PASS / ❌ FAIL
```

---

## CRITERIOS DE ACEPTACIÓN

### Debe Cumplir
✅ Navbar consistente en todas las 12 páginas
✅ Responsive en 480px, 768px, 1024px, 1200px
✅ Textos escalados con clamp()
✅ Hamburger menu funcional en mobile
✅ ARIA attributes completos
✅ Keyboard navigation completa
✅ No errores en console
✅ Touch targets ≥ 44x44px
✅ Logout button funcional
✅ Active state indicators correctos

### Nice to Have
✨ Animaciones suaves
✨ Tema oscuro (futuro)
✨ Breadcrumb navigation (futuro)
✨ Submenu items (futuro)

---

**Status:** Ready for Testing ✅
