<?php

namespace App\Http\Resources\Api\V1\Public\Households;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseholdInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'relationship' => $this->relationship,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'declined_at' => $this->declined_at?->toIso8601String(),
            'status' => $this->accepted_at ? 'accepted' : ($this->declined_at ? 'declined' : ($this->expires_at && $this->expires_at->isPast() ? 'expired' : 'pending')),
            'household' => $this->whenLoaded('household', fn () => [
                'id' => $this->household?->id,
                'name' => $this->household?->name,
                'commune' => $this->household?->commune,
                'address' => $this->household?->address,
            ]),
            'meter' => $this->whenLoaded('meter', fn () => $this->meter ? [
                'id' => $this->meter->id,
                'meter_number' => $this->meter->meter_number,
                'label' => $this->meter->label,
                'network_type' => $this->meter->network_type,
            ] : null),
            'code_for_testing' => app()->environment('local', 'testing') ? $this->code : null,
        ];
    }
}
