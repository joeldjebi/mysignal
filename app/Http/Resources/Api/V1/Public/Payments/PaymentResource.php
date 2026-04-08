<?php

namespace App\Http\Resources\Api\V1\Public\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'initiated_at' => $this->initiated_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'can_download_receipt' => $this->status === 'paid',
            'incident_report' => $this->incidentReport ? [
                'id' => $this->incidentReport->id,
                'reference' => $this->incidentReport->reference,
                'signal_code' => $this->incidentReport->signal_code,
                'signal_label' => $this->incidentReport->signal_label,
                'network_type' => $this->incidentReport->network_type,
            ] : null,
            'pricing_rule' => $this->pricingRule ? [
                'id' => $this->pricingRule->id,
                'code' => $this->pricingRule->code,
                'label' => $this->pricingRule->label,
            ] : null,
        ];
    }
}
