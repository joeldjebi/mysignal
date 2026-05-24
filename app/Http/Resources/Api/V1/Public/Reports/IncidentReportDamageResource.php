<?php

namespace App\Http\Resources\Api\V1\Public\Reports;

use App\Http\Resources\Api\V1\Public\PurchaseReceipts\PurchaseReceiptResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentReportDamageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->id,
            'report_reference' => $this->reference,
            'report_status' => $this->status,
            'application' => [
                'id' => $this->application?->id,
                'code' => $this->application?->code,
                'name' => $this->application?->name,
                'slug' => $this->application?->slug,
            ],
            'organization' => [
                'id' => $this->organization?->id,
                'code' => $this->organization?->code,
                'name' => $this->organization?->name,
            ],
            'signal' => [
                'code' => $this->signal_code,
                'label' => $this->signal_label,
                'incident_type' => $this->incident_type,
                'occurred_at' => $this->occurred_at?->toIso8601String(),
            ],
            'declared_at' => $this->damage_declared_at?->toIso8601String(),
            'summary' => $this->damage_summary,
            'amount_estimated' => $this->damage_amount_estimated !== null ? (float) $this->damage_amount_estimated : null,
            'notes' => $this->damage_notes,
            'attachment' => $this->resolvedDamageAttachment(),
            'purchase_receipt' => $this->whenLoaded('purchaseReceipt', fn () => $this->purchaseReceipt
                ? new PurchaseReceiptResource($this->purchaseReceipt)
                : null),
            'resolution_status' => $this->damage_resolution_status,
            'resolution_notes' => $this->damage_resolution_notes,
            'resolved_at' => $this->damage_resolved_at?->toIso8601String(),
            'reparation_case' => $this->whenLoaded('reparationCase', fn () => $this->reparationCase ? [
                'id' => $this->reparationCase->id,
                'reference' => $this->reparationCase->reference,
                'status' => $this->reparationCase->status,
                'case_type' => $this->reparationCase->case_type,
                'priority' => $this->reparationCase->priority,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
