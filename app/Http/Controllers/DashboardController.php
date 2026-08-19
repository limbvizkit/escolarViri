<?php

namespace App\Http\Controllers;

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

        $recientes = [
            'escuelas' => Escuela::latest()->take(5)->get(),
            'sucursales' => Sucursal::with('escuela')->latest()->take(5)->get(),
            'empleados' => Empleado::with('sucursal')->latest()->take(5)->get(),
            'alumnos' => Alumno::with('gradoEscolar')->latest()->take(5)->get(),
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

        $topPagosPorAlumno = Pago::select('alumno_id')
            ->selectRaw("SUM({$montoTotal}) as total")
            ->groupBy('alumno_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('alumno')
            ->get();

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
            'topPagosPorAlumno' => [
                'labels' => $topPagosPorAlumno->map(fn ($pago) => $pago->alumno->nombre_completo ?? 'Sin alumno')->all(),
                'data' => $topPagosPorAlumno->pluck('total')->map(fn ($t) => (float) $t)->all(),
            ],
        ];

        return view('dashboard', compact('stats', 'recientes', 'charts'));
    }
}
