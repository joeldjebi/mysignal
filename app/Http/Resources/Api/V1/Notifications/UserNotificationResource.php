<?php

namespace App\Http\Resources\Api\V1\Notifications;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->categoryKey(),
            'category_label' => $this->categoryLabel(),
            'data' => $this->data ?? [],
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function categoryKey(): string
    {
        return match ($this->type) {
            'super_admin_broadcast' => 'mysignal',
            'household_invitation_created' => 'gbonhi',
            'partner_discount_applied', 'public_discount_received' => 'discount',
            default => (string) data_get($this->data, 'category', data_get($this->data, 'source', 'general')),
        };
    }

    private function categoryLabel(): string
    {
        return match ($this->categoryKey()) {
            'mysignal', 'super_admin' => 'Information MYSIGNAL',
            'gbonhi', 'household' => 'Gbonhi',
            'report', 'reports' => 'Signalement',
            'payment', 'payments' => 'Paiement',
            'subscription', 'subscriptions' => 'Abonnement',
            'discount', 'discounts', 'partner_discount' => 'Remise',
            default => 'Général',
        };
    }
}
