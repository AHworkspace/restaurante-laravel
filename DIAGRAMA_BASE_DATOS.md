# 📊 DIAGRAMA Y ESTRUCTURA DE LA BASE DE DATOS

## Sistema de Gestión de Restaurante - TecnoWeb

---

## 🗂️ TABLAS PRINCIPALES DEL NEGOCIO

### 👥 **USUARIOS** (Usuarios)
Almacena la información de los usuarios del sistema
- `id` - Identificador único
- `nombre` - Nombre del usuario
- `apellido_paterno` - Apellido paterno
- `apellido_materno` - Apellido materno (opcional)
- `email` - Correo electrónico (único)
- `password` - Contraseña encriptada
- `remember_token` - Token de sesión
- `created_at`, `updated_at` - Timestamps

---

### 📦 **INSUMOS** (Inventario)
Gestiona todos los insumos del restaurante
- `id` - Identificador único
- `nombre` - Nombre del insumo
- `descripcion` - Descripción
- `stock_minimo` - Stock mínimo requerido
- `imagen` - Ruta de la imagen
- **FK:** `unidad_medida_id` → `unidad_medidas`
- **FK:** `categoria_id` → `categorias`

---

### 📏 **UNIDAD_MEDIDAS** (Unidades de Medida)
Define las unidades de medida (kg, litros, unidades, etc.)
- `id` - Identificador único
- `nombre` - Nombre de la unidad
- `abreviatura` - Abreviatura
- `descripcion` - Descripción

---

### 🏷️ **CATEGORIAS** (Categorías de Insumos)
Clasifica los insumos por categorías
- `id` - Identificador único
- `nombre` - Nombre de la categoría
- `descripcion` - Descripción

---

### 🚚 **PROVEEDORES** (Proveedores)
Información de proveedores de insumos
- `id` - Identificador único
- `nombre` - Nombre del proveedor
- `direccion` - Dirección
- `telefono` - Teléfono
- `email` - Correo electrónico
- `descripcion` - Descripción

---

### 🍽️ **RECETAS** (Platillos/Menú)
Recetas de platillos del restaurante
- `id` - Identificador único
- `nombre` - Nombre del platillo
- `descripcion` - Descripción
- `indicaciones` - Instrucciones de preparación
- `tiempo_preparacion` - Tiempo en minutos
- `imagen` - Ruta de la imagen
- `precio` - Precio de venta
- `visible` - Si está visible en el menú

---

### 🔗 **RECETAS_INSUMOS** (Relación Recetas-Insumos)
Tabla intermedia que define qué insumos usa cada receta
- `id` - Identificador único
- **FK:** `receta_id` → `recetas`
- **FK:** `insumo_id` → `insumos`
- `cantidad` - Cantidad necesaria
- `desperdicio` - Porcentaje de desperdicio

---

### 💰 **VENTAS** (Registro de Ventas)
Registro de todas las ventas realizadas
- `id` - Identificador único
- `cantidad` - Cantidad vendida
- `precio` - Precio unitario
- `total` - Total (cantidad × precio)
- **FK:** `receta_id` → `recetas`
- `created_at` - Fecha de la venta

---

### 🛒 **COMPRAS** (Registro de Compras)
Registro de compras de insumos a proveedores
- `id` - Identificador único
- `costo_total` - Costo total de la compra
- `proveedor` - Nombre del proveedor
- `descripcion` - Descripción de la compra
- `created_at` - Fecha de la compra

---

### 📊 **MOVIMIENTO_INVENTARIOS** (Movimientos de Inventario)
Historial completo de movimientos del inventario
- `id` - Identificador único
- `cantidad` - Cantidad del movimiento
- `tipo` - "entrada" o "salida"
- `motivo` - Razón del movimiento (compra, venta, ajuste, merma, etc.)
- **FK:** `insumo_id` → `insumos`
- **FK:** `venta_id` → `ventas` (opcional)
- **FK:** `compra_id` → `compras` (opcional)

---

### 📋 **PLANES_PRODUCCION** (Planes de Producción)
Planificación de producción de recetas
- `id` - Identificador único
- **FK:** `receta_id` → `recetas`
- `cantidad` - Cantidad a producir
- `created_at` - Fecha de creación del plan

---

### 📈 **REPORTES_PERSONALIZADOS** (Reportes)
Configuración de reportes personalizados
- `id` - Identificador único
- `nombre` - Nombre del reporte
- `fecha_desde` - Fecha inicio
- `fecha_hasta` - Fecha fin
- `descripcion` - Descripción

---

### 📜 **HISTORIAL** (Auditoría)
Registro de todas las acciones de usuarios
- `id` - Identificador único
- `user_id` - ID del usuario
- `usuario` - Nombre del usuario
- `rol` - Rol del usuario
- `accion` - Acción realizada
- `detalles` - Detalles adicionales
- `fecha` - Fecha de la acción
- `hora` - Hora de la acción
- `seccion` - Sección del sistema

---

## 🔐 TABLAS DE PERMISOS Y ROLES (Spatie Laravel Permission)

### **PERMISSIONS** (Permisos)
Define permisos individuales del sistema
- `id`, `name`, `guard_name`

### **ROLES** (Roles)
Define roles de usuario (Admin, Gerente, Empleado, etc.)
- `id`, `name`, `guard_name`

### **MODEL_HAS_PERMISSIONS** (Permisos por Usuario)
Asigna permisos específicos a usuarios
- `permission_id`, `model_type`, `model_id`

### **MODEL_HAS_ROLES** (Roles por Usuario)
Asigna roles a usuarios
- `role_id`, `model_type`, `model_id`

### **ROLE_HAS_PERMISSIONS** (Permisos por Rol)
Define qué permisos tiene cada rol
- `permission_id`, `role_id`

---

## 🔔 TABLAS DEL SISTEMA LARAVEL

### **NOTIFICATIONS** (Notificaciones)
Sistema de notificaciones (stock bajo, alertas, etc.)

### **SESSIONS** (Sesiones)
Gestión de sesiones de usuario

### **CACHE** y **CACHE_LOCKS**
Sistema de caché de Laravel

### **JOBS**, **JOB_BATCHES**, **FAILED_JOBS**
Sistema de colas y trabajos en segundo plano

---

## 🔗 DIAGRAMA DE RELACIONES

```
┌─────────────┐
│  USUARIOS   │
└─────────────┘
      │
      ├─── tiene muchos → HISTORIAL
      │
      └─── tiene roles/permisos → MODEL_HAS_ROLES, MODEL_HAS_PERMISSIONS

┌──────────────────┐
│  UNIDAD_MEDIDAS  │
└──────────────────┘
      │
      └─── tiene muchos → INSUMOS

┌─────────────┐
│ CATEGORIAS  │
└─────────────┘
      │
      └─── tiene muchos → INSUMOS

┌─────────────┐
│  INSUMOS    │
└─────────────┘
      │
      ├─── tiene muchos → RECETAS_INSUMOS
      ├─── tiene muchos → MOVIMIENTO_INVENTARIOS
      └─── pertenece a → UNIDAD_MEDIDAS, CATEGORIAS

┌─────────────┐
│  RECETAS    │
└─────────────┘
      │
      ├─── tiene muchos → RECETAS_INSUMOS
      ├─── tiene muchos → VENTAS
      └─── tiene muchos → PLANES_PRODUCCION

┌────────────────────┐
│ RECETAS_INSUMOS    │  (Tabla intermedia)
└────────────────────┘
      │
      ├─── pertenece a → RECETAS
      └─── pertenece a → INSUMOS

┌─────────────┐
│   VENTAS    │
└─────────────┘
      │
      ├─── pertenece a → RECETAS
      └─── tiene muchos → MOVIMIENTO_INVENTARIOS

┌─────────────┐
│  COMPRAS    │
└─────────────┘
      │
      ├─── relacionado con → PROVEEDORES (texto)
      └─── tiene muchos → MOVIMIENTO_INVENTARIOS

┌──────────────────────────┐
│ MOVIMIENTO_INVENTARIOS   │
└──────────────────────────┘
      │
      ├─── pertenece a → INSUMOS
      ├─── pertenece a → VENTAS (opcional)
      └─── pertenece a → COMPRAS (opcional)
```

---

## 🎯 FLUJO DE OPERACIONES PRINCIPALES

### 1️⃣ **Gestión de Inventario**
```
COMPRAS → MOVIMIENTO_INVENTARIOS (entrada) → INSUMOS (actualiza stock)
```

### 2️⃣ **Proceso de Venta**
```
VENTAS → RECETAS → RECETAS_INSUMOS → INSUMOS
                ↓
    MOVIMIENTO_INVENTARIOS (salida)
```

### 3️⃣ **Plan de Producción**
```
PLANES_PRODUCCION → RECETAS → RECETAS_INSUMOS → calcula insumos necesarios
```

### 4️⃣ **Auditoría y Seguridad**
```
Toda acción → HISTORIAL (registro de auditoría)
USERS → ROLES → PERMISSIONS (control de acceso)
```

---

## 📌 NOTAS IMPORTANTES

1. **Integridad Referencial**: Todas las relaciones están definidas con Foreign Keys
2. **Soft Deletes**: No implementado (los registros se eliminan físicamente)
3. **Timestamps**: Todas las tablas principales tienen `created_at` y `updated_at`
4. **Sistema de Permisos**: Usa el paquete Spatie Laravel Permission
5. **Notificaciones**: Sistema para alertas de stock bajo
6. **Predicción IA**: El servicio de IA usa datos de VENTAS y MOVIMIENTO_INVENTARIOS

---

## 🔧 CONFIGURACIÓN DE BASE DE DATOS

La configuración se encuentra en:
- **Archivo**: `sistema-web/config/database.php`
- **Variables de entorno**: `.env`
- **Conexión por defecto**: SQLite (configurable a MySQL/PostgreSQL)

---

## 📦 SEEDERS DISPONIBLES

Los seeders poblan la base de datos con datos de prueba:
- `CategoriaSeeder.php`
- `InsumoSeeder.php`
- `UnidadMedidaSeeder.php`
- `ProveedorSeeder.php`
- `RecetaSeeder.php`
- `VentaSeeder.php`
- `MovimientoSeeder.php`

---

**Generado**: Noviembre 2025  
**Proyecto**: Sistema de Gestión de Restaurante - TecnoWeb

