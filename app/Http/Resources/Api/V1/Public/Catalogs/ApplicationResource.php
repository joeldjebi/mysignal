<?php

namespace App\Http\Resources\Api\V1\Public\Catalogs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'short_description' => $this->short_description,
            'icon_url' => $this->iconUrl(),
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'sort_order' => $this->sort_order,
            'requires_public_user_identifier' => (bool) $this->requires_public_user_identifier,
            'requires_organization_type_on_report' => (bool) $this->requires_organization_type_on_report,
            'organization_types' => $this->whenLoaded('organizations', function () {
                return $this->organizations
                    ->pluck('organizationType')
                    ->filter()
                    ->unique('id')
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($type) => [
                        'id' => $type->id,
                        'code' => $type->code,
                        'name' => $type->name,
                        'description' => $type->description,
                    ]);
            }),
        ];
    }
}
