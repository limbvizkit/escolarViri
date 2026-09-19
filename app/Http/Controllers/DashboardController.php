<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Empleado;
use App\Models\Escuela;
use App\Models\GradoEscolar;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'escuelas' => Escuela::count(),
            'sucursales' => Sucursal::count(),
            'empleados' => Empleado::count(),
            'usuarios' => User::count(),
            'roles' => Rol::count(),
            'alumnos' => Alumno::count(),
            'gradosEscolares' => GradoEscolar::count(),
        ];

        $montoTotal = 'COALESCE(pago_normal, pronto_pago, 0) + COALESCE(talleres, 0) + COALESCE(entrada_8am, 0)';

        $alumnosPorGradoEscolar = GradoEscolar::withCount('alumnos')
            ->orderByDesc('alumnos_count')
            ->get();

        $ingresosPorMes = Pago::select('mes')
            ->selectRaw("SUM({$montoTotal}) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $pagosPorFormaPago = Pago::select('forma_pago_id')
            ->selectRaw("SUM({$montoTotal}) as total")
            ->groupBy('forma_pago_id')
            ->with('formaPago')
            ->get();

        $saldoPendiente = 'SUM(adeudos.monto - adeudos.monto_pagado)';

        $adeudosPorGrado = Adeudo::active()
            ->select('grados_escolares.id', 'grados_escolares.nombre')
            ->selectRaw("{$saldoPendiente} as saldo")
            ->join('alumnos', 'alumnos.id', '=', 'adeudos.alumno_id')
            ->join('grados_escolares', 'grados_escolares.id', '=', 'alumnos.grado_escolar_id')
            ->whereColumn('adeudos.monto', '>', 'adeudos.monto_pagado')
            ->groupBy('grados_escolares.id', 'grados_escolares.nombre')
            ->orderByDesc('saldo')
            ->get();

        $adeudosPorAlumno = Adeudo::active()
            ->select('adeudos.alumno_id')
            ->selectRaw("{$saldoPendiente} as saldo")
            ->join('alumnos', 'alumnos.id', '=', 'adeudos.alumno_id')
            ->whereColumn('adeudos.monto', '>', 'adeudos.monto_pagado')
            ->groupBy('adeudos.alumno_id')
            ->orderByDesc('saldo')
            ->limit(8)
            ->get();

        $alumnosPorId = Alumno::whereIn(
            'id',
            $adeudosPorAlumno->pluck('alumno_id')->filter()->all()
        )->get()->keyBy('id');

        $charts = [
            'alumnosPorGradoEscolar' => [
                'labels' => $alumnosPorGradoEscolar->pluck('nombre')->all(),
                'data' => $alumnosPorGradoEscolar->pluck('alumnos_count')->map(fn ($n) => (int) $n)->all(),
            ],
            'ingresosPorMes' => [
                'labels' => $ingresosPorMes->map(fn ($pago) => Pago::mesLabel($pago->mes))->all(),
                'data' => $ingresosPorMes->pluck('total')->map(fn ($t) => (float) $t)->all(),
            ],
            'pagosPorFormaPago' => [
                'labels' => $pagosPorFormaPago->map(fn ($pago) => $pago->formaPago->nombre ?? 'Sin forma')->all(),
                'data' => $pagosPorFormaPago->pluck('total')->map(fn ($t) => (float) $t)->all(),
            ],
            'adeudosPorGrado' => [
                'labels' => $adeudosPorGrado->pluck('nombre')->all(),
                'data' => $adeudosPorGrado->pluck('saldo')->map(fn ($s) => (float) $s)->all(),
            ],
            'adeudosPorAlumno' => [
                'labels' => $adeudosPorAlumno
                    ->map(fn ($adeudo) => $alumnosPorId[$adeudo->alumno_id]->nombre_completo ?? 'Sin alumno')
                    ->all(),
                'data' => $adeudosPorAlumno->pluck('saldo')->map(fn ($s) => (float) $s)->all(),
            ],
        ];

        return view('dashboard', compact('stats', 'charts'));
    }
}
