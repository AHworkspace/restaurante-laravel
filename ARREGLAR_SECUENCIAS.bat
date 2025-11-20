@echo off
cd "C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web"

echo === ARREGLANDO SECUENCIAS DE POSTGRESQL ===
echo.

php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); $tablas = ['usuarios', 'unidad_medidas', 'categorias', 'proveedores', 'insumos', 'recetas', 'recetas_insumos', 'ventas', 'compras', 'movimiento_inventarios', 'planes_produccion', 'reportes_personalizados', 'historial', 'roles', 'permissions']; foreach($tablas as $tabla) { try { $max = DB::table($tabla)->max('id'); if($max) { DB::statement(\"SELECT setval(pg_get_serial_sequence('$tabla', 'id'), $max);\"); echo \"$tabla: secuencia actualizada a $max\" . PHP_EOL; } } catch(Exception $e) { echo \"$tabla: sin secuencia\" . PHP_EOL; } }"

echo.
echo === SECUENCIAS ARREGLADAS ===
pause

