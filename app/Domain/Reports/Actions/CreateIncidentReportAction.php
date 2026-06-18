<?php

namespace App\Domain\Reports\Actions;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\Application;
use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\OrganizationTypeSignalSla;
use App\Models\PublicUser;
use App\Models\SignalSubType;
use App\Models\SignalType;
use App\Services\WasabiService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateIncidentReportAction
{
    public function __construct(
        private readonly WasabiService $wasabiService,
    ) {}

    public function handle(
        PublicUser $user,
        array $payload,
        ?UploadedFile $signalAttachmentFile = null,
        ?array $storedSignalAttachment = null
    ): IncidentReport {
        $prepared = $this->prepare($user, $payload);

        return DB::transaction(function () use ($user, $prepared, $payload, $signalAttachmentFile, $storedSignalAttachment): IncidentReport {
            $reference = $this->generateReference();
            $signalAttachment = $signalAttachmentFile
                ? $this->storeSignalAttachmentFile($signalAttachmentFile, $reference)
                : $storedSignalAttachment;

            return IncidentReport::query()->create([
                'public_user_id' => $user->id,
                'application_id' => $prepared['application']->id,
                'organization_id' => $prepared['organization']?->id,
                'meter_id' => $prepared['meter']?->id,
                'country_id' => $prepared['country']->id,
                'city_id' => $prepared['city']->id,
                'commune_id' => $prepared['commune']->id,
                'address' => $prepared['meter']?->address ?: ($user->address ?? null),
                'latitude' => $prepared['meter']?->latitude ?? ($payload['latitude'] ?? $user->latitude ?? null),
                'longitude' => $prepared['meter']?->longitude ?? ($payload['longitude'] ?? $user->longitude ?? null),
                'location_accuracy' => $prepared['meter']?->location_accuracy ?? ($payload['location_accuracy'] ?? $user->location_accuracy ?? null),
                'location_source' => $prepared['meter']?->location_source ?? ($payload['location_source'] ?? $user->location_source ?? null),
                'network_type' => $prepared['meter']?->network_type ?: ($prepared['organization']?->code ?: $prepared['application']->code),
                'signal_code' => $prepared['signal_type']->code,
                'signal_label' => $prepared['signal_type']->label,
                'signal_sub_type_id' => $prepared['signal_sub_type']?->id,
                'signal_sub_type_code' => $prepared['signal_sub_type_code'],
                'signal_sub_type_label' => $prepared['signal_sub_type_label'],
                'incident_type' => $prepared['signal_type']->code,
                'reference' => $reference,
                'description' => $payload['description'] ?? null,
                'signal_attachment' => $signalAttachment,
                'target_sla_hours' => $prepared['programmed_sla'] ?? $prepared['signal_type']->default_sla_hours,
                'occurred_at' => $payload['occurred_at'] ?? CarbonImmutable::now(),
                'status' => IncidentReportStatus::Submitted->value,
            ]);
        });
    }

    public function validateForPayment(PublicUser $user, array $payload): array
    {
        return $this->prepare($user, $payload);
    }

    private function prepare(PublicUser $user, array $payload): array
    {
        $meter = null;

        if (! empty($payload['meter_id'])) {
            $meter = $user->meters()->whereKey($payload['meter_id'])->first();

            if ($meter === null) {
                throw ValidationException::withMessages([
                    'meter_id' => ['Le compteur selectionne ne vous appartient pas.'],
                ]);
            }
        }

        $application = $meter?->application;

        if (! $application instanceof Application && ! empty($payload['application_id'])) {
            $application = Application::query()
                ->whereKey($payload['application_id'])
                ->where('status', 'active')
                ->first();
        }

        if (! $application instanceof Application) {
            throw ValidationException::withMessages([
                'application_id' => ['La catégorie selectionnee est invalide.'],
            ]);
        }

        if ($application->requires_public_user_identifier && $meter === null) {
            throw ValidationException::withMessages([
                'meter_id' => ['Un identifiant est obligatoire pour cette catégorie.'],
            ]);
        }

        $organization = $meter?->organization;
        $organizationType = null;

        if (! empty($payload['organization_type_id'])) {
            $organizationType = OrganizationType::query()
                ->whereKey($payload['organization_type_id'])
                ->where('status', 'active')
                ->first();

            if (! $organizationType instanceof OrganizationType) {
                throw ValidationException::withMessages([
                    'organization_type_id' => ['La sous catégorie selectionnee est invalide.'],
                ]);
            }
        }

        if (! $organization instanceof Organization && ! empty($payload['organization_id'])) {
            $organization = Organization::query()
                ->whereKey($payload['organization_id'])
                ->where('application_id', $application->id)
                ->where('status', 'active')
                ->first();
        }

        if (! empty($payload['organization_id']) && ! $organization instanceof Organization) {
            throw ValidationException::withMessages([
                'organization_id' => ['L institution selectionnee est invalide pour cette catégorie.'],
            ]);
        }

        if ($meter?->organization instanceof Organization && $organizationType instanceof OrganizationType && (int) $meter->organization->organization_type_id !== (int) $organizationType->id) {
            throw ValidationException::withMessages([
                'organization_type_id' => ['La sous catégorie selectionnee ne correspond pas a l identifiant choisi.'],
            ]);
        }

        if ($organization instanceof Organization && $organizationType instanceof OrganizationType && (int) $organization->organization_type_id !== (int) $organizationType->id) {
            throw ValidationException::withMessages([
                'organization_id' => ['L institution selectionnee ne correspond pas a la sous catégorie choisie.'],
            ]);
        }

        if (! $organizationType instanceof OrganizationType && $organization instanceof Organization && $organization->organization_type_id !== null) {
            $organizationType = $organization->organizationType;
        }

        if (! $organizationType instanceof OrganizationType && $meter?->organization instanceof Organization && $meter->organization->organization_type_id !== null) {
            $organizationType = $meter->organization->organizationType;
        }

        if ($application->requires_organization_type_on_report && $meter === null && ! $organizationType instanceof OrganizationType) {
            throw ValidationException::withMessages([
                'organization_type_id' => ['La sous catégorie est obligatoire pour cette catégorie.'],
            ]);
        }

        if ($organizationType instanceof OrganizationType) {
            $hasOrganizationForType = Organization::query()
                ->where('application_id', $application->id)
                ->where('organization_type_id', $organizationType->id)
                ->where('status', 'active')
                ->exists();

            if (! $hasOrganizationForType) {
                throw ValidationException::withMessages([
                    'organization_type_id' => ['Aucune institution active ne correspond a cette sous catégorie pour cette catégorie.'],
                ]);
            }
        }

        [$country, $city, $commune] = $meter
            ? $this->resolveLocationFromMeter($user, $meter)
            : $this->resolveLocationFromProfile($user);

        $signalType = SignalType::query()
            ->where('status', 'active')
            ->where('code', strtoupper($payload['signal_code']))
            ->where('application_id', $application->id)
            ->where(function ($query) use ($organization): void {
                $query->where(function ($globalQuery): void {
                    $globalQuery->whereNull('organization_id')
                        ->whereDoesntHave('organizations');
                });

                if ($organization?->id !== null) {
                    $query->orWhere('organization_id', $organization->id)
                        ->orWhereHas('organizations', fn ($organizationQuery) => $organizationQuery->whereKey($organization->id));
                }
            })
            ->when(
                $organization?->id !== null,
                fn ($query) => $query->orderByRaw(
                    'CASE WHEN organization_id = ? OR EXISTS (SELECT 1 FROM organization_signal_type WHERE organization_signal_type.signal_type_id = signal_types.id AND organization_signal_type.organization_id = ?) THEN 0 ELSE 1 END',
                    [$organization->id, $organization->id]
                ),
                fn ($query) => $query->orderBy('organization_id')
            )
            ->first();

        if ($signalType === null) {
            throw ValidationException::withMessages([
                'signal_code' => ['Le type de signal selectionne est invalide.'],
            ]);
        }

        [$signalSubType, $signalSubTypeCode, $signalSubTypeLabel] = $this->resolveSignalSubType($signalType, $payload);

        $organizationTypeId = $organizationType?->id;
        $slaNetworkTypes = collect([
            $signalType->organization?->code,
            $application->code,
            $signalType->network_type,
            $meter?->network_type,
            $organization?->code,
        ])->filter()->map(fn ($value) => strtoupper((string) $value))->unique()->values()->all();

        $programmedSla = $organizationTypeId
            ? OrganizationTypeSignalSla::query()
                ->where('organization_type_id', $organizationTypeId)
                ->where('signal_code', $signalType->code)
                ->when($slaNetworkTypes !== [], fn ($query) => $query->whereIn('network_type', $slaNetworkTypes))
                ->where('status', 'active')
                ->value('sla_hours')
            : null;

        return [
            'meter' => $meter,
            'application' => $application,
            'organization' => $organization,
            'country' => $country,
            'city' => $city,
            'commune' => $commune,
            'signal_type' => $signalType,
            'signal_sub_type' => $signalSubType,
            'signal_sub_type_code' => $signalSubTypeCode,
            'signal_sub_type_label' => $signalSubTypeLabel,
            'programmed_sla' => $programmedSla,
        ];
    }

    private function resolveSignalSubType(SignalType $signalType, array $payload): array
    {
        $activeSubTypes = $signalType->subTypes()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($activeSubTypes->isEmpty()) {
            return [null, null, null];
        }

        $selectedCode = strtoupper(trim((string) ($payload['signal_sub_type_code'] ?? '')));

        if ($selectedCode === '') {
            throw ValidationException::withMessages([
                'signal_sub_type_code' => ['Le sous-type de signal est obligatoire pour ce type de signalement.'],
            ]);
        }

        if ($selectedCode === 'OTHER') {
            return [null, 'OTHER', 'Autre'];
        }

        $subType = $activeSubTypes->first(fn (SignalSubType $item): bool => $item->code === $selectedCode);

        if (! $subType instanceof SignalSubType) {
            throw ValidationException::withMessages([
                'signal_sub_type_code' => ['Le sous-type de signal selectionne est invalide.'],
            ]);
        }

        return [$subType, $subType->code, $subType->label];
    }

    private function resolveLocationFromMeter(PublicUser $user, Meter $meter): array
    {
        $communeName = trim((string) ($meter->commune ?: $user->commune));
        $cityName = trim((string) ($meter->city ?: $user->city));

        if ($communeName === '') {
            throw ValidationException::withMessages([
                'meter_id' => ['La commune enregistree sur cet identifiant est introuvable. Mettez a jour l identifiant avant de signaler.'],
            ]);
        }

        $commune = Commune::query()
            ->with('city.country')
            ->where('name', $communeName)
            ->where('status', 'active')
            ->when($cityName !== '', fn ($query) => $query->whereHas('city', fn ($cityQuery) => $cityQuery->where('name', $cityName)))
            ->whereHas('city', function ($query): void {
                $query->where('status', 'active')
                    ->whereHas('country', fn ($countryQuery) => $countryQuery->where('status', 'active'));
            })
            ->first();

        if ($commune === null || $commune->city === null || $commune->city->country === null) {
            throw ValidationException::withMessages([
                'meter_id' => ['La commune enregistree sur cet identifiant ne correspond a aucune commune active. Mettez a jour l identifiant avant de signaler.'],
            ]);
        }

        return [$commune->city->country, $commune->city, $commune];
    }

    private function resolveLocationFromProfile(PublicUser $user): array
    {
        $commune = null;

        if ($user->commune_id !== null) {
            $commune = Commune::query()
                ->with('city.country')
                ->whereKey($user->commune_id)
                ->where('status', 'active')
                ->whereHas('city', function ($query): void {
                    $query->where('status', 'active')
                        ->whereHas('country', fn ($countryQuery) => $countryQuery->where('status', 'active'));
                })
                ->first();
        }

        if ($commune === null) {
            $communeName = trim((string) $user->commune);
            $cityName = trim((string) $user->city);

            if ($communeName === '') {
                throw ValidationException::withMessages([
                    'commune' => ['La commune du profil est requise pour signaler sans identifiant.'],
                ]);
            }

            $commune = Commune::query()
                ->with('city.country')
                ->where('name', $communeName)
                ->where('status', 'active')
                ->when($cityName !== '', fn ($query) => $query->whereHas('city', fn ($cityQuery) => $cityQuery->where('name', $cityName)))
                ->whereHas('city', function ($query): void {
                    $query->where('status', 'active')
                        ->whereHas('country', fn ($countryQuery) => $countryQuery->where('status', 'active'));
                })
                ->first();
        }

        if ($commune === null || $commune->city === null || $commune->city->country === null) {
            throw ValidationException::withMessages([
                'commune' => ['La commune du profil ne correspond a aucune commune active. Mettez a jour votre profil avant de signaler sans identifiant.'],
            ]);
        }

        return [$commune->city->country, $commune->city, $commune];
    }

    private function storeSignalAttachmentFile(UploadedFile $file, string $reference): array
    {
        $path = $this->wasabiService->uploadFile(
            $file,
            config('wasabi.report_signal_directory', 'reports/signals').'/'.$reference,
            'attachment'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'signal_attachment' => ['Impossible de televerser le fichier sur le stockage distant.'],
            ]);
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        return [
            'type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
            'name' => $file->getClientOriginalName() ?: 'piece-jointe-signalement',
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'path' => $path,
        ];
    }

    private function generateReference(): string
    {
        return 'SIG-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
