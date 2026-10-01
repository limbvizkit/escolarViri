<?php

use App\Http\Controllers\AcademicDocumentController;
use App\Http\Controllers\AdeudoController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\GradoEscolarController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\OpenpayWebhookController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalLoginController;
use App\Http\Controllers\Portal\PortalPasswordChangeController;
use App\Http\Controllers\Portal\PortalPaymentController;
use App\Http\Controllers\Portal\PortalRegistrationController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TallerController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/openpay/webhook', [OpenpayWebhookController::class, 'handle'])->name('openpay.webhook');

/*
|--------------------------------------------------------------------------
| Admin / Super-admin only
|
| User management, configuration entities, bulk exports and any route not
| explicitly granted to director, recepcion or profesor.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin'])->group(function () {
    // Configuration entities.
    Route::resource('escuelas', EscuelaController::class);
    Route::resource('sucursales', SucursalController::class)->parameters(['sucursales' => 'sucursal']);
    Route::resource('grados-escolares', GradoEscolarController::class)->parameters(['grados-escolares' => 'gradoEscolar']);

    // User management.
    Route::resource('empleados', EmpleadoController::class);
    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
    Route::resource('usuarios', UsuarioController::class);

    // Employee exports.
    Route::get('empleados/exportar/pdf', [EmpleadoController::class, 'exportPdf'])->name('empleados.export.pdf');
    Route::get('empleados/exportar/excel', [EmpleadoController::class, 'exportExcel'])->name('empleados.export.excel');

    // Bulk exports (administrative only).
    Route::get('alumnos/exportar/pdf', [AlumnoController::class, 'exportPdf'])->name('alumnos.export.pdf');
    Route::get('alumnos/exportar/excel', [AlumnoController::class, 'exportExcel'])->name('alumnos.export.excel');
    Route::get('pagos/exportar/pdf', [PagoController::class, 'exportPdf'])->name('pagos.export.pdf');
    Route::get('pagos/exportar/excel', [PagoController::class, 'exportExcel'])->name('pagos.export.excel');
    Route::get('talleres/exportar/pdf', [TallerController::class, 'exportPdf'])->name('talleres.export.pdf');
    Route::get('talleres/exportar/excel', [TallerController::class, 'exportExcel'])->name('talleres.export.excel');
    Route::get('adeudos/exportar/pdf', [AdeudoController::class, 'exportPdf'])->name('adeudos.export.pdf');
    Route::get('adeudos/exportar/excel', [AdeudoController::class, 'exportExcel'])->name('adeudos.export.excel');

    // Online payments admin view (not in the approved role matrix).
    Route::get('pagos-en-linea/exportar/pdf', [OnlinePaymentController::class, 'exportPdf'])->name('online-payments.export.pdf');
    Route::get('pagos-en-linea/exportar/excel', [OnlinePaymentController::class, 'exportExcel'])->name('online-payments.export.excel');
    Route::get('pagos-en-linea', [OnlinePaymentController::class, 'index'])->name('online-payments.index');
    Route::get('pagos-en-linea/{payment}', [OnlinePaymentController::class, 'show'])->name('online-payments.show');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|
| Allowed for admin, super-admin, director and profesor. Recepcion is not
| granted dashboard access in the approved matrix.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|profesor'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Alumnos - write routes
|
| Allowed for admin, super-admin, director and recepcion.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|recepcion'])->group(function () {
    Route::resource('alumnos', AlumnoController::class)
        ->parameters(['alumnos' => 'alumno'])
        ->except(['index', 'show']);

    Route::put('alumnos/{alumno}/inline-update', [AlumnoController::class, 'inlineUpdate'])->name('alumnos.inline-update');

    Route::get('alumnos/{alumno}/archivos/{archivo}/descargar', [AlumnoController::class, 'downloadArchivo'])->name('alumnos.archivos.download')->scopeBindings();
    Route::get('alumnos/{alumno}/archivo/descargar', [AlumnoController::class, 'downloadLegacyArchivo'])->name('alumnos.archivo.download');
    Route::post('alumnos/{alumno}/archivos', [AlumnoController::class, 'uploadArchivo'])->name('alumnos.archivos.store');
    Route::delete('alumnos/{alumno}/archivos/{archivo}', [AlumnoController::class, 'destroyArchivo'])->name('alumnos.archivos.destroy')->scopeBindings();
});

/*
|--------------------------------------------------------------------------
| Alumnos - read routes
|
| Allowed for admin, super-admin, director, recepcion and profesor.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|recepcion|profesor'])->group(function () {
    Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
    Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->name('alumnos.show');
});

/*
|--------------------------------------------------------------------------
| Pagos
|
| Allowed for admin, super-admin, director and recepcion.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|recepcion'])->group(function () {
    Route::get('pagos/precargar', [PagoController::class, 'precargar'])->name('pagos.precargar');
    Route::post('pagos/precargar', [PagoController::class, 'precargarStore'])->name('pagos.precargar.store');

    Route::resource('pagos', PagoController::class)->parameters(['pagos' => 'pago']);

    Route::put('pagos/{pago}/inline-update', [PagoController::class, 'inlineUpdate'])->name('pagos.inline-update');
});

/*
|--------------------------------------------------------------------------
| Adeudos
|
| Allowed for admin, super-admin, director and recepcion.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|recepcion'])->group(function () {
    Route::get('adeudos', [AdeudoController::class, 'index'])->name('adeudos.index');
    Route::get('adeudos/crear', [AdeudoController::class, 'create'])->name('adeudos.create');
    Route::post('adeudos', [AdeudoController::class, 'store'])->name('adeudos.store');
    Route::get('adeudos/{adeudo}', [AdeudoController::class, 'show'])->name('adeudos.show');
    Route::put('adeudos/{adeudo}', [AdeudoController::class, 'update'])->name('adeudos.update');
    Route::delete('adeudos/{adeudo}', [AdeudoController::class, 'destroy'])->name('adeudos.destroy');
    Route::post('adeudos/{adeudo}/abonar', [AdeudoController::class, 'abonar'])->name('adeudos.abonar');
    Route::put('adeudos/{adeudo}/abonos/{abono}', [AdeudoController::class, 'abonoUpdate'])->name('adeudos.abonos.update')->scopeBindings();
});

/*
|--------------------------------------------------------------------------
| Documentacion
|
| Allowed for admin, super-admin, director and recepcion.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|recepcion'])->group(function () {
    Route::get('documentacion', [DocumentacionController::class, 'index'])->name('documentacion.index');
    Route::get('documentacion/descargar/{documento}', [DocumentacionController::class, 'descargar'])->name('documentacion.descargar');
    Route::get('documentacion/{alumno}', [DocumentacionController::class, 'show'])->name('documentacion.show');
    Route::post('documentacion/{alumno}', [DocumentacionController::class, 'store'])->name('documentacion.store');
    Route::delete('documentacion/{documento}', [DocumentacionController::class, 'destroy'])->name('documentacion.destroy');

    Route::get('documentacion-academica', [AcademicDocumentController::class, 'index'])->name('academic-documents.index');
    Route::get('documentacion-academica/descargar/{academicDocument}', [AcademicDocumentController::class, 'descargar'])->name('academic-documents.descargar');
    Route::get('documentacion-academica/alumnos/{alumno}', [AcademicDocumentController::class, 'show'])->name('academic-documents.show');
    Route::get('documentacion-academica/alumnos/{alumno}/crear', [AcademicDocumentController::class, 'create'])->name('academic-documents.create');
    Route::post('documentacion-academica/alumnos/{alumno}', [AcademicDocumentController::class, 'store'])->name('academic-documents.store');
    Route::get('documentacion-academica/{academicDocument}/editar', [AcademicDocumentController::class, 'edit'])->name('academic-documents.edit');
    Route::put('documentacion-academica/{academicDocument}', [AcademicDocumentController::class, 'update'])->name('academic-documents.update');
    Route::delete('documentacion-academica/{academicDocument}', [AcademicDocumentController::class, 'destroy'])->name('academic-documents.destroy');
});

/*
|--------------------------------------------------------------------------
| Talleres - write routes
|
| Allowed for admin, super-admin and director.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director'])->group(function () {
    Route::resource('talleres', TallerController::class)
        ->parameters(['talleres' => 'taller'])
        ->except(['index', 'show']);

    Route::put('talleres/inscripciones/{tallerAlumno}/monto', [TallerController::class, 'montoUpdate'])->name('talleres.inscripcion.monto.update');
    Route::get('talleres/{taller}/alumnos/create', [TallerController::class, 'alumnoCreate'])->name('talleres.alumnos.create');
    Route::post('talleres/{taller}/alumnos', [TallerController::class, 'alumnoStore'])->name('talleres.alumnos.store');
    Route::post('talleres/{taller}/alumnos/bulk', [TallerController::class, 'alumnosStoreBulk'])->name('talleres.alumnos.bulk.store');
    Route::delete('talleres/{taller}/alumnos/{alumno}', [TallerController::class, 'alumnoDestroy'])->name('talleres.alumnos.destroy');
});

/*
|--------------------------------------------------------------------------
| Talleres - read routes
|
| Allowed for admin, super-admin, director and profesor.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super-admin|director|profesor'])->group(function () {
    Route::get('talleres', [TallerController::class, 'index'])->name('talleres.index');
});

/*
|--------------------------------------------------------------------------
| Portal routes (unchanged)
|--------------------------------------------------------------------------
*/

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:portal')->group(function () {
        Route::get('/registro', [PortalRegistrationController::class, 'showRegistrationForm'])->name('register');
        Route::post('/registro', [PortalRegistrationController::class, 'register'])
            ->middleware('throttle:portal-register')
            ->name('register.attempt');
        Route::get('/login', [PortalLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [PortalLoginController::class, 'login'])
            ->middleware('throttle:portal-login')
            ->name('login.attempt');
    });

    Route::middleware('auth:portal')->group(function () {
        Route::post('/logout', [PortalLoginController::class, 'logout'])->name('logout');
    });

    Route::middleware(['auth:portal', 'portal.password.change'])->group(function () {
        Route::get('/cambiar-contrasena', [PortalPasswordChangeController::class, 'showChangePasswordForm'])
            ->name('password.change');
        Route::post('/cambiar-contrasena', [PortalPasswordChangeController::class, 'update'])
            ->name('password.change.update');
    });

    Route::middleware(['auth:portal', 'portal.password.changed'])->group(function () {
        Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

        Route::get('/pagar', [PortalPaymentController::class, 'create'])->name('payments.create');
        Route::post('/pagar', [PortalPaymentController::class, 'store'])->name('payments.store');
        Route::get('/mis-pagos', [PortalPaymentController::class, 'index'])->name('payments.index');
        Route::get('/mis-pagos/{payment}', [PortalPaymentController::class, 'show'])->name('payments.show');
    });
});
