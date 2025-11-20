# 📚 INSTRUCCIONES PARA LA BASE DE DATOS

## Sistema de Gestión de Restaurante - TecnoWeb

---

## 📍 UBICACIÓN DE LOS ARCHIVOS

En tu proyecto Laravel, la base de datos se define mediante:

1. **Migraciones**: `sistema-web/database/migrations/`
2. **Seeders**: `sistema-web/database/seeders/`
3. **Configuración**: `sistema-web/config/database.php`
4. **Variables de entorno**: `sistema-web/.env`

---

## 🚀 MÉTODOS PARA CREAR LA BASE DE DATOS

### Método 1: Usando Laravel (RECOMENDADO)

Este es el método estándar de Laravel y el más recomendado:

```bash
# Navegar al directorio del sistema web
cd sistema-web

# Ejecutar las migraciones (crea todas las tablas)
php artisan migrate

# Poblar con datos de prueba (opcional)
php artisan db:seed

# O ejecutar todo junto (migrar y sembrar)
php artisan migrate:fresh --seed
```

**Ventajas:**
- ✅ Mantiene el control de versiones de la base de datos
- ✅ Fácil de revertir cambios
- ✅ Compatible con múltiples motores de BD
- ✅ Integrado con el sistema

---

### Método 2: Usando el Script SQL Directo

Si prefieres trabajar directamente con SQL:

#### Para MySQL/MariaDB:

```bash
# Conectar a MySQL
mysql -u root -p

# Crear la base de datos
CREATE DATABASE restaurante_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Usar la base de datos
USE restaurante_db;

# Ejecutar el script
SOURCE SCRIPT_BASE_DATOS_COMPLETA.sql;

# O desde la terminal:
mysql -u root -p restaurante_db < SCRIPT_BASE_DATOS_COMPLETA.sql
```

#### Para PostgreSQL:

```bash
# Conectar a PostgreSQL
psql -U postgres

# Crear la base de datos
CREATE DATABASE restaurante_db WITH ENCODING 'UTF8';

# Conectar a la base de datos
\c restaurante_db

# Ejecutar el script (necesitarás adaptarlo a sintaxis PostgreSQL)
\i SCRIPT_BASE_DATOS_COMPLETA.sql
```

---

## ⚙️ CONFIGURACIÓN DE LA BASE DE DATOS

### 1. Configurar el archivo `.env`

Edita el archivo `sistema-web/.env`:

#### Para SQLite (por defecto):
```env
DB_CONNECTION=sqlite
DB_DATABASE=C:\ruta\completa\al\database.sqlite
```

#### Para MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurante_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

#### Para PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restaurante_db
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 2. Limpiar caché de configuración

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🔄 COMANDOS ÚTILES DE LARAVEL

### Gestión de Migraciones

```bash
# Ver estado de las migraciones
php artisan migrate:status

# Ejecutar migraciones pendientes
php artisan migrate

# Revertir la última migración
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset

# Rehacer migraciones (rollback + migrate)
php artisan migrate:refresh

# Eliminar todas las tablas y recrear desde cero
php artisan migrate:fresh

# Recrear y poblar con datos
php artisan migrate:fresh --seed
```

### Gestión de Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=InsumoSeeder
php artisan db:seed --class=RecetaSeeder
```

---

## 🗃️ CREAR LA BASE DE DATOS DESDE CERO

### Opción A: Proceso Completo con Laravel

```bash
# 1. Navegar al directorio
cd sistema-web

# 2. Copiar el archivo de entorno (si no existe)
cp .env.example .env

# 3. Generar la clave de la aplicación
php artisan key:generate

# 4. Crear el archivo de base de datos SQLite (si usas SQLite)
touch database/database.sqlite

# 5. Ejecutar migraciones
php artisan migrate

# 6. Crear usuario administrador y datos iniciales
php artisan db:seed

# 7. Verificar que todo funciona
php artisan serve
```

### Opción B: Con Script SQL Manual

```bash
# 1. Crear la base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE restaurante_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Ejecutar el script SQL
mysql -u root -p restaurante_db < SCRIPT_BASE_DATOS_COMPLETA.sql

# 3. Configurar el .env
# (editar manualmente con los datos de conexión)

# 4. Limpiar caché
cd sistema-web
php artisan config:clear

# 5. Verificar conexión
php artisan migrate:status
```

---

## 🛠️ SOLUCIÓN DE PROBLEMAS COMUNES

### Error: "Database does not exist"
```bash
# Para SQLite
touch sistema-web/database/database.sqlite

# Para MySQL
mysql -u root -p -e "CREATE DATABASE restaurante_db;"
```

### Error: "Access denied for user"
```bash
# Verificar credenciales en .env
# Otorgar permisos en MySQL:
mysql -u root -p
GRANT ALL PRIVILEGES ON restaurante_db.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Class not found" o "Migration not found"
```bash
# Regenerar autoload
composer dump-autoload

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Error: "Foreign key constraint fails"
```bash
# Ejecutar migraciones en orden correcto
php artisan migrate:fresh

# O desactivar temporalmente las foreign keys (solo para desarrollo)
# En MySQL:
SET FOREIGN_KEY_CHECKS=0;
# ... ejecutar operaciones ...
SET FOREIGN_KEY_CHECKS=1;
```

---

## 📊 VERIFICAR LA ESTRUCTURA

### En Laravel:
```bash
# Ver todas las tablas
php artisan db:show

# Ver estructura de una tabla
php artisan db:table users
php artisan db:table insumos
```

### En MySQL:
```sql
-- Ver todas las tablas
SHOW TABLES;

-- Ver estructura de una tabla
DESCRIBE users;
DESCRIBE insumos;

-- Ver relaciones (foreign keys)
SELECT 
    TABLE_NAME, 
    COLUMN_NAME, 
    CONSTRAINT_NAME, 
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'restaurante_db';
```

---

## 🔐 CREAR USUARIOS Y ROLES INICIALES

Después de crear la base de datos, necesitas crear roles y usuarios:

```bash
# Opción 1: Usar el seeder (si existe)
php artisan db:seed --class=RoleSeeder

# Opción 2: Usar tinker (consola interactiva de Laravel)
php artisan tinker

# Dentro de tinker:
>>> use App\Models\User;
>>> use Spatie\Permission\Models\Role;
>>> use Spatie\Permission\Models\Permission;

# Crear roles
>>> $admin = Role::create(['name' => 'Administrador']);
>>> $gerente = Role::create(['name' => 'Gerente']);
>>> $empleado = Role::create(['name' => 'Empleado']);

# Crear un usuario administrador
>>> $user = User::create([
...     'nombre' => 'Admin',
...     'apellido_paterno' => 'Sistema',
...     'apellido_materno' => '',
...     'email' => 'admin@restaurante.com',
...     'password' => bcrypt('admin123')
... ]);

# Asignar rol
>>> $user->assignRole('Administrador');

# Salir
>>> exit
```

---

## 📈 RESPALDO Y RESTAURACIÓN

### Hacer respaldo (Backup):

```bash
# MySQL
mysqldump -u root -p restaurante_db > backup_$(date +%Y%m%d).sql

# SQLite
cp database/database.sqlite backup_database_$(date +%Y%m%d).sqlite
```

### Restaurar desde respaldo:

```bash
# MySQL
mysql -u root -p restaurante_db < backup_20251103.sql

# SQLite
cp backup_database_20251103.sqlite database/database.sqlite
```

---

## 🔗 ENLACES ÚTILES

- **Documentación de Laravel Migrations**: https://laravel.com/docs/migrations
- **Spatie Laravel Permission**: https://spatie.be/docs/laravel-permission
- **Laravel Database Seeding**: https://laravel.com/docs/seeding

---

## 📝 NOTAS FINALES

1. **Desarrollo**: Usa `migrate:fresh --seed` para resetear la BD con datos de prueba
2. **Producción**: NUNCA uses `migrate:fresh`, solo `migrate`
3. **Respaldos**: Haz respaldos regulares antes de cambios importantes
4. **Permisos**: Asegúrate de que Laravel tenga permisos de escritura en `storage/` y `bootstrap/cache/`
5. **Seguridad**: Nunca subas el archivo `.env` a control de versiones

---

**Última actualización**: Noviembre 2025  
**Proyecto**: Sistema de Gestión de Restaurante - TecnoWeb

