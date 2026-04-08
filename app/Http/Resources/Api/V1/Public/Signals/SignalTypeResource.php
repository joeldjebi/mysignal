<?php

namespace App\Http\Resources\Api\V1\Public\Signals;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignalTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this['code'],
            'application_id' => $this['application_id'] ?? null,
            'organization_id' => $this['organization_id'] ?? null,
            'application_code' => $this['application_code'] ?? null,
            'application_name' => $this['application_name'] ?? null,
            'organization_code' => $this['organization_code'] ?? null,
            'organization_name' => $this['organization_name'] ?? null,
            'network_type' => $this['network_type'],
            'label' => $this['label'],
            'description' => $this['description'],
            'sla_target' => $this['sla_target'],
            'sla_targets' => $this['sla_targets'] ?? [],
            'data_fields' => $this['data_fields'],
        ];
    }
}
