<?php

namespace App\Domain\Meters\Actions;

use App\Domain\Meters\Enums\MeterStatus;
use App\Models\Application;
use App\Models\Meter;
use App\Models\MeterAssignment;
use App\Models\Organization;
use App\Models\PublicUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMeterAction
{
    public function handle(PublicUser $user, array $payload): Meter
    {
        return DB::transaction(function () use ($user, $payload): Meter {
            $application = Application::query()->whereKey($payload['application_id'])->where('status', 'active')->firstOrFail();
            $organization = Organization::query()
                ->whereKey($payload['organization_id'])
                ->where('application_id', $application->id)
                ->where('status', 'active')
                ->first();

            if ($organization === null) {
                throw ValidationException::withMessages([
                    'organization_id' => ['L organisation selectionnee n appartient pas a l application choisie.'],
                ]);
            }

            $networkType = $payload['network_type'] ?: $organization->code ?: $application->code;
            $this->ensureMeterLimitIsNotExceeded($user, $organization->id);

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

    private function ensureMeterLimitIsNotExceeded(PublicUser $user, int $organizationId): void
    {
        $limit = config('acepen.public.max_meters_per_network', 1);

        $count = $user->meters()
            ->where('organization_id', $organizationId)
            ->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'organization_id' => ['Le nombre maximal de compteurs pour cette organisation est atteint.'],
            ]);
        }
    }
}
