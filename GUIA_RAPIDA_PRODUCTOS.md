# 🎯 GUÍA RÁPIDA - Productos FUNCIONALES

## Verificar que Todo Funcione

### 1. Acceder a productos.php
```
URL: http://localhost:8080/heladeriacg/paginas/admin/productos.php
```

### 2. ¿Ves la tabla con productos existentes?
- Sí → ✅ Conexión BD OK
- No → ⚠️ Verificar conexión en `conexion/clientes_db.php`

---

## 🆕 Crear Nuevo Producto

1. **Click** botón "Agregar Producto" (arriba a la izquierda)
   - Debe abrirse modal
   - Modal tiene título "Crear Nuevo Producto"

2. **Llenar el formulario:**
   ```
   Nombre: Helado Fresa
   Sabor: Fresa
   Precio: 8.50
   Stock: 50
   Descripción: (opcional)
   Proveedor: Seleccionar del dropdown
   Activo: Checked (debe estar marcado)
   ```

3. **Click** "Guardar Producto"
   - Modal desaparece
   - Mensaje verde: "Producto creado exitosamente"
   - Nueva fila aparece en tabla

4. **Si hay ERROR:**
   ```
   Mensaje rojo: "Todos los campos requeridos deben estar completos"
   → Verificar que hayas llenado TODOS los campos
   
   Mensaje rojo: "Error de base de datos: ..."
   → Hay problema con BD, ver la consola del navegador (F12)
   ```

---

## ✏️ Editar Producto Existente

1. **En la tabla**, busca el producto que quieres editar

2. **Click botón "Editar"** (en la última columna)
   - Modal abre
   - Título: "Editar Producto"
   - Todos los campos están llenos con datos actuales

3. **Cambiar lo que necesites:**
   ```
   Ejemplo: cambiar precio de 8.50 a 9.99
   Ejemplo: cambiar stock de 50 a 40
   ```

4. **Click** "Guardar Producto"
   - Cambios guardan en BD
   - Modal desaparece
   - Tabla se actualiza
   - Mensaje verde: "Producto actualizado exitosamente"

---

## 📦 Actualizar Stock Rápido

1. **En la tabla**, busca el producto

2. **Click botón "Stock"** (en la última columna)
   - Aparece popup (prompt) preguntando nuevo stock
   - Muestra stock actual
   ```
   Ingrese el nuevo stock para el producto (actual: 50L):
   ```

3. **Escribir nuevo valor:**
   ```
   Ejemplo: 35
   ```

4. **Click OK**
   - Stock se actualiza inmediatamente
   - Tabla se refresca
   - Mensaje: "Stock actualizado exitosamente"

---

## 🗑️ Desactivar Producto

1. **En la tabla**, busca el producto

2. **Click botón "Desactivar"** (rojo, en la última columna)
   - Alert pide confirmación:
   ```
   ¿Estás seguro de que deseas desactivar el producto "Helado Fresa"?
   ```

3. **Click OK para confirmar**
   - Producto se desactiva en BD
   - Estado cambia a "Inactivo" (en rojo)
   - El producto sigue en BD (no se borra)

---

## 🔍 Buscar y Filtrar

### Buscar por Nombre/Sabor
```
Campo: "Buscar producto..."
Escribir: "fresa"
→ Solo muestra productos con "fresa" en nombre o sabor
```

### Filtrar por Estado
```
Dropdown: "Todos los productos"
Opciones:
- Todos los productos
- Activos (solo productos activos)
- Inactivos (solo desactivados)
```

### Filtrar por Proveedor
```
Dropdown: "Todos los proveedores"
Seleccionar proveedor específico
→ Solo muestra productos de ese proveedor
```

### Filtrar por Stock
```
Dropdown: "Todos los stocks"
Opciones:
- Bajo Stock (< 10L)
- Stock Medio (10-30L)
- Stock Alto (> 30L)
```

---

## 🐛 Si Algo No Funciona

### Modal no abre al hacer click "Agregar Producto"
```
1. Abre DevTools: F12
2. Pestaña "Console"
3. ¿Hay error en ROJO?
   SÍ → Copiar el error, revisar funciones JS
   NO → Problema de CSS, revisar modal.css incluido
```

### No puedo guardar el producto
```
1. Verifica que TODOS los campos * estén llenos
2. Abre DevTools (F12) → Console
3. ¿Hay error?
   SÍ → Problema de conexión BD
   NO → Revisar que el servidor esté corriendo
```

### Los cambios no se guardan en BD
```
1. ¿Ves mensaje de error rojo?
   SÍ → Leer el error, corregir
   NO → Revisar conexión a BD
2. Abre DevTools (F12) → Network
3. Buscar request POST
4. ¿Respuesta 200 OK?
   NO → Error del servidor (revisar logs)
```

### La tabla no muestra ningún producto
```
1. Verifica que BD tenga datos
2. Abre DevTools (F12) → Console
3. ¿Error de conexión?
4. Revisar: conexion/clientes_db.php
```

---

## 📊 Base de Datos

### Tabla: `productos`
```
id_producto      INT (PK)
nombre           VARCHAR(100) *
sabor            VARCHAR(100) *
descripcion      TEXT
precio           DECIMAL(10,2) *
stock            INT *
id_proveedor     INT (FK) *
activo           TINYINT (0/1)
fecha_registro   DATETIME
```

### Tabla: `proveedores`
```
id_proveedor     INT (PK)
empresa          VARCHAR(100)
contacto         VARCHAR(100)
correo           VARCHAR(100)
telefono         VARCHAR(20)
direccion        TEXT
```

### Tabla: `auditoria`
```
Registra todas las operaciones:
- CREATE: Nuevo producto
- UPDATE: Cambios en producto
- DELETE: Desactivación (soft delete)
```

---

## ✅ Checklist Funcionalidad

- [ ] Veo tabla con productos
- [ ] Puedo crear producto nuevo
- [ ] Puedo editar producto existente
- [ ] Puedo actualizar stock rápido
- [ ] Puedo desactivar producto
- [ ] Puedo buscar por nombre/sabor
- [ ] Puedo filtrar por estado
- [ ] Puedo filtrar por proveedor
- [ ] Puedo filtrar por stock
- [ ] Los cambios se guardan en BD
- [ ] Veo mensajes de éxito
- [ ] Modal se abre/cierra correctamente

**Si todo tiene ✅ = Funciona perfectamente!**

---

## 🚀 Comandos Útiles

### Ver logs de BD
```
Si usa MySQL:
tail -f /var/log/mysql/error.log

Si usa MariaDB:
tail -f /var/log/mariadb/mariadb.log
```

### Verificar BD
```sql
-- Ver tabla productos
SELECT * FROM productos;

-- Ver últimas 5 cambios en auditoría
SELECT * FROM auditoria ORDER BY fecha DESC LIMIT 5;

-- Ver productos con stock bajo
SELECT * FROM productos WHERE stock < 10;
```

---

## 📞 Soporte

Si necesitas ayuda:

1. **Abre DevTools:** F12
2. **Pestaña Console:** ¿Errores en ROJO?
3. **Pestaña Network:** ¿Las requests funcionan?
4. **Revisa MEJORAS_PRODUCTOS.md** para documentación técnica

---

**Última verificación:** Diciembre 2024
**Status:** ✅ FUNCIONAL Y LISTO
