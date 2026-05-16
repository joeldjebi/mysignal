<?php

namespace App\Http\Controllers\Api\V1\Public\Payments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Payments\IncidentReportPaymentSessionResource;
use App\Models\IncidentReportPaymentSession;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicIncidentReportPaymentSessionController extends Controller
{
    public function show(Request $request, string $syncRef)
    {
        $session = IncidentReportPaymentSession::query()
            ->with([
                'pricingRule',
                'incidentReport.application',
                'incidentReport.organization',
                'incidentReport.meter.organization',
                'incidentReport.country',
                'incidentReport.city',
                'incidentReport.commune',
                'incidentReport.payments.pricingRule',
            ])
            ->where('sync_ref', $syncRef)
            ->where('public_user_id', $request->user('public_api')->id)
            ->firstOrFail();

        return ApiResponse::success([
            'payment_session' => new IncidentReportPaymentSessionResource($session),
        ]);
    }
}
