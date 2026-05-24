<?php

namespace App\Http\Resources\Api\V1\Public\PurchaseReceipts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'material_name' => $this->material_name,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'attachment' => $this->resolvedAttachment(),
            'has_attachment' => filled($this->attachment),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
