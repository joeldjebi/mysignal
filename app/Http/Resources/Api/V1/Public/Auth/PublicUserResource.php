<?php

namespace App\Http\Resources\Api\V1\Public\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_user_type' => $this->publicUserType ? [
                'id' => $this->publicUserType->id,
                'code' => $this->publicUserType->code,
                'name' => $this->publicUserType->name,
                'profile_kind' => $this->publicUserType->profile_kind,
                'pricing_rule' => $this->publicUserType->pricingRule ? [
                    'id' => $this->publicUserType->pricingRule->id,
                    'code' => $this->publicUserType->pricingRule->code,
                    'label' => $this->publicUserType->pricingRule->label,
                    'amount' => $this->publicUserType->pricingRule->amount,
                    'currency' => $this->publicUserType->pricingRule->currency,
                ] : null,
            ] : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'is_whatsapp_number' => (bool) $this->is_whatsapp_number,
            'email' => $this->email,
            'company_name' => $this->company_name,
            'company_registration_number' => $this->company_registration_number,
            'tax_identifier' => $this->tax_identifier,
            'business_sector' => $this->business_sector,
            'company_address' => $this->company_address,
            'commune' => $this->commune,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'location_accuracy' => $this->location_accuracy,
            'location_source' => $this->location_source,
            'status' => $this->status,
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
