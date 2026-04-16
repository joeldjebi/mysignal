<?php

namespace App\Http\Resources\Api\V1\Public\Reports;

use App\Http\Resources\Api\V1\Public\Payments\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $damageDeclaration = $this->buildDamageDeclarationPayload();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
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
            'network_type' => $this->network_type,
            'signal_code' => $this->signal_code,
            'signal_label' => $this->signal_label,
            'incident_type' => $this->incident_type,
            'description' => $this->description,
            'signal_payload' => $this->resolvedSignalPayload(),
            'target_sla_hours' => $this->target_sla_hours,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'official_response' => $this->official_response,
            'resolution_confirmation' => [
                'status' => $this->resolution_confirmation_status,
                'confirmed_at' => $this->resolution_confirmed_at?->toIso8601String(),
                'can_confirm' => $this->status === 'resolved' && $this->resolution_confirmation_status !== 'confirmed',
            ],
            'damage_declaration' => $damageDeclaration,
            'sla' => $this->buildSlaPayload(),
            'meter' => [
                'id' => $this->meter?->id,
                'meter_number' => $this->meter?->meter_number,
                'label' => $this->meter?->label,
                'network_type' => $this->meter?->network_type,
                'application_id' => $this->meter?->application_id,
                'organization_id' => $this->meter?->organization_id,
                'organization_name' => $this->meter?->organization?->name,
            ],
            'location' => [
                'country' => $this->country?->name,
                'city' => $this->city?->name,
                'commune' => $this->commune?->name,
                'address' => $this->address,
                'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
                'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
                'location_accuracy' => $this->location_accuracy,
                'location_source' => $this->location_source,
            ],
            'latest_payment' => $this->whenLoaded('payments', fn () => $this->payments->isNotEmpty()
                ? new PaymentResource($this->payments->sortByDesc('id')->first())
                : null),
            'payments' => $this->whenLoaded('payments', fn () => PaymentResource::collection($this->payments->sortByDesc('id')->values())),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function buildDamageDeclarationPayload(): array
    {
        $confirmedAt = $this->resolution_confirmed_at;
        $windowHours = 24;
        $availableUntil = $confirmedAt?->copy()->addHours($windowHours);
        $hasWindowExpired = $availableUntil ? now()->greaterThan($availableUntil) : false;
        $canDeclare = $this->resolution_confirmation_status === 'confirmed'
            && $this->damage_declared_at === null
            && $confirmedAt !== null
            && ! $hasWindowExpired;

        return [
            'declared_at' => $this->damage_declared_at?->toIso8601String(),
            'can_declare' => $canDeclare,
            'window_hours' => $windowHours,
            'available_until' => $availableUntil?->toIso8601String(),
            'window_expired' => $hasWindowExpired,
            'resolution_status' => $this->damage_resolution_status,
            'resolution_notes' => $this->damage_resolution_notes,
            'resolved_at' => $this->damage_resolved_at?->toIso8601String(),
            'summary' => $this->damage_summary,
            'amount_estimated' => $this->damage_amount_estimated !== null ? (float) $this->damage_amount_estimated : null,
            'notes' => $this->damage_notes,
            'attachment' => $this->resolvedDamageAttachment(),
        ];
    }

    private function buildSlaPayload(): array
    {
        if (! $this->created_at || ! $this->target_sla_hours) {
            return [
                'status' => 'unconfigured',
                'label' => 'SLA non configure',
                'is_respected' => null,
                'target_hours' => $this->target_sla_hours,
                'elapsed_hours' => null,
                'importance' => [
                    'code' => 'unspecified',
                    'label' => 'Priorite non definie',
                    'details' => 'Aucun delai contractuel n est encore defini pour ce type de signalement.',
                ],
            ];
        }

        $endReference = $this->resolved_at ?: now();
        $elapsedHours = round($this->created_at->diffInMinutes($endReference) / 60, 1);
        $isResolved = $this->resolved_at !== null;
        $isRespected = $isResolved ? $elapsedHours <= (float) $this->target_sla_hours : null;
        $ratio = $this->target_sla_hours > 0 ? ($elapsedHours / $this->target_sla_hours) : 0;

        $status = match (true) {
            $isResolved && $isRespected === false => 'breached',
            $ratio >= 0.8 => 'risk',
            default => 'within',
        };

        $label = match ($status) {
            'breached' => 'SLA depasse',
            'risk' => 'SLA a risque',
            default => 'Dans le TCM',
        };

        return [
            'status' => $status,
            'label' => $label,
            'is_respected' => $isRespected,
            'target_hours' => $this->target_sla_hours,
            'elapsed_hours' => $elapsedHours,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'importance' => $this->buildSlaImportancePayload($this->target_sla_hours),
        ];
    }

    private function buildSlaImportancePayload(int|float|null $targetHours): array
    {
        if (! $targetHours) {
            return [
                'code' => 'unspecified',
                'label' => 'Priorite non definie',
                'details' => 'Ce type de signalement ne dispose pas encore d une cibleTCM.',
            ];
        }

        return match (true) {
            $targetHours <= 4 => [
                'code' => 'critical',
                'label' => 'Intervention critique',
                'details' => 'Ce delai tres court sert aux incidents a fort impact securite, service ou exposition immediate des usagers.',
            ],
            $targetHours <= 12 => [
                'code' => 'high',
                'label' => 'Intervention prioritaire',
                'details' => 'Le traitement doit rester rapide car le sinistre peut vite aggraver les dommages ou perturber fortement le service.',
            ],
            $targetHours <= 24 => [
                'code' => 'medium',
                'label' => 'Intervention importante',
                'details' => 'LeTCM protege la qualite de service et limite l extension des consequences sur le terrain.',
            ],
            default => [
                'code' => 'standard',
                'label' => 'Intervention planifiee',
                'details' => 'Le delai laisse une marge d organisation mais reste un engagement de traitement a respecter.',
            ],
        };
    }
}