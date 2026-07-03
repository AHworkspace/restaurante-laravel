<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PrediccionesController;
use App\Http\Controllers\ReporteController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ConsumidorController;
use App\Http\Controllers\ConsumoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ConfiguracionConsumidoresController;
use App\Http\Controllers\MenuDiaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionSistemaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoProduccionController;
use App\Http\Controllers\FormatoEmpaqueController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login-cliente',fn()=>redirect()->route('login'))->name('cliente.login');
Route::post('/login-cliente',[App\Http\Controllers\Auth\Cliente\LoginController::class,'login'])->name('cliente.login.store');
Route::get('/registro-cliente',[App\Http\Controllers\Auth\Cliente\RegisterController::class,'showRegistrationForm'])->name('cliente.register');
Route::post('/registro-cliente',[App\Http\Controllers\Auth\Cliente\RegisterController::class,'register'])->name('cliente.register.store')->middleware('throttle:10,1');
Route::post('/logout-cliente',[App\Http\Controllers\Auth\Cliente\LoginController::class,'logout'])->name('cliente.logout');
Route::middleware('auth:cliente')->group(function(){
    Route::get('/cliente/inicio',[ClienteController::class,'inicio'])->name('cliente.inicio');
    Route::get('/cliente/consumos',[ClienteController::class,'consumos'])->name('cliente.consumos');
    Route::get('/cliente/pagos',[ClienteController::class,'pagos'])->name('cliente.pagos');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/categorias/insumos', [CategoriaController::class, 'indexInsumos'])->name('categorias.insumos.index')->middleware('auth');
Route::get('/categorias/unidades', [UnidadMedidaController::class, 'index'])->name('categorias.unidades.index')->middleware('auth');
Route::get('/categorias/rangos', [CategoriaController::class, 'indexRangos'])->name('categorias.rangos.index')->middleware('auth');

// Rutas para recursos
Route::resources([
    'categorias' => CategoriaController::class,
    'insumos' => InsumoController::class,
    'unidades' => UnidadMedidaController::class,
    'proveedores' => ProveedorController::class,
    'movimientos' => MovimientoInventarioController::class,
    'recetas' => RecetaController::class,
    'ventas' => VentaController::class,
]);
Route::resource('marcas',MarcaController::class)->only(['index','store','update','destroy'])->middleware('auth');
Route::resource('formatos-empaque',FormatoEmpaqueController::class)->parameters(['formatos-empaque'=>'formatoEmpaque'])->only(['index','store','update','destroy'])->middleware('auth');
Route::resource('tipos-produccion',TipoProduccionController::class)->parameters(['tipos-produccion'=>'tipoProduccion'])->only(['index','store','update','destroy'])->middleware('auth');
Route::post('insumos/{insumo}/presentaciones',[InsumoController::class,'storePresentacion'])->name('insumos.presentaciones.store')->middleware('auth');
Route::get('insumos/{insumo}/presentaciones/crear',[InsumoController::class,'createPresentacion'])->name('insumos.presentaciones.create')->middleware('auth');
Route::get('insumos/{insumo}/presentaciones/{presentacion}/editar',[InsumoController::class,'editPresentacion'])->name('insumos.presentaciones.edit')->middleware('auth');
Route::put('insumos/{insumo}/presentaciones/{presentacion}',[InsumoController::class,'updatePresentacion'])->name('insumos.presentaciones.update')->middleware('auth');
Route::delete('insumos/{insumo}/presentaciones/{presentacion}',[InsumoController::class,'destroyPresentacion'])->name('insumos.presentaciones.destroy')->middleware('auth');

Route::get('consumidores/buscar', [ConsumidorController::class, 'buscar'])
    ->name('consumidores.buscar')->middleware('auth');
Route::resource('consumidores', ConsumidorController::class)
    ->parameters(['consumidores' => 'consumidor'])
    ->middleware('auth');
Route::resource('consumos', ConsumoController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy'])
    ->middleware('auth');
Route::get('consumidores/{consumidor}/consumos-pendientes', [ConsumoController::class, 'pendientes'])
    ->name('consumidores.consumos-pendientes')
    ->middleware('auth');
Route::get('consumos/{consumidor}/pendientes', [ConsumoController::class, 'pendientes'])
    ->name('consumos.pendientes')->middleware('auth');
Route::get('pagos/consumos-periodo', [PagoController::class, 'consumosPeriodo'])
    ->name('pagos.consumos-periodo')->middleware('auth');
Route::resource('pagos', PagoController::class)
    ->only(['index', 'create', 'store', 'show'])
    ->middleware('auth');
Route::prefix('configuraciones')->name('configuraciones.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('roles', [ConfiguracionSistemaController::class, 'roles'])->name('roles');
    Route::post('roles', [ConfiguracionSistemaController::class, 'guardarRol'])->name('roles.store');
    Route::put('roles/{role}', [ConfiguracionSistemaController::class, 'actualizarRol'])->name('roles.update');
    Route::get('permisos', [ConfiguracionSistemaController::class, 'permisos'])->name('permisos');
    Route::put('permisos/{role}', [ConfiguracionSistemaController::class, 'guardarPermisos'])->name('permisos.update');
    Route::get('diseno', [ConfiguracionSistemaController::class, 'diseno'])->name('diseno');
    Route::put('diseno', [ConfiguracionSistemaController::class, 'guardarDiseno'])->name('diseno.update');
    Route::delete('diseno', [ConfiguracionSistemaController::class, 'restablecerDiseno'])->name('diseno.reset');
});
Route::post('menus-dia/{menuDia}/toggle-visible', [MenuDiaController::class, 'toggleVisible'])
    ->name('menus-dia.toggle-visible')->middleware('auth');
Route::post('menus-dia/{menuDia}/update-titulo', [MenuDiaController::class, 'updateTituloExisting'])
    ->name('menus-dia.update-titulo')->middleware('auth');
Route::post('menus-dia/{menuDia}/eliminar-titulo', [MenuDiaController::class, 'eliminarTitulo'])
    ->name('menus-dia.eliminar-titulo')->middleware('auth');
Route::resource('menus-dia', MenuDiaController::class)
    ->parameters(['menus-dia' => 'menuDia'])
    ->middleware('auth');
Route::post('compras/{compra}/abono-proveedor',[CompraController::class,'registrarAbonoProveedor'])->name('compras.abono-proveedor')->middleware('auth');
Route::resource('compras',CompraController::class)->only(['index','create','store','show'])->middleware('auth');

Route::middleware('auth')->group(function(){
    Route::get('fuerzas',[ConfiguracionConsumidoresController::class,'fuerzas'])->name('fuerzas.index');
    Route::post('fuerzas',[ConfiguracionConsumidoresController::class,'storeFuerza'])->name('fuerzas.store');
    Route::put('fuerzas/{id}',[ConfiguracionConsumidoresController::class,'updateFuerza'])->name('fuerzas.update');
    Route::delete('fuerzas/{id}',[ConfiguracionConsumidoresController::class,'destroyFuerza'])->name('fuerzas.destroy');
    Route::get('instituciones',[ConfiguracionConsumidoresController::class,'instituciones'])->name('instituciones.index');
    Route::post('instituciones',[ConfiguracionConsumidoresController::class,'storeInstitucion'])->name('instituciones.store');
    Route::put('instituciones/{id}',[ConfiguracionConsumidoresController::class,'updateInstitucion'])->name('instituciones.update');
    Route::delete('instituciones/{id}',[ConfiguracionConsumidoresController::class,'destroyInstitucion'])->name('instituciones.destroy');
    Route::get('grados',[ConfiguracionConsumidoresController::class,'grados'])->name('grados.index');
    Route::post('grados',[ConfiguracionConsumidoresController::class,'storeGrado'])->name('grados.store');
    Route::put('grados/{id}',[ConfiguracionConsumidoresController::class,'updateGrado'])->name('grados.update');
    Route::delete('grados/{id}',[ConfiguracionConsumidoresController::class,'destroyGrado'])->name('grados.destroy');
    Route::get('tipos-comida',[ConfiguracionConsumidoresController::class,'tiposComida'])->name('tipos-comida.index');
    Route::post('tipos-comida',[ConfiguracionConsumidoresController::class,'storeTipoComida'])->name('tipos-comida.store');
    Route::put('tipos-comida/{id}',[ConfiguracionConsumidoresController::class,'updateTipoComida'])->name('tipos-comida.update');
    Route::delete('tipos-comida/{id}',[ConfiguracionConsumidoresController::class,'destroyTipoComida'])->name('tipos-comida.destroy');
});

// Rutas para predicciones y reportes
//Route::get('predicciones', [PrediccionesController::class, 'index'])->name('predicciones.index');
Route::get('predicciones', [PrediccionesController::class, 'index'])
    ->name('predicciones.index')
    ->middleware('auth');

Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
Route::get('/reportes/ventas-data', [ReporteController::class, 'getVentasData'])->name('reportes.ventas-data');
Route::get('/reportes/parcial/semanal', [ReporteController::class, 'parcialSemanal']);
Route::get('/reportes/parcial/mensual', [ReporteController::class, 'parcialMensual']);
Route::get('/reportes/parcial/anual', [ReporteController::class, 'parcialAnual']);
// CRUD de reportes personalizados (CU8)
Route::post('/reportes/guardar', [ReporteController::class, 'guardarReporte'])->name('reportes.guardar');
Route::get('/reportes/personalizados', [ReporteController::class, 'listarPersonalizados'])->name('reportes.personalizados');
Route::get('/reportes/personalizado/{id}', [ReporteController::class, 'verPersonalizado'])->name('reportes.personalizado');
Route::put('/reportes/personalizado/{id}', [ReporteController::class, 'actualizarPersonalizado'])->name('reportes.actualizar');
Route::delete('/reportes/personalizado/{id}', [ReporteController::class, 'eliminarPersonalizado'])->name('reportes.eliminar');
Route::get('/reportes/generar-pdf/{id}', [ReporteController::class, 'generarPDF'])->name('reportes.generar-pdf');
Route::get('/reportes/obtener-pdf/{id}', [ReporteController::class, 'obtenerPDF'])->name('reportes.obtener-pdf')->withoutMiddleware('auth');

// Rutas de perfil
Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

// Rutas de usuarios (admin y director - director solo lectura)
Route::resource('users', UserController::class)->middleware('auth');
/*
Route::get('users', [UserController::class, 'index'])->name('users.index')
    ->middleware('role:admin'); // Solo usuarios con rol "admin"

Route::get('users/create', [UserController::class, 'create'])->name('users.create');
*/

// Rutas de recetas: solo admin puede crear, editar, actualizar y borrar
Route::resource('recetas', RecetaController::class)->only(['index', 'show']);
Route::resource('recetas', RecetaController::class)->except(['index', 'show'])->middleware('auth');

Route::post('/movimientos/seleccion', [MovimientoInventarioController::class, 'guardarSeleccion'])->name('movimientos.guardarSeleccion');

Route::prefix('reportes/movimientos')->name('reportes.movimientos.')->group(function () {
    Route::get('/', [ReporteController::class, 'listarMovimientos'])->name('listar');
    Route::get('{id}', [ReporteController::class, 'verMovimientos'])->name('ver');
    Route::put('{id}', [ReporteController::class, 'actualizarMovimientos'])->name('actualizar');
    Route::delete('{id}', [ReporteController::class, 'eliminarMovimientos'])->name('eliminar');
    Route::get('{id}/pdf', [ReporteController::class, 'pdfMovimientos'])->name('pdf');
});

// Rutas de notificaciones e historial
Route::get('/notificaciones', [NotificationsController::class, 'index'])->name('notificaciones.index')->middleware('auth');
Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index')->middleware('auth');

// Rutas para pruebas del sistema por email
Route::get('/email-test', [App\Http\Controllers\EmailTestController::class, 'index'])->name('email-test.index')->middleware('role:admin');
Route::post('/email-test/imap', [App\Http\Controllers\EmailTestController::class, 'testImapConnection'])->name('email-test.imap')->middleware('role:admin');
Route::post('/email-test/process', [App\Http\Controllers\EmailTestController::class, 'processEmails'])->name('email-test.process')->middleware('role:admin');
Route::post('/email-test/command', [App\Http\Controllers\EmailTestController::class, 'processTestCommand'])->name('email-test.command')->middleware('role:admin');
