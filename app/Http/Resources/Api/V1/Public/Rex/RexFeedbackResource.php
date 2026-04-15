<?php

namespace App\Http\Resources\Api\V1\Public\Rex;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RexFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'context_type' => $this->context_type,
            'context_id' => $this->context_id,
            'rating' => $this->rating,
            'is_resolved' => $this->is_resolved,
            'response_time_rating' => $this->response_time_rating,
            'communication_rating' => $this->communication_rating,
            'quality_rating' => $this->quality_rating,
            'fairness_rating' => $this->fairness_rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'submitted_at' => optional($this->submitted_at)->toISOString(),
            'incident_report' => [
                'id' => $this->incidentReport?->id,
                'reference' => $this->incidentReport?->reference,
                'signal_label' => $this->incidentReport?->signal_label,
                'signal_code' => $this->incidentReport?->signal_code,
            ],
            'application' => [
                'id' => $this->application?->id,
                'name' => $this->application?->name,
                'code' => $this->application?->code,
            ],
            'organization' => [
                'id' => $this->organization?->id,
                'name' => $this->organization?->name,
                'code' => $this->organization?->code,
            ],
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
