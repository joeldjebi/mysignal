<?php

namespace App\Http\Resources\Api\V1\Public\Households;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseholdMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'relationship' => $this->relationship,
            'is_owner' => (bool) $this->is_owner,
            'status' => $this->status,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'user' => [
                'id' => $this->publicUser?->id,
                'first_name' => $this->publicUser?->first_name,
                'last_name' => $this->publicUser?->last_name,
                'phone' => $this->publicUser?->phone,
                'commune' => $this->publicUser?->commune,
            ],
        ];
    }
}
