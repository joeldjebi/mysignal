<?php

namespace App\Domain\Reports\Actions;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\IncidentReport;
use App\Models\Organization;
use App\Models\OrganizationTypeSignalSla;
use App\Models\PublicUser;
use App\Models\SignalType;
use App\Services\WasabiService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateIncidentReportAction
{
    public function __construct(
        private readonly WasabiService $wasabiService,
    ) {}

    public function handle(PublicUser $user, array $payload): IncidentReport
    {
        $meter = $user->meters()->whereKey($payload['meter_id'])->first();

        if ($meter === null) {
            throw ValidationException::withMessages([
                'meter_id' => ['Le compteur selectionne ne vous appartient pas.'],
            ]);
        }

        $country = Country::query()->whereKey($payload['country_id'])->where('status', 'active')->firstOrFail();
        $city = City::query()->whereKey($payload['city_id'])->where('country_id', $country->id)->where('status', 'active')->first();
        $commune = Commune::query()->whereKey($payload['commune_id'])->where('city_id', $payload['city_id'])->where('status', 'active')->first();

        if ($city === null) {
            throw ValidationException::withMessages([
                'city_id' => ['La ville selectionnee n appartient pas au pays choisi.'],
            ]);
        }

        if ($commune === null) {
            throw ValidationException::withMessages([
                'commune_id' => ['La commune selectionnee n appartient pas a la ville choisie.'],
            ]);
        }

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

        return DB::transaction(function () use ($user, $meter, $country, $city, $commune, $payload, $signalType, $signalPayload, $organizationTypeId, $programmedSla): IncidentReport {
            $reference = $this->generateReference();
            $storedSignalPayload = $this->storeSignalPayloadFiles($signalPayload, $reference);

            return IncidentReport::query()->create([
                'public_user_id' => $user->id,
                'application_id' => $meter->application_id ?: $signalType->application_id,
                'organization_id' => $meter->organization_id,
                'meter_id' => $meter->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
                'commune_id' => $commune->id,
                'address' => $payload['address'] ?? null,
                'latitude' => $payload['latitude'] ?? null,
                'longitude' => $payload['longitude'] ?? null,
                'location_accuracy' => $payload['location_accuracy'] ?? null,
                'location_source' => $payload['location_source'] ?? null,
                'network_type' => $meter->network_type,
                'signal_code' => $signalType->code,
                'signal_label' => $signalType->label,
                'incident_type' => $signalType->code,
                'reference' => $reference,
                'description' => $payload['description'] ?? null,
                'signal_payload' => $storedSignalPayload,
                'target_sla_hours' => $programmedSla ?? $signalType->default_sla_hours,
                'occurred_at' => $payload['occurred_at'] ?? CarbonImmutable::now(),
                'status' => IncidentReportStatus::Submitted->value,
            ]);
        });
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

    private function generateReference(): string
    {
        return 'SIG-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
