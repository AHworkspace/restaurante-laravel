-- ============================================
-- SCRIPT COMPLETO DE BASE DE DATOS
-- Sistema de Gestión de Restaurante
-- ============================================

-- Tabla: usuarios
-- Descripción: Almacena información de los usuarios del sistema
CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: unidad_medidas
-- Descripción: Unidades de medida para los insumos (kg, litros, unidades, etc.)
CREATE TABLE unidad_medidas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    abreviatura VARCHAR(10) NOT NULL,
    descripcion VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: categorias
-- Descripción: Categorías para clasificar los insumos
CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: proveedores
-- Descripción: Información de los proveedores de insumos
CREATE TABLE proveedores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    direccion VARCHAR(100) NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: insumos
-- Descripción: Inventario de insumos del restaurante
CREATE TABLE insumos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) NULL,
    stock_minimo FLOAT NOT NULL,
    imagen VARCHAR(100) NULL,
    unidad_medida_id BIGINT UNSIGNED NOT NULL,
    categoria_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (unidad_medida_id) REFERENCES unidad_medidas(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabla: recetas
-- Descripción: Recetas de platillos del restaurante
CREATE TABLE recetas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    indicaciones TEXT NOT NULL,
    tiempo_preparacion INT NOT NULL,
    imagen VARCHAR(100) NULL,
    precio DECIMAL(10, 2) DEFAULT 0,
    visible BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: recetas_insumos
-- Descripción: Relación muchos a muchos entre recetas e insumos
CREATE TABLE recetas_insumos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receta_id BIGINT UNSIGNED NOT NULL,
    insumo_id BIGINT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    desperdicio INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (receta_id) REFERENCES recetas(id),
    FOREIGN KEY (insumo_id) REFERENCES insumos(id)
);

-- Tabla: ventas
-- Descripción: Registro de ventas de recetas/platillos
CREATE TABLE ventas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cantidad FLOAT NOT NULL,
    precio FLOAT NOT NULL,
    total FLOAT NOT NULL,
    receta_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (receta_id) REFERENCES recetas(id)
);

-- Tabla: compras
-- Descripción: Registro de compras de insumos
CREATE TABLE compras (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    costo_total FLOAT NOT NULL,
    proveedor VARCHAR(200) NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: movimiento_inventarios
-- Descripción: Historial de movimientos del inventario (entradas y salidas)
CREATE TABLE movimiento_inventarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cantidad FLOAT NOT NULL,
    tipo VARCHAR(255) NOT NULL, -- 'entrada' o 'salida'
    motivo VARCHAR(255) NOT NULL, -- 'compra', 'venta', 'ajuste', etc.
    insumo_id BIGINT UNSIGNED NOT NULL,
    venta_id BIGINT UNSIGNED NULL,
    compra_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (compra_id) REFERENCES compras(id)
);

-- Tabla: planes_produccion
-- Descripción: Planes de producción para recetas
CREATE TABLE planes_produccion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receta_id BIGINT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
);

-- Tabla: reportes_personalizados
-- Descripción: Configuración de reportes personalizados
CREATE TABLE reportes_personalizados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla: historial
-- Descripción: Registro de acciones de usuarios en el sistema
CREATE TABLE historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    usuario VARCHAR(255) NOT NULL,
    rol VARCHAR(255) NOT NULL,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT NULL,
    fecha DATE NULL,
    hora TIME NULL,
    seccion VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ============================================
-- TABLAS DEL SISTEMA DE PERMISOS (Spatie)
-- ============================================

-- Tabla: permissions
-- Descripción: Permisos individuales del sistema
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
);

-- Tabla: roles
-- Descripción: Roles de usuario
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
);

-- Tabla: model_has_permissions
-- Descripción: Relación entre modelos y permisos
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    KEY model_has_permissions_model_id_model_type_index (model_id, model_type),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Tabla: model_has_roles
-- Descripción: Relación entre modelos y roles
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    KEY model_has_roles_model_id_model_type_index (model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Tabla: role_has_permissions
-- Descripción: Relación entre roles y permisos
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- ============================================
-- TABLAS DEL SISTEMA LARAVEL
-- ============================================

-- Tabla: notifications
-- Descripción: Sistema de notificaciones de Laravel
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY notifications_notifiable_type_notifiable_id_index (notifiable_type, notifiable_id)
);

-- Tabla: sessions
-- Descripción: Sesiones de usuario
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
);

-- Tabla: cache
-- Descripción: Sistema de caché de Laravel
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);

-- Tabla: cache_locks
-- Descripción: Bloqueos de caché
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);

-- Tabla: jobs
-- Descripción: Cola de trabajos en segundo plano
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY jobs_queue_index (queue)
);

-- Tabla: job_batches
-- Descripción: Lotes de trabajos
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);

-- Tabla: failed_jobs
-- Descripción: Trabajos fallidos
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================

