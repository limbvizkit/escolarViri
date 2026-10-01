<?php

namespace App\Http\Controllers;

use App\Exports\OnlinePaymentExport;
use App\Models\PagoOnline;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OnlinePaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $payments = $this->paginateOrdered(
            $query,
            $request,
            $this->allowedSorts(),
            'id',
        );

        $filtros = [
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [
                PagoOnline::STATUS_PENDING => 'Pendiente',
                PagoOnline::STATUS_COMPLETED => 'Completado',
                PagoOnline::STATUS_FAILED => 'Fallido',
            ]],
        ];

        return view('online-payments.index', compact('payments', 'filtros'));
    }

    public function show(PagoOnline $payment): View
    {
        $payment->load('portalUser');

        return view('online-payments.show', compact('payment'));
    }

    public function exportPdf(Request $request)
    {
        $payments = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('online-payments.pdf', compact('payments'))->setPaper('a4', 'landscape');

        return $pdf->download('pagos-en-linea-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request));

        return Excel::download(new OnlinePaymentExport($query), 'pagos-en-linea-'.now()->format('Y-m-d').'.xlsx');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PagoOnline::with('portalUser');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('pagos_online.estatus', $request->input('estatus'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('pagos_online.created_at', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('pagos_online.created_at', '<=', $request->input('fecha_hasta'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'portal_user_id', 'concepto', 'monto', 'estatus', 'created_at'];
    }
}
