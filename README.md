# Sistema de Gestión de Heladería "Heladería CG"

## Descripción General

Sistema web completo de gestión para una heladería, desarrollado con PHP, MySQL, HTML, CSS y JavaScript. El sistema permite gestionar productos, clientes, ventas, empleados, proveedores, pedidos, inventario y reportes. El sistema sigue un patrón MVC simplificado y utiliza un enfoque de autenticación basado en roles para controlar el acceso a diferentes funcionalidades.

## Características Principales

### 1. Gestión de Productos
- Crear, leer, actualizar y eliminar productos (CRUD completo)
- Gestión de inventario y stock
- Categorización por tipo, sabor y precio
- Imágenes y descripciones de productos
- Control de activación/desactivación de productos

### 2. Gestión de Clientes
- Registro y mantenimiento de información de clientes
- Historial de compras
- Preferencias de sabores y métodos de pago

### 3. Gestión de Ventas
- Registro de ventas con carrito de compras
- Sistema de punto de venta (POS)
- Generación de facturas/recibos
- Control de métodos de pago
- Búsqueda y filtro de ventas

### 4. Gestión de Empleados
- Registro y control de empleados
- Asignación de roles y permisos
- Control de turnos y horarios
- Autenticación de usuarios

### 5. Gestión de Proveedores
- Registro de proveedores
- Control de suministros
- Relación con productos
- Información de contacto

### 6. Gestión de Pedidos
- Sistema de pedidos clientes
- Seguimiento de estado de pedidos
- Control de entrega y recojo

### 7. Reportes y Estadísticas
- Estadísticas de ventas
- Productos más vendidos
- Informes financieros
- Gráficos y visualizaciones

## Arquitectura del Sistema

### Estructura de Carpetas
```
heladeriacg/
├── conexion/          # Archivos de conexión y sesión
├── conexion/          # Archivos de base de datos y clientes
├── css/              # Hojas de estilo CSS
│   ├── admin/        # Estilos para panel de administración
│   ├── cliente/      # Estilos para panel de cliente
│   ├── empleado/     # Estilos para panel de empleado
│   └── publico/      # Estilos para área pública
├── js/               # Archivos JavaScript
├── paginas/          # Páginas principales
│   ├── admin/        # Páginas de administración
│   ├── cliente/      # Páginas de clientes
│   ├── empleado/     # Páginas de empleados
│   └── publico/      # Páginas públicas
└── bd/               # Scripts de base de datos
```

### Base de Datos
- Motor: MySQL
- Nombre de la base de datos: `heladeriacgbd`
- Tablas principales:
  - `productos`: Información de productos (nombre, sabor, precio, stock)
  - `clientes`: Datos de clientes
  - `empleados`: Información de empleados
  - `ventas`: Registro de ventas
  - `detalle_ventas`: Detalles de ventas
  - `proveedores`: Información de proveedores
  - `usuarios`: Autenticación de usuarios
  - `roles`: Roles de usuarios (admin, empleado, cliente)

### Patrones de Diseño Implementados

1. **Autenticación basada en sesiones**
   - Verificación de roles antes de acceder a páginas privadas
   - Control de permisos por nivel de usuario

2. **CRUD (Create, Read, Update, Delete)**
   - Implementado en todas las entidades principales
   - Validaciones y manejo de errores

3. **Responsive Design**
   - Adaptación a diferentes dispositivos (móvil, tablet, desktop)
   - Uso de media queries y layout flexible

## Tipos de Usuarios

### 1. Administrador
- Acceso completo al sistema
- Gestión de todos los módulos
- Reportes y estadísticas
- Configuración del sistema

### 2. Empleado
- Registro de ventas
- Gestión de clientes
- Consulta de inventario
- Procesamiento de pedidos

### 3. Cliente
- Consulta de productos
- Realización de pedidos
- Consulta de estado de pedidos
- Historial de compras

## Tecnologías Utilizadas

- **Backend**: PHP 7.x+
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap (componentes) + CSS personalizado
- **Iconografía**: Font Awesome
- **Tipografía**: Google Fonts (Poppins)
- **Servidor**: Apache (XAMPP)

## Instalación

1. **Requisitos**:
   - XAMPP (Apache, MySQL, PHP)
   - Navegador web moderno

2. **Instalación**:
   - Clonar/copiar el proyecto a `htdocs/heladeriacg`
   - Importar `bd_en_xampp.sql` en phpMyAdmin
   - Iniciar Apache y MySQL en XAMPP
   - Acceder a `http://localhost/heladeriacg`

3. **Usuarios por defecto**:
   - Admin: `admin` / `admin`
   - Empleado: `empleado` / `empleado`
   - Cliente: `cliente` / `cliente`

## Módulos Funcionales

### Panel de Administración
- Dashboard con estadísticas
- Gestión de productos
- Gestión de clientes
- Gestión de empleados
- Gestión de ventas
- Reportes y estadísticas
- Gestión de proveedores
- Configuración del sistema

### Panel de Empleado
- Sistema de punto de venta
- Consulta de productos
- Registro de ventas
- Control de inventario
- Consulta de clientes

### Panel de Cliente
- Consulta de productos
- Realización de pedidos
- Consulta de estado de pedidos
- Historial de compras

## Seguridad Implementada

- Validación de sesión en todas las páginas privadas
- Control de roles y permisos
- Protección contra inyección SQL (consultas preparadas)
- Verificación de tipo de datos
- Sanitización de entradas

## Personalización

El sistema es altamente personalizable:
- Temas y colores modificables en archivos CSS
- Estructura de base de datos adaptable
- Funcionalidades extensibles
- Diseño responsivo adaptable

## API y Servicios

- Archivos PHP para procesamiento de datos
- Intercambio de datos en formato JSON
- Consultas AJAX para actualizaciones dinámicas
- Generación de reportes en diferentes formatos

## Notas Importantes

1. El sistema utiliza sesiones PHP para control de acceso
2. Todos los formularios incluyen validación tanto en cliente como en servidor
3. El sistema maneja diferentes tipos de usuarios con permisos específicos
4. La base de datos incluye relaciones entre tablas para mantener integridad referencial
5. El sistema tiene funcionalidades CRUD completas para todas las entidades principales

## Futuras Extensiones

- Integración con sistemas de pago electrónicos
- Aplicación móvil
- Sincronización en tiempo real
- Soporte multilenguaje
- API REST para integración externa

## Licencia

Proyecto académico/desarrollo interno. Derechos reservados para fines educativos/comerciales.