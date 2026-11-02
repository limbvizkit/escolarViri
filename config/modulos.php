<?php

/*
|--------------------------------------------------------------------------
| Module catalog and initial permission matrix
|--------------------------------------------------------------------------
|
| Every module the sidebar and the `permiso` middleware know about. `prefijo`
| is the first segment of the route name (e.g. `pagos` for `pagos.index`) and
| is what the middleware uses to resolve the module of a request. `ruta` is the
| index route name and `url` the public path it resolves to.
|
| `orden` is global and grouped contiguously per `grupo`, so ordering by
| `orden` keeps every group together in catalog order.
|
| `inicial` reproduces the access that the removed hardcoded role middleware
| granted. `super-admin` appears in the seed arrays for completeness only: it
| bypasses the matrix in code and its rows are never written.
|
*/

$admin = ['admin', 'super-admin'];
$adminDirector = ['admin', 'super-admin', 'director'];
$staff = ['admin', 'super-admin', 'director', 'recepcion'];
$everyone = ['admin', 'super-admin', 'director', 'recepcion', 'profesor'];

$modulos = [
    ['clave' => 'dashboard', 'nombre' => 'Dashboard', 'grupo' => 'Principal', 'prefijo' => 'dashboard', 'ruta' => 'dashboard', 'url' => '/', 'icono' => 'bi-grid-1x2-fill'],
    ['clave' => 'pagos', 'nombre' => 'Pagos', 'grupo' => 'Administrativo', 'prefijo' => 'pagos', 'ruta' => 'pagos.index', 'url' => '/pagos', 'icono' => 'bi-cash-coin'],
    ['clave' => 'adeudos', 'nombre' => 'Adeudos', 'grupo' => 'Administrativo', 'prefijo' => 'adeudos', 'ruta' => 'adeudos.index', 'url' => '/adeudos', 'icono' => 'bi-cash-stack'],
    ['clave' => 'talleres', 'nombre' => 'Talleres', 'grupo' => 'Administrativo', 'prefijo' => 'talleres', 'ruta' => 'talleres.index', 'url' => '/talleres', 'icono' => 'bi-easel'],
    ['clave' => 'cursos', 'nombre' => 'Cursos', 'grupo' => 'Administrativo', 'prefijo' => 'cursos', 'ruta' => 'cursos.index', 'url' => '/cursos', 'icono' => 'bi-book'],
    ['clave' => 'pagos-talleres', 'nombre' => 'Pagos talleres', 'grupo' => 'Administrativo', 'prefijo' => 'pagos-talleres', 'ruta' => 'pagos-talleres.index', 'url' => '/pagos-talleres', 'icono' => 'bi-easel2'],
    ['clave' => 'pagos-cursos', 'nombre' => 'Pagos cursos', 'grupo' => 'Administrativo', 'prefijo' => 'pagos-cursos', 'ruta' => 'pagos-cursos.index', 'url' => '/pagos-cursos', 'icono' => 'bi-mortarboard'],
    ['clave' => 'pagos-lunch', 'nombre' => 'Pagos lunch', 'grupo' => 'Administrativo', 'prefijo' => 'pagos-lunch', 'ruta' => 'pagos-lunch.index', 'url' => '/pagos-lunch', 'icono' => 'bi-cup-hot'],
    ['clave' => 'pagos-8am', 'nombre' => 'Pagos 8 AM', 'grupo' => 'Administrativo', 'prefijo' => 'pagos-8am', 'ruta' => 'pagos-8am.index', 'url' => '/pagos-8am', 'icono' => 'bi-sunrise'],
    ['clave' => 'pagos-horarios-extendidos', 'nombre' => 'Pagos horario extendido', 'grupo' => 'Administrativo', 'prefijo' => 'pagos-horarios-extendidos', 'ruta' => 'pagos-horarios-extendidos.index', 'url' => '/pagos-horarios-extendidos', 'icono' => 'bi-clock-history'],
    ['clave' => 'datos-facturacion', 'nombre' => 'Datos Facturación', 'grupo' => 'Administrativo', 'prefijo' => 'datos-facturacion', 'ruta' => 'datos-facturacion.index', 'url' => '/datos-facturacion', 'icono' => 'bi-receipt'],
    ['clave' => 'pagos-en-linea', 'nombre' => 'Pagos en línea', 'grupo' => 'Administrativo', 'prefijo' => 'online-payments', 'ruta' => 'online-payments.index', 'url' => '/pagos-en-linea', 'icono' => 'bi-credit-card'],
    ['clave' => 'escuelas', 'nombre' => 'Escuelas', 'grupo' => 'Catálogos', 'prefijo' => 'escuelas', 'ruta' => 'escuelas.index', 'url' => '/escuelas', 'icono' => 'bi-buildings-fill'],
    ['clave' => 'sucursales', 'nombre' => 'Sucursales', 'grupo' => 'Catálogos', 'prefijo' => 'sucursales', 'ruta' => 'sucursales.index', 'url' => '/sucursales', 'icono' => 'bi-diagram-3-fill'],
    ['clave' => 'empleados', 'nombre' => 'Empleados', 'grupo' => 'Catálogos', 'prefijo' => 'empleados', 'ruta' => 'empleados.index', 'url' => '/empleados', 'icono' => 'bi-people-fill'],
    ['clave' => 'grados-escolares', 'nombre' => 'Grados Escolares', 'grupo' => 'Catálogos', 'prefijo' => 'grados-escolares', 'ruta' => 'grados-escolares.index', 'url' => '/grados-escolares', 'icono' => 'bi-layers-fill'],
    ['clave' => 'documentacion', 'nombre' => 'Documentación', 'grupo' => 'Documentación', 'prefijo' => 'documentacion', 'ruta' => 'documentacion.index', 'url' => '/documentacion', 'icono' => 'bi-folder2-open'],
    ['clave' => 'documentacion-academica', 'nombre' => 'Documentación Académica', 'grupo' => 'Documentación', 'prefijo' => 'academic-documents', 'ruta' => 'academic-documents.index', 'url' => '/documentacion-academica', 'icono' => 'bi-journal-text'],
    ['clave' => 'usuarios', 'nombre' => 'Usuarios', 'grupo' => 'Acceso', 'prefijo' => 'usuarios', 'ruta' => 'usuarios.index', 'url' => '/usuarios', 'icono' => 'bi-person-badge-fill'],
    ['clave' => 'roles', 'nombre' => 'Roles', 'grupo' => 'Acceso', 'prefijo' => 'roles', 'ruta' => 'roles.index', 'url' => '/roles', 'icono' => 'bi-shield-lock-fill'],
    ['clave' => 'alumnos', 'nombre' => 'Alumnos', 'grupo' => 'Académico', 'prefijo' => 'alumnos', 'ruta' => 'alumnos.index', 'url' => '/alumnos', 'icono' => 'bi-person-lines-fill'],
    ['clave' => 'estimulacion-temprana', 'nombre' => 'Estimulación Temprana', 'grupo' => 'Académico', 'prefijo' => 'estimulacion-temprana', 'ruta' => 'estimulacion-temprana.index', 'url' => '/estimulacion-temprana', 'icono' => 'bi-balloon'],
];

foreach ($modulos as $indice => &$modulo) {
    $modulo['orden'] = $indice + 1;
}

unset($modulo);

return [
    'modulos' => $modulos,

    'inicial' => [
        'dashboard' => ['ver' => ['admin', 'super-admin', 'director', 'profesor'], 'escribir' => [], 'eliminar' => []],
        'pagos' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'adeudos' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'talleres' => ['ver' => ['admin', 'super-admin', 'director', 'profesor'], 'escribir' => $adminDirector, 'eliminar' => $adminDirector],
        'cursos' => ['ver' => ['admin', 'super-admin', 'director', 'profesor'], 'escribir' => $adminDirector, 'eliminar' => $adminDirector],
        'pagos-talleres' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'pagos-cursos' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'pagos-lunch' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'pagos-8am' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'pagos-horarios-extendidos' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'datos-facturacion' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'pagos-en-linea' => ['ver' => $admin, 'escribir' => [], 'eliminar' => []],
        'escuelas' => ['ver' => $admin, 'escribir' => $admin, 'eliminar' => $admin],
        'sucursales' => ['ver' => $admin, 'escribir' => $admin, 'eliminar' => $admin],
        'empleados' => ['ver' => $admin, 'escribir' => $admin, 'eliminar' => $admin],
        'grados-escolares' => ['ver' => $admin, 'escribir' => $admin, 'eliminar' => $admin],
        'documentacion' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'documentacion-academica' => ['ver' => $staff, 'escribir' => $staff, 'eliminar' => $staff],
        'usuarios' => ['ver' => $admin, 'escribir' => $admin, 'eliminar' => $admin],
        'roles' => ['ver' => $admin, 'escribir' => ['super-admin'], 'eliminar' => ['super-admin']],
        'alumnos' => ['ver' => $everyone, 'escribir' => $staff, 'eliminar' => $staff],
        'estimulacion-temprana' => ['ver' => $everyone, 'escribir' => $staff, 'eliminar' => $staff],
    ],

    /*
    | Bulk exports are a cross-cutting administrative capability: the three
    | matrix actions cannot express it, so each module declares which roles may
    | export its data. The payment submodules keep the staff list they had
    | before the matrix existed; every other module falls back to
    | `exporta_default`. Both are invariants, not per-role configuration.
    */
    'exportaciones' => [
        'pagos-talleres' => $staff,
        'pagos-cursos' => $staff,
        'pagos-lunch' => $staff,
        'pagos-8am' => $staff,
        'pagos-horarios-extendidos' => $staff,
    ],

    'exporta_default' => $admin,
];
