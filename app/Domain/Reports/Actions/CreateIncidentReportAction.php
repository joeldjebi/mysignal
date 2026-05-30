<?php

namespace App\Domain\Reports\Actions;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Models\Organization;
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
                'application_id' => $prepared['meter']->application_id ?: $prepared['signal_type']->application_id,
                'organization_id' => $prepared['meter']->organization_id,
                'meter_id' => $prepared['meter']->id,
                'country_id' => $prepared['country']->id,
                'city_id' => $prepared['city']->id,
                'commune_id' => $prepared['commune']->id,
                'address' => $prepared['meter']->address ?: ($user->address ?? null),
                'latitude' => $prepared['meter']->latitude ?? ($payload['latitude'] ?? null),
                'longitude' => $prepared['meter']->longitude ?? ($payload['longitude'] ?? null),
                'location_accuracy' => $prepared['meter']->location_accuracy ?? ($payload['location_accuracy'] ?? null),
                'location_source' => $prepared['meter']->location_source ?? ($payload['location_source'] ?? null),
                'network_type' => $prepared['meter']->network_type,
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
        $meter = $user->meters()->whereKey($payload['meter_id'])->first();

        if ($meter === null) {
            throw ValidationException::withMessages([
                'meter_id' => ['Le compteur selectionne ne vous appartient pas.'],
            ]);
        }

        [$country, $city, $commune] = $this->resolveLocationFromMeter($user, $meter);

        $signalType = SignalType::query()
            ->where('status', 'active')
            ->where('code', strtoupper($payload['signal_code']))
            ->where('application_id', $meter->application_id)
            ->where(function ($query) use ($meter): void {
                $query->whereNull('organization_id');

                if ($meter->organization_id !== null) {
                    $query->orWhere('organization_id', $meter->organization_id);
                }
            })
            ->orderByRaw('CASE WHEN organization_id IS NULL THEN 1 ELSE 0 END')
            ->first();

        if ($signalType === null) {
            throw ValidationException::withMessages([
                'signal_code' => ['Le type de signal selectionne est invalide.'],
            ]);
        }

        [$signalSubType, $signalSubTypeCode, $signalSubTypeLabel] = $this->resolveSignalSubType($signalType, $payload);

        $organizationTypeId = $meter->organization_id
            ? Organization::query()
                ->whereKey($meter->organization_id)
                ->where('status', 'active')
                ->value('organization_type_id')
            : null;
        $slaNetworkTypes = collect([
            $signalType->organization?->code,
            $signalType->application?->code,
            $signalType->network_type,
            $meter->network_type,
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
