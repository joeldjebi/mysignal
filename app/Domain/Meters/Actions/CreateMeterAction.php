<?php

namespace App\Domain\Meters\Actions;

use App\Domain\Meters\Enums\MeterStatus;
use App\Models\Application;
use App\Models\Meter;
use App\Models\MeterAssignment;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\PublicUser;
use App\Support\ApplicationCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMeterAction
{
    public function handle(PublicUser $user, array $payload): Meter
    {
        return DB::transaction(function () use ($user, $payload): Meter {
            $networkType = strtoupper((string) ($payload['network_type'] ?? ''));

            $application = $this->resolveApplication($payload, $networkType);
            $organization = $this->resolveOrganization($payload, $application, $networkType);

            if ($organization === null) {
                throw ValidationException::withMessages([
                    'organization_id' => ['L organisation selectionnee n appartient pas a l application choisie.'],
                ]);
            }

            $networkType = $networkType !== '' ? $networkType : ($organization->code ?: $application->code);

            $meter = Meter::query()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'meter_number' => $payload['meter_number'],
                ],
                [
                    'application_id' => $application->id,
                    'organization_id' => $organization->id,
                    'network_type' => $networkType,
                    'label' => $payload['label'] ?? null,
                    'commune' => $payload['commune'] ?? null,
                    'neighborhood' => $payload['neighborhood'] ?? null,
                    'sub_neighborhood' => $payload['sub_neighborhood'] ?? null,
                    'address' => $payload['address'] ?? null,
                    'latitude' => $payload['latitude'] ?? null,
                    'longitude' => $payload['longitude'] ?? null,
                    'location_accuracy' => $payload['location_accuracy'] ?? null,
                    'location_source' => $payload['location_source'] ?? null,
                    'status' => MeterStatus::Active->value,
                ],
            );

            $meter->fill([
                'application_id' => $meter->application_id ?? $application->id,
                'organization_id' => $meter->organization_id ?? $organization->id,
                'network_type' => $meter->network_type ?? $networkType,
                'label' => $meter->label ?? ($payload['label'] ?? null),
                'commune' => $meter->commune ?? ($payload['commune'] ?? null),
                'neighborhood' => $meter->neighborhood ?? ($payload['neighborhood'] ?? null),
                'sub_neighborhood' => $meter->sub_neighborhood ?? ($payload['sub_neighborhood'] ?? null),
                'address' => $meter->address ?? ($payload['address'] ?? null),
                'latitude' => $meter->latitude ?? ($payload['latitude'] ?? null),
                'longitude' => $meter->longitude ?? ($payload['longitude'] ?? null),
                'location_accuracy' => $meter->location_accuracy ?? ($payload['location_accuracy'] ?? null),
                'location_source' => $meter->location_source ?? ($payload['location_source'] ?? null),
            ]);
            $meter->save();

            if ($user->meters()->whereKey($meter->id)->exists()) {
                throw ValidationException::withMessages([
                    'meter_number' => ['Ce compteur est deja rattache a votre compte.'],
                ]);
            }

            if (($payload['is_primary'] ?? false) === true) {
                $user->meterAssignments()->update(['is_primary' => false]);
            }

            MeterAssignment::query()->create([
                'meter_id' => $meter->id,
                'public_user_id' => $user->id,
                'is_primary' => (bool) ($payload['is_primary'] ?? false),
            ]);

            return $meter->fresh();
        });
    }

    private function resolveApplication(array $payload, string $networkType): Application
    {
        if (! empty($payload['application_id'])) {
            return Application::query()
                ->whereKey($payload['application_id'])
                ->where('status', 'active')
                ->firstOrFail();
        }

        $catalogApplication = ApplicationCatalog::findByNetworkType($networkType);

        if ($catalogApplication instanceof Application) {
            return $catalogApplication;
        }

        $applicationCode = match ($networkType) {
            'CIE' => 'MON_NRJ',
            'SODECI' => 'MON_EAU',
            default => $networkType !== '' ? $networkType : 'GENERIC',
        };

        $applicationName = match ($networkType) {
            'CIE' => 'MON NRJ',
            'SODECI' => 'MON EAU',
            default => $applicationCode,
        };

        return Application::query()->firstOrCreate(
            ['code' => $applicationCode],
            [
                'name' => $applicationName,
                'slug' => strtolower(str_replace('_', '-', $applicationCode)),
                'status' => 'active',
                'sort_order' => 99,
            ],
        );
    }

    private function resolveOrganization(array $payload, Application $application, string $networkType): ?Organization
    {
        if (! empty($payload['organization_id'])) {
            return Organization::query()
                ->whereKey($payload['organization_id'])
                ->where('application_id', $application->id)
                ->where('status', 'active')
                ->first();
        }

        if ($networkType === '') {
            return null;
        }

        $defaultNames = [
            'CIE' => 'Compagnie Ivoirienne d Electricite',
            'SODECI' => 'SODECI',
        ];

        $organizationType = $this->resolveOrganizationType($networkType);

        return Organization::query()->firstOrCreate(
            [
                'application_id' => $application->id,
                'code' => $networkType,
            ],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => $defaultNames[$networkType] ?? $networkType,
                'portal_key' => strtolower($networkType),
                'status' => 'active',
            ],
        );
    }

    private function resolveOrganizationType(string $networkType): ?OrganizationType
    {
        $code = match ($networkType) {
            'CIE' => 'ELECTRICITE',
            'SODECI' => 'EAU_POTABLE',
            default => null,
        };

        if ($code === null) {
            return OrganizationType::query()->where('status', 'active')->first();
        }

        return OrganizationType::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => match ($code) {
                    'ELECTRICITE' => 'Electricite',
                    'EAU_POTABLE' => 'Eau potable',
                    default => $code,
                },
                'description' => 'Type historique cree automatiquement pour compatibilite.',
                'status' => 'active',
            ],
        );
    }

}
