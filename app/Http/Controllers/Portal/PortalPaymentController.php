<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\OpenpayException;
use App\Http\Controllers\Controller;
use App\Models\PagoOnline;
use App\Models\PortalUser;
use App\Services\OpenpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalPaymentController extends Controller
{
    public function __construct(
        private readonly OpenpayService $openpayService,
    ) {}

    public function create(Request $request): View
    {
        $orderId = $this->generateOrderId();

        return view('portal.payments.create', [
            'orderId' => $orderId,
            'merchantId' => config('openpay.merchant_id'),
            'publicKey' => config('openpay.public_key'),
            'sandbox' => config('openpay.mode', 'sandbox') !== 'production',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000', 'decimal:0,2'],
            'concepto' => ['required', 'string', 'min:3', 'max:255'],
            'token_id' => ['required', 'string', 'max:255'],
            'device_session_id' => ['required', 'string', 'max:255'],
            'order_id' => ['required', 'string', 'max:64'],
        ]);

        /** @var PortalUser $portalUser */
        $portalUser = Auth::guard('portal')->user();

        $existingPayment = PagoOnline::where('order_id', $validated['order_id'])
            ->where('portal_user_id', $portalUser->id)
            ->first();

        if ($existingPayment !== null) {
            if ($existingPayment->isCompleted()) {
                return redirect()->route('portal.payments.show', $existingPayment)
                    ->with('status', 'Este pago ya fue procesado anteriormente.');
            }

            if ($existingPayment->isFailed()) {
                return redirect()->route('portal.payments.create')
                    ->with('error', 'El pago anterior no pudo completarse. Intenta con un nuevo pago.');
            }
        }

        $payment = PagoOnline::create([
            'portal_user_id' => $portalUser->id,
            'concepto' => $validated['concepto'],
            'monto' => $validated['amount'],
            'moneda' => 'MXN',
            'order_id' => $validated['order_id'],
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        try {
            $response = $this->openpayService->createCharge([
                'source_id' => $validated['token_id'],
                'amount' => (float) $validated['amount'],
                'currency' => 'MXN',
                'description' => $validated['concepto'],
                'order_id' => $validated['order_id'],
                'device_session_id' => $validated['device_session_id'],
                'customer' => $this->buildCustomer($portalUser),
            ]);
        } catch (OpenpayException $e) {
            $payment->update([
                'estatus' => PagoOnline::STATUS_FAILED,
                'error_code' => $e->getErrorCode(),
                'error_category' => $e->getCategory(),
                'error_message' => $e->getMessage(),
            ]);

            Log::warning('OpenPay payment failed', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'error_code' => $e->getErrorCode(),
                'error_category' => $e->getCategory(),
            ]);

            return redirect()->route('portal.payments.create')
                ->with('error', $this->safeErrorMessage($e))
                ->withInput();
        }

        $payment->update([
            'estatus' => PagoOnline::STATUS_COMPLETED,
            'openpay_charge_id' => $response['id'] ?? null,
            'authorization' => $response['authorization'] ?? null,
            'card_brand' => $response['card']['brand'] ?? null,
            'card_last4' => $this->extractLastFour($response['card']['card_number'] ?? null),
        ]);

        return redirect()->route('portal.payments.show', $payment)
            ->with('success', 'Pago procesado correctamente.');
    }

    public function index(Request $request): View
    {
        /** @var PortalUser $portalUser */
        $portalUser = Auth::guard('portal')->user();

        $payments = PagoOnline::forPortalUser($portalUser)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('portal.payments.index', compact('payments'));
    }

    public function show(PagoOnline $payment): View
    {
        /** @var PortalUser $portalUser */
        $portalUser = Auth::guard('portal')->user();

        abort_if($payment->portal_user_id !== $portalUser->id, 403);

        return view('portal.payments.show', compact('payment'));
    }

    private function generateOrderId(): string
    {
        return 'PO-'.date('Ymd-His').'-'.Str::upper(Str::random(6));
    }

    /**
     * @param  PortalUser  $portalUser
     * @return array<string, string|null>
     */
    private function buildCustomer($portalUser): array
    {
        return [
            'name' => $portalUser->name,
            'email' => $portalUser->email,
            'phone_number' => $portalUser->phone,
        ];
    }

    private function extractLastFour(?string $cardNumber): ?string
    {
        if ($cardNumber === null || $cardNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $cardNumber);

        if ($digits === null || strlen($digits) < 4) {
            return null;
        }

        return substr($digits, -4);
    }

    private function safeErrorMessage(OpenpayException $exception): string
    {
        $category = $exception->getCategory();

        if ($category === 'configuration') {
            return 'El servicio de pagos no está configurado. Contacta al administrador.';
        }

        if ($category === 'connection') {
            return 'No pudimos conectar con el procesador de pagos. Intenta de nuevo en unos momentos.';
        }

        return 'No pudimos procesar el pago. Verifica los datos de tu tarjeta e intenta de nuevo.';
    }
}
