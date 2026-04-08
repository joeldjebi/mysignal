<?php

namespace App\Http\Controllers\Api\V1\Public\Payments;

use App\Domain\Payments\Actions\ConfirmReportPaymentAction;
use App\Domain\Payments\Actions\CreateReportPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Payments\PaymentResource;
use App\Models\IncidentReport;
use App\Models\Payment;
use App\Support\Pdf\SimplePaymentReceiptPdf;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicReportPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['pricingRule', 'incidentReport'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'payments' => PaymentResource::collection($payments),
        ]);
    }

    public function store(Request $request, IncidentReport $report, CreateReportPaymentAction $action)
    {
        $payment = $action->handle($request->user('public_api'), $report);
        $payment->load(['pricingRule', 'incidentReport']);

        return ApiResponse::success([
            'payment' => new PaymentResource($payment),
        ], 'Paiement initialise avec succes.', 201);
    }

    public function confirm(Request $request, Payment $payment, ConfirmReportPaymentAction $action)
    {
        $payment = $action->handle($request->user('public_api'), $payment);

        return ApiResponse::success([
            'payment' => new PaymentResource($payment),
        ], 'Paiement confirme avec succes.');
    }

    public function receipt(Request $request, Payment $payment, SimplePaymentReceiptPdf $pdf)
    {
        abort_unless((int) $payment->public_user_id === (int) $request->user('public_api')->id, 404);
        abort_unless($payment->status === 'paid', 422, 'Le reçu n est disponible que pour un paiement confirme.');

        $payment->loadMissing(['pricingRule', 'incidentReport.meter']);
        $content = $pdf->make($payment, $request->user('public_api'));

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, 'recu-'.$payment->reference.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recu-'.$payment->reference.'.pdf"',
        ]);
    }
}
