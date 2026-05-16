<?php

namespace App\Http\Controllers\Api\V1\Public\Payments;

use App\Domain\Payments\Actions\ConfirmIncidentReportFineoPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Payments\IncidentReportPaymentSessionResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class FineoPayCallbackController extends Controller
{
    public function __invoke(Request $request, ConfirmIncidentReportFineoPaymentAction $action)
    {
        $expectedToken = (string) config('services.fineopay.callback_token');

        if ($expectedToken !== '') {
            abort_unless(hash_equals($expectedToken, (string) $request->query('token')), 403);
        }

        $payload = $request->validate([
            'syncRef' => ['required', 'string', 'max:60'],
            'reference' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:30'],
            'clientAccountNumber' => ['nullable', 'string', 'max:80'],
            'timestamp' => ['nullable', 'date'],
        ]);

        $session = $action->handle($payload, $request);

        return ApiResponse::success([
            'payment_session' => new IncidentReportPaymentSessionResource($session),
        ], 'Callback FineoPay traite avec succes.');
    }
}
