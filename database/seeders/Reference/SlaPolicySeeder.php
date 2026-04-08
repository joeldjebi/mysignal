<?php

namespace Database\Seeders\Reference;

use App\Models\OrganizationType;
use App\Models\OrganizationTypeSignalSla;
use App\Models\SignalType;
use Illuminate\Database\Seeder;

class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $organizationTypesByCode = OrganizationType::query()
            ->pluck('id', 'code');

        if ($organizationTypesByCode->isEmpty()) {
            return;
        }

        $applicationTypeMap = [
            'MON_NRJ' => ['ELECTRICITE'],
            'MON_EAU' => ['EAU_POTABLE'],
            'MA_BANK' => ['BANQUE', 'MICROFINANCE'],
            'MON_ASS' => ['ASSURANCE'],
            'MA_COM' => ['TELECOMMUNICATION'],
            'MON_ENVI' => ['ENVIRONNEMENT'],
        ];

        SignalType::query()
            ->with(['application', 'organization'])
            ->where('status', 'active')
            ->orderBy('application_id')
            ->orderBy('organization_id')
            ->orderBy('code')
            ->get()
            ->each(function (SignalType $signalType) use ($applicationTypeMap, $organizationTypesByCode): void {
                $applicationCode = $signalType->application?->code;

                if (! $applicationCode || ! isset($applicationTypeMap[$applicationCode])) {
                    return;
                }

                $organizationTypeIds = collect($applicationTypeMap[$applicationCode])
                    ->map(fn (string $typeCode) => $organizationTypesByCode->get($typeCode))
                    ->filter()
                    ->unique()
                    ->values();

                if ($organizationTypeIds->isEmpty()) {
                    return;
                }

                $networkType = $signalType->organization?->code
                    ?: $signalType->application?->code
                    ?: $signalType->network_type;

                foreach ($organizationTypeIds as $organizationTypeId) {
                    OrganizationTypeSignalSla::query()->updateOrCreate(
                        [
                            'organization_type_id' => $organizationTypeId,
                            'signal_code' => $signalType->code,
                        ],
                        [
                            'network_type' => strtoupper((string) $networkType),
                            'signal_label' => $signalType->label,
                            'sla_hours' => $signalType->default_sla_hours ?? 24,
                            'description' => $signalType->description
                                ? 'SLA cible aligne sur le type de signal: '.$signalType->description
                                : 'SLA cible aligne sur le type de signal '.$signalType->label.'.',
                            'status' => 'active',
                        ],
                    );
                }
            });
    }
}
