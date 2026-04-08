<?php

namespace App\Http\Resources\Api\V1\Public\Households;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseholdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'commune' => $this->commune,
            'address' => $this->address,
            'status' => $this->status,
            'owner_public_user_id' => $this->owner_public_user_id,
            'members' => HouseholdMemberResource::collection($this->whenLoaded('members')),
            'pending_invitations' => HouseholdInvitationResource::collection($this->whenLoaded('invitations')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
