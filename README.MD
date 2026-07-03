# PROYECTO FINAL SWI

## Reconstruccion del sistema ampliado de restaurante

Este repositorio es la base elegida para reconstruir la version amplia encontrada en el historial de Cursor (`proyecto_restaurante2`). La aplicacion web se encuentra en `sistema-web`.

Sectores recuperados e integrados:

- Consumidores con fuerza, institucion y grado/rango.
- Consumos por cliente, receta y tipo de comida.
- Pagos totales, parciales, por periodo y adelantos.
- Estado de cuenta e historial por consumidor.
- Configuracion con subsecciones de fuerzas, instituciones, grados y tipos de comida.
- Navegacion por roles y nuevos indicadores en el panel principal.

La base PostgreSQL existente conservaba las tablas y datos principales del sistema perdido. Las migraciones de reconstruccion reutilizan esas tablas y agregan solamente las estructuras faltantes.

### Ejecucion rapida

```powershell
cd C:\Users\20\Desktop\proyecto-software2_restaurante\sistema-web
php artisan migrate
npm run build
php artisan serve
```

Abrir `http://127.0.0.1:8000`. Para desarrollo del frontend se puede ejecutar `npm run dev` en otra terminal.

## SISTEMA INTELIGENTE PARA CONTROLAR INSUMOS EN ALMACEN DE COCINA

## 🚀 Instrucciones de Ejecución

### **Paso 1: Ejecutar el Sistema Web (Laravel)**

1. **Abrir una terminal y navegar a la carpeta del sistema-web:**
    ```bash
    cd sistema-web
    ```

2. **Instalar dependencias de PHP (si no lo has hecho):**
    ```bash
    composer install
    ```

3. **Instalar dependencias de Node.js (si no lo has hecho):**
    ```bash
    npm install
    ```

4. **Configurar el archivo `.env`:**
    - Copia `.env.example` a `.env` si no existe
    - Configura las variables de base de datos: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

5. **Generar la clave de la aplicación (si no lo has hecho):**
    ```bash
    php artisan key:generate
    ```

6. **Ejecutar migraciones (si no lo has hecho):**
    ```bash
    php artisan migrate
    ```

7. **Habilitar storage público (si no lo has hecho):**
    ```bash
    php artisan storage:link
    ```

8. **Iniciar el servidor web:**
    ```bash
    php artisan serve
    ```

✅ **El sistema web estará disponible en:** `http://localhost:8000`

---

### **Paso 2: Ejecutar el Servicio de IA (Flask)**

1. **Abrir una NUEVA terminal** (deja la anterior corriendo el sistema web)

2. **Navegar a la carpeta del servicio-ia:**
    ```bash
    cd servicio-ia
    ```

3. **Instalar dependencias de Python (si no lo has hecho):**
    ```bash
    pip install -r requirements.txt
    ```

4. **Iniciar el servicio Flask:**
    ```bash
    python flask_service.py
    ```

✅ **El servicio de IA estará disponible en:** `http://127.0.0.1:5000`

---

### **📝 Notas Importantes:**

- **Necesitas 2 terminales abiertas:** Una para el sistema web y otra para el servicio-ia
- **El sistema web debe estar corriendo** antes de usar las predicciones
- **El servicio-ia debe estar corriendo** para que funcionen las predicciones de desperdicios, insumos y recetas
- Si cambias el puerto del servicio-ia, actualiza la variable `HOST_MODELO_PREDICCION` en el `.env` del sistema-web

---

## Instalación Completa (Primera vez)

Si es la primera vez que ejecutas el proyecto, sigue estos pasos adicionales:

1. **Clonar el repositorio:**
    ```bash
    git clone https://github.com/tu-usuario/proyecto-final-sw1.git
    cd proyecto-final-sw1
    ```

2. **Seguir los pasos de "Ejecutar el Sistema Web" arriba**

3. **Seguir los pasos de "Ejecutar el Servicio de IA" arriba**

## Requisitos

- PHP >= 7.3
- Composer
- MySQL o cualquier otra base de datos compatible

## Ejecutar pruebas

Para ejecutar las pruebas, usa el siguiente comando:
```bash
php artisan test
```

## Plantilla de vistas

`https://demo.plainadmin.com/`

## Notas

- Importante habilitar protocolo ssl para poder usar la camara





###### #############################################################

## datos del gmail asignado a este programa

correo: scgrupo012tecnoweb@gmail.com
contraseña: SCgrupo12tecnoweb2025


## datos que pide la cuenta para este programa

nombre de aplicacion (para la cuenta): proyectotecnowebgrupo12
contraseña de aplicacion: fgfd kpuj hbhl wzpb


## Ejecución del "Servicio Gmail" (Procesamiento de comandos por correo)

El sistema permite ejecutar comandos mediante correos electrónicos enviados a `scgrupo012tecnoweb@gmail.com`.

### **✅ Método Principal (RECOMENDADO) - Sistema Java**

Este es el método que funciona correctamente. Ejecuta en tu terminal:

```bash
cd sistema-web
run_email_complete.bat
```

**¿Qué hace este comando?**
1. Compila el archivo Java `EmailSystemComplete.java` con las librerías necesarias
2. Se conecta al servidor POP3 de Gmail usando las credenciales configuradas
3. Lee continuamente los correos nuevos en la bandeja de entrada
4. Procesa los comandos encontrados en los correos
5. Se conecta a la base de datos MySQL para ejecutar las consultas
6. Envía respuestas automáticas al remitente con los resultados

**Nota:** Deja la terminal abierta ejecutándose. El programa revisará los correos periódicamente.

---

### **Alternativas (Requieren configuración adicional en .env):**

#### Opción 2: Laravel Scheduler - Procesamiento automático continuo
```bash
cd sistema-web
php artisan schedule:work
```
*Requiere configurar IMAP_HOST, IMAP_PORT, IMAP_USERNAME, IMAP_PASSWORD en .env*

#### Opción 3: Procesamiento manual Laravel (una sola vez)
```bash
cd sistema-web
php artisan email:process
```
*Requiere configurar IMAP_HOST, IMAP_PORT, IMAP_USERNAME, IMAP_PASSWORD en .env*

---

### **Comandos disponibles por email:**

Envía un correo a `scgrupo012tecnoweb@gmail.com` con los siguientes comandos en el asunto o cuerpo:

- `CONSULTAR INSUMOS` - Ver lista de insumos
- `CONSULTAR STOCK` - Ver estado del inventario
- `CONSULTAR MOVIMIENTOS` - Ver movimientos de inventario
- `CONSULTAR RECETAS` - Ver recetas disponibles
- `CONSULTAR VENTAS` - Ver reporte de ventas
- `CONSULTAR REPORTE` - Ver reportes generales
- `CONSULTAR PREDICCIONES` - Ver predicciones de IA
- `AGREGAR MOVIMIENTO` - Agregar entrada/salida
- `AYUDA` - Ver lista de comandos disponibles

---

### **Diagnóstico del sistema de email:**

Si tienes problemas con el sistema de correo, ejecuta el diagnóstico:

```bash
cd sistema-web
diagnostico_email.bat
```

Esto verificará que todas las librerías y archivos necesarios estén presentes.

---

### **Configuración del Sistema de Gmail:**

El sistema Java ya está configurado con las credenciales correctas:

**Librerías necesarias (ya incluidas en `/sistema-web/lib/`):**
- `javax.mail.jar` - Para conexión POP3/SMTP con Gmail
- `javax.activation.jar` - Soporte para tipos MIME
- `mysql-connector.jar` - Conexión con base de datos MySQL

**Credenciales configuradas en el código:**
- **Host POP3:** pop.gmail.com (puerto 995, SSL)
- **Host SMTP:** smtp.gmail.com (puerto 465, SSL)
- **Usuario:** scgrupo012tecnoweb@gmail.com
- **Contraseña:** fgfd kpuj hbhl wzpb (contraseña de aplicación)
- **Base de datos:** Conexión automática desde configuración Laravel

---

### **🔧 Solución de Problemas:**

**Si el comando no funciona:**

1. **Verifica que Java esté instalado:**
   ```bash
   java -version
   ```

2. **Verifica que todas las librerías existan:**
   ```bash
   cd sistema-web
   dir lib
   ```
   Debe mostrar: `javax.mail.jar`, `javax.activation.jar`, `mysql-connector.jar`

3. **Revisa el log de errores:** El programa mostrará en consola si hay problemas de conexión a Gmail o MySQL

4. **Verifica la conexión a Internet:** El sistema necesita conexión para acceder a Gmail

5. **Asegúrate que el servidor web Laravel esté corriendo:** El sistema necesita acceso a la base de datos configurada en Laravel

---

### **📨 Ejemplo de uso:**

1. Ejecuta el servicio GMAIL
   ```bash
   cd sistema-web
   run_email_complete.bat
   ```

2. Envía un correo a `scgrupo012tecnoweb@gmail.com` con el asunto o cuerpo:
   ```
   CONSULTAR INSUMOS
   ```

3. El sistema procesará el comando y responderá automáticamente al remitente con la lista de insumos disponibles.

4. Revisa la terminal para ver el log de procesamiento:
   ```
   ✓ Email recibido de: tu_correo@example.com
   ✓ Comando detectado: CONSULTAR INSUMOS
   ✓ Consultando base de datos...
   ✓ Respuesta enviada
   ```

---






 4. INDICACIONES SOBRE LOS COMANDOS DE CADA CASO DE USO



## 📋 LISTADO COMPLETO DE COMANDOS POR CASO DE USO

### **CU1 - GESTIÓN DE USUARIOS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 278-490)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 400-950)
- `sistema-web/resources/views/livewire/usuario.blade.php` (Web UI)
- `sistema-web/app/Http/Controllers/UserController.php` (Web Controller)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CREAR USUARIO** | `CREAR USUARIO [nombre] [apellido_p] [apellido_m] [email] [password]` | Crea un nuevo usuario en el sistema |
| **CONSULTAR USUARIOS** | `CONSULTAR USUARIOS` | Lista todos los usuarios con sus roles |
| **ACTUALIZAR USUARIO** | `ACTUALIZAR USUARIO [id] [campo] [valor]` | Actualiza campos de un usuario<br>**Campos:** nombre, apellido_paterno, apellido_materno, email, password |
| **ELIMINAR USUARIO** | `ELIMINAR USUARIO [id]` | Elimina un usuario del sistema |
| **ASIGNAR ROL** | `ASIGNAR ROL [id] [rol]` | Asigna un rol a un usuario<br>**Roles:** admin, director, cocinero, cajero, ayudante_cocina, mesero |

**Ejemplo:**
```
CREAR USUARIO Juan Perez Lopez juan@mail.com Pass1234
CONSULTAR USUARIOS
ACTUALIZAR USUARIO 5 email nuevo@mail.com
ASIGNAR ROL 5 cocinero
ELIMINAR USUARIO 10
```

---

### **CU2 - GESTIÓN DE INSUMOS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 600-1100)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 1200-2100)
- `sistema-web/app/Http/Controllers/InsumoController.php` (Web Controller)
- `sistema-web/resources/views/insumos/index.blade.php` (Web UI)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CONSULTAR INSUMOS** | `CONSULTAR INSUMOS [filtro] [valor], [filtro] [valor]...` | Lista insumos con filtros opcionales<br>**Filtros:** insumo, categoria, stock_minimo |
| **CONSULTAR STOCK DISPONIBLE** | `CONSULTAR STOCK DISPONIBLE [nombre_insumo]` | Muestra stock actual y mínimo de un insumo |
| **CREAR INSUMO** | `CREAR INSUMO [nombre] [descripcion] [stock_minimo] [categoria] [unidad]` | Crea un nuevo insumo |
| **ACTUALIZAR INSUMO** | `ACTUALIZAR INSUMO [nombre] [campo] [valor], [campo] [valor]...` | Actualiza campos de un insumo<br>**Campos:** nombre, descripcion, stock_minimo, categoria, unidad |
| **ELIMINAR INSUMO** | `ELIMINAR INSUMO [nombre]` | Elimina un insumo (verifica asociaciones) |

**Ejemplo:**
```
CONSULTAR INSUMOS
CONSULTAR INSUMOS categoria Verduras, stock_minimo 10
CONSULTAR STOCK DISPONIBLE tomate
CREAR INSUMO cebolla cebolla blanca 5 Verduras kilogramo
ACTUALIZAR INSUMO tomate stock_minimo 15, descripcion tomate rojo fresco
ELIMINAR INSUMO cebolla
```

---

### **CU3 - GESTIÓN DE RECETAS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 1150-1850)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 2150-3200)
- `sistema-web/app/Http/Controllers/RecetaController.php` (Web Controller)
- `sistema-web/resources/views/recetas/index.blade.php` (Web UI)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CONSULTAR LISTA RECETAS** | `CONSULTAR LISTA RECETAS` | Lista todas las recetas (sin detalles) |
| **CONSULTAR RECETA** | `CONSULTAR RECETA [nombre]` | Muestra detalles completos de una receta |
| **CREAR RECETA** | `CREAR RECETA [nombre] [precio] [tiempo]` | Crea una nueva receta<br>**Tiempo:** en minutos |
| **AÑADIR INDICACIONES** | `AÑADIR INDICACIONES [receta] [texto]` | Agrega/actualiza indicaciones de preparación |
| **AGREGAR INGREDIENTES** | `AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...` | Agrega múltiples ingredientes |
| **QUITAR INGREDIENTE** | `QUITAR INGREDIENTE [receta] [insumo]` | Elimina un ingrediente de la receta |
| **ACTUALIZAR RECETA** | `ACTUALIZAR RECETA [nombre] [campo] [valor], [campo] [valor]...` | Actualiza campos de una receta<br>**Campos:** nombre, precio, tiempo_preparacion, indicaciones |
| **ELIMINAR RECETA** | `ELIMINAR RECETA [nombre]` | Elimina una receta completa |

**Ejemplo:**
```
CREAR RECETA Salteñas 10 60
AÑADIR INDICACIONES Salteñas Mezclar ingredientes y hornear 30 minutos
AGREGAR INGREDIENTES Salteñas harina 500, huevo 3, mantequilla 100
CONSULTAR RECETA Salteñas
ACTUALIZAR RECETA Salteñas precio 12, tiempo_preparacion 45
QUITAR INGREDIENTE Salteñas mantequilla
ELIMINAR RECETA Salteñas
```

---

### **CU4 - GESTIÓN DE INVENTARIOS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 1900-2400)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 3250-4100)
- `sistema-web/app/Http/Controllers/MovimientoInventarioController.php` (Web Controller)
- `sistema-web/resources/views/movimientos/index.blade.php` (Web UI)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CREAR MOVIMIENTO** | `CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]` | Registra un movimiento de inventario<br>**Tipos:** entrada, salida |
| **CONSULTAR MOVIMIENTOS** | `CONSULTAR MOVIMIENTOS [filtro] [valor], [filtro] [valor]...` | Lista movimientos con filtros<br>**Filtros:** tipo, insumo, fecha, fecha_desde, fecha_hasta, motivo |
| **ACTUALIZAR MOVIMIENTO** | `ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor]` | Actualiza un movimiento específico<br>**Campos:** tipo, cantidad, motivo, fecha |
| **ELIMINAR MOVIMIENTO** | `ELIMINAR MOVIMIENTO [nombre_insumo] [id]` | Elimina un movimiento específico |

**Ejemplo:**
```
CREAR MOVIMIENTO tomate entrada 50 Compra semanal
CONSULTAR MOVIMIENTOS
CONSULTAR MOVIMIENTOS tipo entrada, fecha_desde 2025-11-1, fecha_hasta 2025-11-30
ACTUALIZAR MOVIMIENTO tomate 15 cantidad 60, motivo Compra mensual
ELIMINAR MOVIMIENTO tomate 15
```

---

### **CU5 - GESTIÓN DE PRODUCCIÓN (Planes)**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 2450-2900)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 4150-4850)
- `sistema-web/resources/views/livewire/calculoinsumo.blade.php` (Web UI Livewire)
- `sistema-web/app/Models/PlanProduccion.php` (Modelo)
- `sistema-web/database/migrations/2025_11_03_000000_create_planes_produccion_table.php` (Migración)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CREAR PLAN PRODUCCION** | `CREAR PLAN PRODUCCION [nombre_plan] [receta] [cantidad]` | Crea un plan de producción con cálculo de insumos |
| **CONSULTAR PLANES PRODUCCION** | `CONSULTAR PLANES PRODUCCION [filtro] [valor], [filtro] [valor]...` | Lista planes con filtros<br>**Filtros:** nombre, receta, cantidad, fecha, fecha_desde, fecha_hasta |
| **CONSULTAR PLAN PRODUCCION** | `CONSULTAR PLAN PRODUCCION [nombre_plan] [id]` | Muestra detalles y cálculo de insumos de un plan |
| **ACTUALIZAR PLAN PRODUCCION** | `ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]` | Actualiza un plan<br>**Campos:** nombre, receta, cantidad |
| **ELIMINAR PLAN PRODUCCION** | `ELIMINAR PLAN PRODUCCION [nombre_plan] [id]` | Elimina un plan de producción |

**Ejemplo:**
```
CREAR PLAN PRODUCCION PlanSemana1 Pizza 50
CONSULTAR PLANES PRODUCCION
CONSULTAR PLANES PRODUCCION receta Pizza, fecha_desde 2025-11-1
CONSULTAR PLAN PRODUCCION PlanSemana1 1
ACTUALIZAR PLAN PRODUCCION PlanSemana1 1 cantidad 60
ELIMINAR PLAN PRODUCCION PlanSemana1 1
```

---

### **CU6 - GESTIÓN DE VENTAS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 2950-3400)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 4900-5450)
- `sistema-web/app/Http/Controllers/VentaController.php` (Web Controller)
- `sistema-web/resources/views/ventas/index.blade.php` (Web UI)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CREAR VENTA** | `CREAR VENTA [receta] [cantidad]` | Registra una venta (precio automático de la receta)<br>Crea movimientos de salida automáticamente |
| **CONSULTAR VENTAS** | `CONSULTAR VENTAS [filtro] [valor], [filtro] [valor]...` | Lista ventas con filtros<br>**Filtros:** receta, cantidad, precio, total, fecha, fecha_desde, fecha_hasta |
| **ACTUALIZAR VENTA** | `ACTUALIZAR VENTA [receta] [id] [campo] [valor]` | Actualiza una venta<br>**Campos:** cantidad, precio, receta |
| **ELIMINAR VENTA** | `ELIMINAR VENTA [receta] [id]` | Elimina una venta y sus movimientos asociados |

**Ejemplo:**
```
CREAR VENTA Pizza 3
CONSULTAR VENTAS
CONSULTAR VENTAS receta Pizza, fecha_desde 2025-11-1, fecha_hasta 2025-11-30
ACTUALIZAR VENTA Pizza 10 cantidad 5
ELIMINAR VENTA Pizza 10
```



---

### **CU7 - GESTIÓN DE PAGOS**

**Estado:** ⚠️ **EN DESARROLLO**

**Archivos relacionados:**
- `sistema-web/app/Models/Compra.php` (Modelo)
- `sistema-web/database/migrations/2025_11_03_200000_add_fields_to_compras.php` (Migración)

**Nota:** Este módulo está pendiente de implementación completa.

---



### **CU8 - GESTIÓN DE REPORTES DE VENTAS**

**Archivos relacionados:**
- `sistema-web/app/Services/EmailCommandParser.php` (PHP - líneas 3150-3500)
- `sistema-web/EmailSystemComplete.java` (Java - líneas 2300-2530)
- `sistema-web/app/Http/Controllers/ReporteController.php` (Web Controller - métodos CRUD)
- `sistema-web/resources/views/livewire/reportes-guardar.blade.php` (Web UI Livewire)
- `sistema-web/resources/views/reportes/pdf_ventas.blade.php` (Plantilla PDF)
- `sistema-web/app/Models/ReportePersonalizado.php` (Modelo)
- `sistema-web/database/migrations/2025_11_03_043342_create_reportes_personalizados_table.php` (Migración)

**Comandos disponibles:**

| Comando | Sintaxis | Descripción |
|---------|----------|-------------|
| **CREAR REPORTE** | `CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]` | Genera un reporte de ventas<br>• Sin `guardar`: solo muestra<br>• Con `guardar`: muestra y guarda en BD |
| **CONSULTAR REPORTE** | `CONSULTAR REPORTE [nombre] [id]` | Consulta reportes guardados<br>• Sin parámetros: lista todos<br>• Con parámetros: muestra detalle específico |
| **GENERAR REPORTE** | `GENERAR REPORTE [nombre] [id]` | Genera PDF y lo envía por email como adjunto<br>Incluye: estadísticas, gráficos de barras, Top 5, tabla de ventas |
| **ACTUALIZAR REPORTE** | `ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]` | Actualiza un reporte guardado<br>**Campos:** nombre, fecha_desde, fecha_hasta, descripcion |
| **ELIMINAR REPORTE** | `ELIMINAR REPORTE [nombre] [id]` | Elimina un reporte guardado |

**Ejemplo:**
```
CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30
CREAR REPORTE VentasOctubre 2025-10-1 2025-10-31 guardar
CONSULTAR REPORTE
CONSULTAR REPORTE VentasOctubre 1
GENERAR REPORTE VentasOctubre 1
ACTUALIZAR REPORTE VentasOctubre 1 fecha_hasta 2025-11-5
ELIMINAR REPORTE VentasOctubre 1
```

---

## 🔧 FUNCIONALIDADES ESPECIALES

### **Normalización de Fechas**
El sistema acepta fechas en formato flexible:
- `2025-11-1` → Se convierte a `2025-11-01`
- `2025-6-15` → Se convierte a `2025-06-15`

Implementado en:
- `EmailCommandParser.php` (método `normalizarFecha()`)
- `EmailSystemComplete.java` (método `normalizarFecha()`)

### **Filtros Combinables**
Varios comandos permiten combinar múltiples filtros con coma:
```
CONSULTAR INSUMOS categoria Verduras, stock_minimo 10
CONSULTAR MOVIMIENTOS tipo entrada, fecha_desde 2025-11-1, fecha_hasta 2025-11-30
CONSULTAR VENTAS receta Pizza, total 100
```

### **Generación de PDF**
El comando `GENERAR REPORTE` crea un PDF profesional con:
- ✅ 4 tarjetas de estadísticas coloridas
- ✅ Gráfico de barras Top 5 productos
- ✅ Gráfico de barras de ventas por día
- ✅ Tabla detallada de todas las ventas
- ✅ Se envía como adjunto por email

---

## 📧 SISTEMA DE COMANDOS POR EMAIL

### **Cómo funciona:**

1. **Envía un email** a: `scgrupo012tecnoweb@gmail.com`
2. **Asunto del email:** El comando completo (ej: `CONSULTAR USUARIOS`)
3. **Cuerpo:** Puede estar vacío
4. **Respuesta:** Recibes un email automático con el resultado
5. **PDF:** Si usas `GENERAR REPORTE`, recibes el PDF como adjunto

### **Sistema en ejecución:**

**Terminal/Consola:**
```bash
cd sistema-web
run_email_complete.bat
```

Verás:
```
🚀 SISTEMA COMPLETO DE EMAIL CON BASE DE DATOS
📧 Email: scgrupo012tecnoweb@gmail.com
🗄️ Base de datos: jdbc:mysql://127.0.0.1:3306/proyecto_final_sw1

🔄 Iniciando ciclo de verificación...
📨 Email recibido:
   De: usuario@example.com
   Asunto: CONSULTAR USUARIOS
💬 Respuesta enviada exitosamente
⏰ Esperando 10 segundos...
```

### **Comando de Ayuda:**

Envía un email con asunto: **`AYUDA`**

Recibirás la lista completa de todos los comandos organizados por caso de uso.

---

## 🔐 SEGURIDAD Y VALIDACIONES

- ✅ Validación de roles (Spatie Permissions)
- ✅ Validación de emails únicos
- ✅ Validación de passwords (mínimo 8 caracteres)
- ✅ Verificación de existencia (usuarios, insumos, recetas)
- ✅ Verificación de asociaciones antes de eliminar
- ✅ Normalización automática de fechas
- ✅ Registro en historial de todas las acciones

---

## 📊 SINCRONIZACIÓN WEB ↔ GMAIL

**Todas las funcionalidades están 100% sincronizadas:**

- ✅ Lo que haces en la **web** se refleja en **Gmail**
- ✅ Lo que haces por **Gmail** se refleja en la **web**
- ✅ Misma base de datos
- ✅ Mismo historial de acciones
- ✅ Mismas validaciones

**Ejemplo:**
1. Creas un usuario por Gmail → Aparece en la web
2. Creas una venta en la web → Puedes consultarla por Gmail
3. Guardas un reporte en la web → Puedes generarlo por Gmail

---

## 📦 TECNOLOGÍAS UTILIZADAS

### **Backend:**
- **Laravel 11.41.3** (PHP 8.3.20)
- **Java** (JavaMail + JDBC)
- **MySQL** (Base de datos)
- **Livewire Volt** (Componentes reactivos)
- **Spatie Permissions** (Roles y permisos)

### **Frontend:**
- **Blade Templates** (Laravel)
- **Chart.js** (Gráficos web)
- **Bootstrap 5** (UI Framework)
- **Tailwind CSS** (Estilos)

### **Generación de PDF:**
- **Barryvdh DomPDF** (Laravel)
- **CSS puro** (Gráficos de barras)

### **Email:**
- **JavaMail API** (Lectura POP3 + Envío SMTP)
- **Gmail** (Servidor de correo)

---

**✅ DOCUMENTACIÓN COMPLETA ACTUALIZADA EN README.MD** 📚































**Iniciar el servidor-web:**
    ```bash

    cd sistema-web
    php artisan serve


. **Iniciar el servicio-ia:**
    ```bash
    cd servicio-ia
    python flask_service.py
