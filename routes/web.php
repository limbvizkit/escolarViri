<?php

use App\Http\Controllers\AdeudoController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\GradoEscolarController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TallerController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('escuelas', EscuelaController::class);
    Route::resource('sucursales', SucursalController::class)->parameters(['sucursales' => 'sucursal']);
    Route::resource('empleados', EmpleadoController::class);
    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('grados-escolares', GradoEscolarController::class)->parameters(['grados-escolares' => 'gradoEscolar']);
    Route::get('alumnos/exportar/pdf', [AlumnoController::class, 'exportPdf'])->name('alumnos.export.pdf');
    Route::get('alumnos/exportar/excel', [AlumnoController::class, 'exportExcel'])->name('alumnos.export.excel');
    Route::resource('alumnos', AlumnoController::class)->parameters(['alumnos' => 'alumno']);
    Route::put('alumnos/{alumno}/inline-update', [AlumnoController::class, 'inlineUpdate'])->name('alumnos.inline-update');
    Route::get('pagos/exportar/pdf', [PagoController::class, 'exportPdf'])->name('pagos.export.pdf');
    Route::get('pagos/exportar/excel', [PagoController::class, 'exportExcel'])->name('pagos.export.excel');
    Route::get('pagos/precargar', [PagoController::class, 'precargar'])->name('pagos.precargar');
    Route::post('pagos/precargar', [PagoController::class, 'precargarStore'])->name('pagos.precargar.store');
    Route::resource('pagos', PagoController::class)->parameters(['pagos' => 'pago']);
    Route::put('pagos/{pago}/inline-update', [PagoController::class, 'inlineUpdate'])->name('pagos.inline-update');
    Route::put('talleres/inscripciones/{tallerAlumno}/monto', [TallerController::class, 'montoUpdate'])->name('talleres.inscripcion.monto.update');
    Route::get('talleres/{taller}/alumnos/create', [TallerController::class, 'alumnoCreate'])->name('talleres.alumnos.create');
    Route::post('talleres/{taller}/alumnos', [TallerController::class, 'alumnoStore'])->name('talleres.alumnos.store');
    Route::delete('talleres/{taller}/alumnos/{alumno}', [TallerController::class, 'alumnoDestroy'])->name('talleres.alumnos.destroy');
    Route::resource('talleres', TallerController::class)->parameters(['talleres' => 'taller'])->except(['show']);
    Route::get('documentacion', [DocumentacionController::class, 'index'])->name('documentacion.index');
    Route::get('documentacion/descargar/{documento}', [DocumentacionController::class, 'descargar'])->name('documentacion.descargar');
    Route::get('documentacion/{alumno}', [DocumentacionController::class, 'show'])->name('documentacion.show');
    Route::post('documentacion/{alumno}', [DocumentacionController::class, 'store'])->name('documentacion.store');
    Route::delete('documentacion/{documento}', [DocumentacionController::class, 'destroy'])->name('documentacion.destroy');
    Route::get('adeudos', [AdeudoController::class, 'index'])->name('adeudos.index');
    Route::get('adeudos/crear', [AdeudoController::class, 'create'])->name('adeudos.create');
    Route::post('adeudos', [AdeudoController::class, 'store'])->name('adeudos.store');
    Route::get('adeudos/{adeudo}', [AdeudoController::class, 'show'])->name('adeudos.show');
    Route::post('adeudos/{adeudo}/abonar', [AdeudoController::class, 'abonar'])->name('adeudos.abonar');

    Route::put('adeudos/{adeudo}/abonos/{abono}', [AdeudoController::class, 'abonoUpdate'])->name('adeudos.abonos.update');
});
