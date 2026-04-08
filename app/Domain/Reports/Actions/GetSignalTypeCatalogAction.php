<?php

namespace App\Domain\Reports\Actions;

use App\Models\OrganizationTypeSignalSla;
use App\Models\SignalType;

class GetSignalTypeCatalogAction
{
    public function handle()
    {
        $slaPoliciesBySignalCode = OrganizationTypeSignalSla::query()
            ->where('status', 'active')
            ->get()
            ->groupBy('signal_code');

        return SignalType::query()
            ->with(['application:id,code,name,slug', 'organization:id,code,name'])
            ->where('status', 'active')
            ->orderBy('application_id')
            ->orderBy('organization_id')
            ->orderBy('code')
            ->get()
            ->map(function (SignalType $signalType) use ($slaPoliciesBySignalCode): array {
                $fallbackSlaTargets = $slaPoliciesBySignalCode
                    ->get($signalType->code, collect())
                    ->mapWithKeys(fn (OrganizationTypeSignalSla $slaPolicy) => [
                        (string) $slaPolicy->organization_type_id => [
                            'hours' => $slaPolicy->sla_hours,
                            'label' => $slaPolicy->sla_hours.'h',
                        ],
                    ])
                    ->all();

                return [
                    'code' => $signalType->code,
                    'application_id' => $signalType->application_id,
                    'organization_id' => $signalType->organization_id,
                    'application_code' => $signalType->application?->code,
                    'application_name' => $signalType->application?->name,
                    'organization_code' => $signalType->organization?->code,
                    'organization_name' => $signalType->organization?->name,
                    'network_type' => $signalType->network_type,
                    'label' => $signalType->label,
                    'description' => $signalType->description,
                    'sla_target' => $signalType->default_sla_hours !== null
                        ? ['hours' => $signalType->default_sla_hours, 'label' => $signalType->default_sla_hours.'h']
                        : null,
                    'sla_targets' => $fallbackSlaTargets,
                    'data_fields' => $signalType->data_fields ?? [],
                ];
            })
            ->values();
    }
}
