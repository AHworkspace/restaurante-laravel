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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

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

// Rutas para predicciones y reportes
//Route::get('predicciones', [PrediccionesController::class, 'index'])->name('predicciones.index');
Route::get('predicciones', [PrediccionesController::class, 'index'])
    ->name('predicciones.index')
    ->middleware('role:admin'); // Solo usuarios con rol "admin"

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
Route::resource('users', UserController::class)->middleware('role:admin|director');
/*
Route::get('users', [UserController::class, 'index'])->name('users.index')
    ->middleware('role:admin'); // Solo usuarios con rol "admin"

Route::get('users/create', [UserController::class, 'create'])->name('users.create');
*/

// Rutas de recetas: solo admin puede crear, editar, actualizar y borrar
Route::resource('recetas', RecetaController::class)->only(['index', 'show']);
Route::resource('recetas', RecetaController::class)->except(['index', 'show'])->middleware('role:admin|cocinero');
Route::post('/recetas/{receta}/toggle-visible', [RecetaController::class, 'toggleVisible'])->name('recetas.toggleVisible')->middleware('role:admin');

Route::post('/movimientos/seleccion', [MovimientoInventarioController::class, 'guardarSeleccion'])->name('movimientos.guardarSeleccion');

Route::prefix('reportes/movimientos')->name('reportes.movimientos.')->group(function () {
    Route::get('/', [ReporteController::class, 'listarMovimientos'])->name('listar');
    Route::get('{id}', [ReporteController::class, 'verMovimientos'])->name('ver');
    Route::put('{id}', [ReporteController::class, 'actualizarMovimientos'])->name('actualizar');
    Route::delete('{id}', [ReporteController::class, 'eliminarMovimientos'])->name('eliminar');
    Route::get('{id}/pdf', [ReporteController::class, 'pdfMovimientos'])->name('pdf');
});

// Rutas de notificaciones e historial
Route::get('/notificaciones', [NotificationsController::class, 'index'])->name('notificaciones.index')->middleware('role:admin|director|cocinero|ayudante_cocina');
Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index')->middleware('role:admin|director|cajero');

// Rutas para pruebas del sistema por email
Route::get('/email-test', [App\Http\Controllers\EmailTestController::class, 'index'])->name('email-test.index')->middleware('role:admin');
Route::post('/email-test/imap', [App\Http\Controllers\EmailTestController::class, 'testImapConnection'])->name('email-test.imap')->middleware('role:admin');
Route::post('/email-test/process', [App\Http\Controllers\EmailTestController::class, 'processEmails'])->name('email-test.process')->middleware('role:admin');
Route::post('/email-test/command', [App\Http\Controllers\EmailTestController::class, 'processTestCommand'])->name('email-test.command')->middleware('role:admin');


