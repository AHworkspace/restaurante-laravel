@echo off
echo ==========================================
echo DIAGNOSTICO DEL SISTEMA DE EMAIL
echo ==========================================
echo.

echo 1. Verificando procesos Java activos...
tasklist | findstr java
echo.

echo 2. Verificando conexion a Gmail...
echo Probando conexion POP3...
echo.

echo 3. Verificando base de datos...
echo Probando conexion PostgreSQL...
echo.

echo 4. Verificando archivos de configuracion...
if exist email_config.bat (
    echo email_config.bat: EXISTE
) else (
    echo email_config.bat: NO EXISTE
)

if exist lib\javax.mail.jar (
    echo javax.mail.jar: EXISTE
) else (
    echo javax.mail.jar: NO EXISTE
)

if exist lib\javax.activation.jar (
    echo javax.activation.jar: EXISTE
) else (
    echo javax.activation.jar: NO EXISTE
)

if exist lib\postgresql-42.6.0.jar (
    echo postgresql-42.6.0.jar: EXISTE
) else (
    echo postgresql-42.6.0.jar: NO EXISTE
)

echo.
echo 5. Verificando EmailSystemComplete.java...
if exist EmailSystemComplete.java (
    echo EmailSystemComplete.java: EXISTE
) else (
    echo EmailSystemComplete.java: NO EXISTE
)

echo.
echo ==========================================
echo DIAGNOSTICO COMPLETADO
echo ==========================================
pause






