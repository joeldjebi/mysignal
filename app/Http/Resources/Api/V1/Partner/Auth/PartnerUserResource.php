<?php

namespace App\Http\Resources\Api\V1\Partner\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'organization' => $this->organization ? [
                'id' => $this->organization->id,
                'code' => $this->organization->code,
                'name' => $this->organization->name,
                'status' => $this->organization->status,
                'organization_type' => $this->organization->organizationType ? [
                    'id' => $this->organization->organizationType->id,
                    'code' => $this->organization->organizationType->code,
                    'name' => $this->organization->organizationType->name,
                ] : null,
            ] : null,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($role) => [
                'id' => $role->id,
                'code' => $role->code,
                'name' => $role->name,
            ])->values()->all()),
            'permissions' => $this->permissionCodes()->values()->all(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
