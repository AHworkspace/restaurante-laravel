@echo off
echo ========================================
echo SISTEMA COMPLETO DE EMAIL CON BASE DE DATOS
echo ========================================
echo.

REM Cambiar al directorio correcto
cd /d "%~dp0"

REM Compilar el sistema
echo Compilando sistema...
javac -encoding UTF-8 -cp "lib\javax.mail.jar;lib\javax.activation.jar;lib\postgresql-42.6.0.jar;." EmailSystemComplete.java

if %errorlevel% neq 0 (
    echo Error en la compilacion
    pause
    exit /b 1
)

echo Compilacion exitosa
echo.

REM Ejecutar el sistema
echo Iniciando sistema completo...
echo.
java -cp "lib\javax.mail.jar;lib\javax.activation.jar;lib\postgresql-42.6.0.jar;." EmailSystemComplete

pause
