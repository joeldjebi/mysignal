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
            ->with([
                'application:id,code,name,slug',
                'organization:id,code,name',
                'organizations:id,code,name',
                'subTypes' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')->orderBy('label'),
            ])
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
                    'organization_ids' => $signalType->organizations->pluck('id')->when(
                        $signalType->organization_id !== null,
                        fn ($ids) => $ids->push($signalType->organization_id)
                    )->unique()->values()->all(),
                    'application_code' => $signalType->application?->code,
                    'application_name' => $signalType->application?->name,
                    'organization_code' => $signalType->organization?->code,
                    'organization_name' => $signalType->organization?->name,
                    'organizations' => $signalType->organizations
                        ->map(fn ($organization): array => [
                            'id' => $organization->id,
                            'code' => $organization->code,
                            'name' => $organization->name,
                        ])
                        ->values()
                        ->all(),
                    'network_type' => $signalType->network_type,
                    'label' => $signalType->label,
                    'description' => $signalType->description,
                    'requires_public_user_identifier' => (bool) $signalType->requires_public_user_identifier,
                    'sla_target' => $signalType->default_sla_hours !== null
                        ? ['hours' => $signalType->default_sla_hours, 'label' => $signalType->default_sla_hours.'h']
                        : null,
                    'sla_targets' => $fallbackSlaTargets,
                    'sub_types' => $signalType->subTypes->isNotEmpty()
                        ? $signalType->subTypes
                            ->map(fn ($subType): array => [
                                'id' => $subType->id,
                                'code' => $subType->code,
                                'label' => $subType->label,
                                'description' => $subType->description,
                                'is_other' => false,
                            ])
                            ->push([
                                'id' => null,
                                'code' => 'OTHER',
                                'label' => 'Autre',
                                'description' => 'Le motif exact ne figure pas dans la liste.',
                                'is_other' => true,
                            ])
                            ->values()
                            ->all()
                        : [],
                ];
            })
            ->values();
    }
}
