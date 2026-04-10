<?php

namespace App\Http\Resources\Api\V1\Public\ReparationCases;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReparationCaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'case_type' => $this->case_type,
            'priority' => $this->priority,
            'eligibility_reason' => $this->eligibility_reason,
            'damage_summary' => $this->damage_summary,
            'damage_amount_claimed' => $this->damage_amount_claimed !== null ? (float) $this->damage_amount_claimed : null,
            'damage_amount_validated' => $this->damage_amount_validated !== null ? (float) $this->damage_amount_validated : null,
            'opening_notes' => $this->opening_notes,
            'resolution_notes' => $this->resolution_notes,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'bailiff' => $this->bailiff?->name,
            'lawyer' => $this->lawyer?->name,
            'incident_report' => [
                'id' => $this->incidentReport?->id,
                'reference' => $this->incidentReport?->reference,
                'signal_label' => $this->incidentReport?->signal_label,
                'signal_code' => $this->incidentReport?->signal_code,
                'application_name' => $this->incidentReport?->application?->name,
                'organization_name' => $this->incidentReport?->organization?->name,
            ],
            'histories' => ReparationCaseHistoryResource::collection(
                $this->whenLoaded('histories', fn () => $this->histories->where('is_visible_to_public', true)->sortByDesc('id')->values())
            ),
            'steps' => $this->whenLoaded('steps', fn () => $this->steps
                ->where('is_visible_to_public', true)
                ->sortByDesc('id')
                ->values()
                ->map(fn ($step) => [
                    'id' => $step->id,
                    'step_type' => $step->step_type,
                    'title' => $step->title,
                    'status' => $step->status,
                    'summary' => $step->summary,
                    'assigned_to' => $step->assignedTo?->name,
                    'due_at' => $step->due_at?->toIso8601String(),
                    'completed_at' => $step->completed_at?->toIso8601String(),
                    'created_at' => $step->created_at?->toIso8601String(),
                ])),
        ];
    }
}
