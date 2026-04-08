<?php

namespace App\Http\Resources\Api\V1\Public\Meters;

use App\Support\ApplicationCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $application = $this->resource->relationLoaded('application')
            ? $this->application
            : ApplicationCatalog::findByNetworkType($this->network_type);

        return [
            'id' => $this->id,
            'application_id' => $application?->id,
            'application_code' => $application?->code,
            'application_name' => $application?->name,
            'organization_id' => $this->organization_id,
            'organization_name' => $this->organization?->name,
            'organization_code' => $this->organization?->code,
            'organization_type_id' => $this->organization?->organization_type_id,
            'organization_type_name' => $this->organization?->organizationType?->name,
            'network_type' => $this->network_type,
            'meter_number' => $this->meter_number,
            'label' => $this->label,
            'commune' => $this->commune,
            'neighborhood' => $this->neighborhood,
            'sub_neighborhood' => $this->sub_neighborhood,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'location_accuracy' => $this->location_accuracy,
            'location_source' => $this->location_source,
            'status' => $this->status,
            'is_primary' => (bool) ($this->pivot?->is_primary ?? false),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
