<?php

namespace App\Domain\Reports\Actions;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Models\Organization;
use App\Models\OrganizationTypeSignalSla;
use App\Models\PublicUser;
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

    public function handle(PublicUser $user, array $payload, ?UploadedFile $signalAttachmentFile = null): IncidentReport
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

        $signalPayload = $payload['signal_payload'] ?? [];

        foreach ($signalType->data_fields ?? [] as $field) {
            if (($field['required'] ?? true) && (! array_key_exists($field['key'], $signalPayload) || blank($signalPayload[$field['key']]))) {
                throw ValidationException::withMessages([
                    'signal_payload.'.$field['key'] => ['La donnee ['.$field['label'].'] est requise pour ce type de signal.'],
                ]);
            }

            if (($field['type'] ?? 'text') === 'select' && array_key_exists($field['key'], $signalPayload) && filled($signalPayload[$field['key']])) {
                $allowedOptions = collect($field['options'] ?? [])
                    ->map(fn ($option) => trim((string) $option))
                    ->filter()
                    ->values()
                    ->all();

                if (! in_array((string) $signalPayload[$field['key']], $allowedOptions, true)) {
                    throw ValidationException::withMessages([
                        'signal_payload.'.$field['key'] => ['La valeur selectionnee pour ['.$field['label'].'] est invalide.'],
                    ]);
                }
            }
        }

        $organizationTypeId = $meter->organization_id
            ? Organization::query()
                ->whereKey($meter->organization_id)
                ->where('status', 'active')
                ->value('organization_type_id')
            : null;

        $programmedSla = $organizationTypeId
            ? OrganizationTypeSignalSla::query()
                ->where('organization_type_id', $organizationTypeId)
                ->where('signal_code', $signalType->code)
                ->where('status', 'active')
                ->value('sla_hours')
            : null;

        $effectiveSlaHours = (int) ($programmedSla ?? $signalType->default_sla_hours ?? 0);

        $latestSimilarReport = IncidentReport::query()
            ->where('meter_id', $meter->id)
            ->where('signal_code', $signalType->code)
            ->where('status', '!=', IncidentReportStatus::Rejected->value)
            ->latest('created_at')
            ->first(['id', 'reference', 'created_at', 'target_sla_hours', 'status']);

        if ($latestSimilarReport !== null) {
            $blockingSlaHours = (int) ($latestSimilarReport->target_sla_hours ?? $effectiveSlaHours);

            if ($blockingSlaHours > 0 && $latestSimilarReport->created_at !== null) {
                $availableAt = CarbonImmutable::instance($latestSimilarReport->created_at)->addHours($blockingSlaHours);

                if (now()->lt($availableAt)) {
                    throw ValidationException::withMessages([
                        'signal_code' => [
                            'Un signalement identique existe deja pour ce compteur. Vous pourrez en soumettre un nouveau a partir du '.$availableAt->translatedFormat('d/m/Y \a H:i').'.',
                        ],
                    ]);
                }
            }
        }

        return DB::transaction(function () use ($user, $meter, $country, $city, $commune, $payload, $signalType, $signalPayload, $programmedSla, $signalAttachmentFile): IncidentReport {
            $reference = $this->generateReference();
            $storedSignalPayload = $this->storeSignalPayloadFiles($signalPayload, $reference);
            $signalAttachment = $signalAttachmentFile
                ? $this->storeSignalAttachmentFile($signalAttachmentFile, $reference)
                : null;

            return IncidentReport::query()->create([
                'public_user_id' => $user->id,
                'application_id' => $meter->application_id ?: $signalType->application_id,
                'organization_id' => $meter->organization_id,
                'meter_id' => $meter->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
                'commune_id' => $commune->id,
                'address' => $meter->address ?: ($user->address ?? null),
                'latitude' => $meter->latitude ?? ($payload['latitude'] ?? null),
                'longitude' => $meter->longitude ?? ($payload['longitude'] ?? null),
                'location_accuracy' => $meter->location_accuracy ?? ($payload['location_accuracy'] ?? null),
                'location_source' => $meter->location_source ?? ($payload['location_source'] ?? null),
                'network_type' => $meter->network_type,
                'signal_code' => $signalType->code,
                'signal_label' => $signalType->label,
                'incident_type' => $signalType->code,
                'reference' => $reference,
                'description' => $payload['description'] ?? null,
                'signal_payload' => $storedSignalPayload,
                'signal_attachment' => $signalAttachment,
                'target_sla_hours' => $programmedSla ?? $signalType->default_sla_hours,
                'occurred_at' => $payload['occurred_at'] ?? CarbonImmutable::now(),
                'status' => IncidentReportStatus::Submitted->value,
            ]);
        });
    }

    private function resolveLocationFromMeter(PublicUser $user, Meter $meter): array
    {
        $communeName = trim((string) ($meter->commune ?: $user->commune));

        if ($communeName === '') {
            throw ValidationException::withMessages([
                'meter_id' => ['La commune enregistree sur cet identifiant est introuvable. Mettez a jour l identifiant avant de signaler.'],
            ]);
        }

        $commune = Commune::query()
            ->with('city.country')
            ->where('name', $communeName)
            ->where('status', 'active')
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

    private function storeSignalPayloadFiles(array $signalPayload, string $reference): array
    {
        return collect($signalPayload)
            ->map(function ($value, $key) use ($reference) {
                if (! is_array($value) || empty($value['data_url'])) {
                    return $value;
                }

                $path = $this->wasabiService->uploadDataUrl(
                    (string) $value['data_url'],
                    config('wasabi.report_signal_directory', 'reports/signals').'/'.$reference,
                    (string) $key,
                    $value['name'] ?? null,
                );

                if (! $path) {
                    throw ValidationException::withMessages([
                        'signal_payload.'.$key => ['Impossible de televerser le fichier sur le stockage distant.'],
                    ]);
                }

                return [
                    'type' => $value['type'] ?? 'file',
                    'name' => $value['name'] ?? ($key.'.bin'),
                    'mime_type' => $value['mime_type'] ?? 'application/octet-stream',
                    'path' => $path,
                ];
            })
            ->all();
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
