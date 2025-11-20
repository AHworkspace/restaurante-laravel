@echo off
cd "C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web"

echo === VERIFICACION DE ROLES ===
echo.

php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); $relaciones = DB::table('model_has_roles')->join('roles', 'model_has_roles.role_id', '=', 'roles.id')->join('usuarios', 'model_has_roles.model_id', '=', 'usuarios.id')->select('usuarios.email', 'roles.name as rol')->get(); echo 'USUARIOS Y SUS ROLES:' . PHP_EOL; foreach($relaciones as $r) { echo '  ' . $r->email . ' -> ' . $r->rol . PHP_EOL; }"

echo.
pause

