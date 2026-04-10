<?php

namespace App\Http\Resources\Api\V1\Public\ReparationCases;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReparationCaseHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'title' => $this->title,
            'description' => $this->description,
            'created_by' => $this->createdBy?->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
