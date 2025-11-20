@echo off
cd "C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web"

echo Asignando rol admin...

php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); $user = App\Models\User::where('email', 'admin@gmail.com')->first(); if($user) { $user->assignRole('admin'); echo 'Rol asignado correctamente'; } else { echo 'Usuario no existe'; }"

echo.
pause

