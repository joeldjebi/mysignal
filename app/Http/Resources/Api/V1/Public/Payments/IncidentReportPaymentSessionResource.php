<?php

namespace App\Http\Resources\Api\V1\Public\Payments;

use App\Http\Resources\Api\V1\Public\Reports\IncidentReportResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentReportPaymentSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sync_ref' => $this->sync_ref,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider' => $this->provider,
            'payment_context' => $this->payment_context ?? 'report',
            'checkout_link' => $this->checkout_link,
            'provider_reference' => $this->provider_reference,
            'initiated_at' => $this->initiated_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'incident_report_id' => $this->incident_report_id,
            'report' => $this->whenLoaded('incidentReport', fn () => $this->incidentReport
                ? new IncidentReportResource($this->incidentReport)
                : null),
            'pricing_rule' => $this->pricingRule ? [
                'id' => $this->pricingRule->id,
                'code' => $this->pricingRule->code,
                'label' => $this->pricingRule->label,
            ] : null,
            'damage' => ($this->payment_context ?? 'report') === 'damage' ? [
                'summary' => $this->damage_payload['damage_summary'] ?? null,
                'amount_estimated' => isset($this->damage_payload['damage_amount_estimated'])
                    ? (float) $this->damage_payload['damage_amount_estimated']
                    : null,
                'has_attachment' => filled($this->damage_attachment),
            ] : null,
        ];
    }
}
