<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# PROYECTO FINAL SWI

## SISTEMA INTELIGENTE PARA CONTROLAR INSUMOS EN ALMACEN DE COCINA

## Instalación

Sigue estos pasos para instalar y configurar el proyecto Laravel:

1. **Clonar el repositorio:**
    ```bash
    git clone https://github.com/tu-usuario/proyecto-final-sw1.git
    cd proyecto-final-sw1
    ```

2. **Instalar dependencias:**
    Asegúrate de tener [Composer](https://getcomposer.org/) instalado y ejecuta:
    ```bash
    composer install
    ```

3. **Configurar el archivo `.env`:**
    Copia el archivo `.env.example` a `.env` y configura tus variables de entorno:
    ```bash
    cp .env.example .env
    ```

4. **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

5. **Configurar la base de datos:**
    En el archivo `.env`, configura las variables `DB_DATABASE`, `DB_USERNAME`, y `DB_PASSWORD` con tus credenciales de base de datos.

6. **Migrar la base de datos:**
    ```bash
    php artisan migrate
    ```
    
7. **Habilitar storage publico:**
    ```bash
    php artisan storage:link
    ```

8. **Iniciar el servidor de desarrollo:**
    ```bash
    php artisan serve
    ```

Ahora puedes acceder a tu aplicación en `http://localhost:8000`.

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